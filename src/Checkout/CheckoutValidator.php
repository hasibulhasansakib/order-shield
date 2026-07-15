<?php
namespace OrderShield\Checkout;

class CheckoutValidator {
    
    private RulesEngine $rulesEngine;
    private EventLogger $eventLogger;

    public function __construct(RulesEngine $rulesEngine, EventLogger $eventLogger) {
        $this->rulesEngine = $rulesEngine;
        $this->eventLogger = $eventLogger;
    }

    public function init(): void {
        // Traditional Shortcode Checkout
        add_action('woocommerce_after_checkout_validation', [$this, 'validateCheckout'], 10, 2);
        add_action('woocommerce_checkout_order_processed', [$this, 'logSuccessfulOrder'], 10, 3);

        // WooCommerce Blocks (Store API) Checkout Validation
        add_action('woocommerce_store_api_checkout_order_processed', [$this, 'logSuccessfulOrderBlocks'], 10, 1);
        add_action('woocommerce_store_api_validate_checkout_request', [$this, 'validateCheckoutBlocks'], 10, 1);
    }

    /**
     * Validate the checkout before the order is created.
     */
    public function validateCheckout($data, $errors): void {
        
        $customer_data = [
            'phone' => $data['billing_phone'] ?? '',
            'email' => $data['billing_email'] ?? '',
            'ip'    => $this->getClientIp()
        ];

        $cart_snapshot = $this->getCartSnapshot();
        $evaluation = $this->rulesEngine->evaluate($customer_data);

        if (!$evaluation['allowed']) {
            // Block the order and log the attempt
            $errors->add('validation', $evaluation['reason']);
            $this->eventLogger->logAttempt('blocked', $customer_data, $evaluation['rule_id'], $cart_snapshot);
        } else {
            // It will succeed (assuming WC validation passes), so log it as success attempt
            // In a real scenario, we might hook into woocommerce_checkout_order_processed to log the actual success,
            // but logging the successful attempt here is also valid for tracking.
            $this->eventLogger->logAttempt('success', $customer_data, $evaluation['rule_id'], $cart_snapshot);
        }
    }

    public function logSuccessfulOrder($order_id, $posted_data, $order): void {
        // Log is already handled in validateCheckout for success to capture attempts, 
        // but if we want to ensure only strictly placed orders are success:
        // For now, validateCheckout handles the success log to track all attempts (even if payment fails later).
    }

    public function logSuccessfulOrderBlocks(\Automattic\WooCommerce\StoreApi\Exceptions\RouteException $error = null): void {
        // Blocks specific hook
    }

    public function validateCheckoutBlocks(\WP_REST_Request $request): void {
        $billing_address = $request->get_param('billing_address');
        
        $customer_data = [
            'phone' => $billing_address['phone'] ?? '',
            'email' => $billing_address['email'] ?? '',
            'ip'    => $this->getClientIp()
        ];

        $cart_snapshot = $this->getCartSnapshot();
        $evaluation = $this->rulesEngine->evaluate($customer_data);

        if (!$evaluation['allowed']) {
            $this->eventLogger->logAttempt('blocked', $customer_data, $evaluation['rule_id'], $cart_snapshot);
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
                'woocommerce_rest_checkout_error',
                $evaluation['reason'],
                400
            );
        } else {
            $this->eventLogger->logAttempt('success', $customer_data, $evaluation['rule_id'], $cart_snapshot);
        }
    }

    private function getClientIp(): string {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return trim($ip);
    }

    /**
     * Get a snapshot of the current cart contents safely
     */
    private function getCartSnapshot(): ?string {
        if (!isset(WC()->cart)) {
            return null;
        }

        $items = [];
        $cart_contents = WC()->cart->get_cart();
        
        if (empty($cart_contents)) {
            return null;
        }

        foreach ($cart_contents as $cart_item_key => $cart_item) {
            $_product = $cart_item['data'];
            if (!$_product) {
                continue;
            }

            $img_url = '';
            $image_id = $_product->get_image_id();
            if ($image_id) {
                $img_src = wp_get_attachment_image_src($image_id, 'thumbnail');
                if ($img_src) {
                    $img_url = $img_src[0];
                }
            }

            $items[] = [
                'id'        => $_product->get_id(),
                'name'      => $_product->get_name(),
                'qty'       => $cart_item['quantity'],
                'price'     => strip_tags(wc_price($_product->get_price())),
                'image'     => $img_url,
                'permalink' => $_product->get_permalink()
            ];
        }

        return !empty($items) ? json_encode($items) : null;
    }
}
