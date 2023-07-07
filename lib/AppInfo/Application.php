<?php
namespace OCA\WhmcsIntegration\AppInfo;
use \OCP\AppFramework\App;
use \OCA\WhmcsIntegration\Hooks\UserHooks;

class Application extends App {
    public function __construct(array $urlParams=array()){
        parent::__construct('whmcsintegration', $urlParams);
        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserManager()
            );
        });
    }
}