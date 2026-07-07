<?php
namespace OrderShield\Core;

class Plugin {
    
    private static ?Plugin $instance = null;

    private function __construct() {}

    public static function getInstance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        $geoProvider = new \OrderShield\GeoLocation\IpApiProvider();
        $eventLogger = new \OrderShield\Checkout\EventLogger($geoProvider);
        $rulesEngine = new \OrderShield\Checkout\RulesEngine();
        
        $checkoutValidator = new \OrderShield\Checkout\CheckoutValidator($rulesEngine, $eventLogger);
        $checkoutValidator->init();

        // Admin Modules
        if (is_admin()) {
            $adminMenu = new \OrderShield\Admin\AdminMenu();
            $adminMenu->init();

            $dashboardController = new \OrderShield\Admin\DashboardController();
            $dashboardController->init();
        }
    }
}
