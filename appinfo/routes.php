<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\WhmcsIntegration\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],	   
	   //['name' => 'page#updatelimit', 'url' => '/', 'verb' => 'POST'],
           ['name' => 'page#updatelimit', 'url' => '/', 'verb' => 'POST'],           
	   //['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],
    ],
    'ocs' => [
	   ['name' => 'page#groupsWithDetails', 'url' => '/groupswithdetails', 'verb' => 'GET'],
	   ['name' => 'page#usersWithDetails', 'url' => '/userswithdetails', 'verb' => 'GET'],
	   ['name' => 'page#getGroupLimit', 'url' => '/getgrouplimit/{groupid}', 'verb' => 'GET'],
	   ['name' => 'page#setGroupLimit', 'url' => '/setgrouplimit', 'verb' => 'POST'],
	   ['name' => 'page#toggleGroups', 'url' => '/togglegroups', 'verb' => 'POST'],
	   ['name' => 'page#toggleGroupAdmins', 'url' => '/togglegroupadmins', 'verb' => 'POST'],
	   ['name' => 'page#suspendUser', 'url' => '/suspenduser', 'verb' => 'POST'],
	   ['name' => 'page#unsuspendUser', 'url' => '/unsuspenduser', 'verb' => 'POST'],
	   ['name' => 'page#delUserValue', 'url' => '/deluservalue', 'verb' => 'POST'],
	   ['name' => 'page#changeUserPassword', 'url' => '/changeuserpassword', 'verb' => 'POST'],           
       	   ['name' => 'page#autoLoginUser', 'url' => '/autologinuser', 'verb' => 'POST'],
    ]
];
