<?php
namespace OCA\WhmcsIntegration\Hooks;
use OCP\IUserManager;

class UserHooks {

    private $userManager;

    public function __construct(IUserManager $userManager){
        $this->userManager = $userManager;
    }

    public function register() {
        $callback = function($user) {            
            // your code that executes before $user is deleted
            //echo '<pre>'; print_r($params); die();
            //error_reporting(E_ALL); ini_set('display_errors', 1);
            
            
        };
        $this->userManager->listen('\OC\User', 'postCreateUser', $callback);
    }

}