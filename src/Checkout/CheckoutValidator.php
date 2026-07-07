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
        add_action('woocommerce_after_checkout_validation', [$this, 'validateCheckout'], 10, 2);
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

        $evaluation = $this->rulesEngine->evaluate($customer_data);

        if (!$evaluation['allowed']) {
            // Block the order and log the attempt
            $errors->add('validation', $evaluation['reason']);
            $this->eventLogger->logAttempt('blocked', $customer_data, $evaluation['rule_id']);
        } else {
            // It will succeed (assuming WC validation passes), so log it as success attempt
            // In a real scenario, we might hook into woocommerce_checkout_order_processed to log the actual success,
            // but logging the successful attempt here is also valid for tracking.
            $this->eventLogger->logAttempt('success', $customer_data, $evaluation['rule_id']);
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
}
