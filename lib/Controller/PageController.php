<?php
namespace OCA\WhmcsIntegration\Controller;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\DB\QueryBuilder\IQueryBuilder;


class PageController extends Controller {

    private $userId;

    public function __construct($AppName, IRequest $request, IUserManager $usermanager, IGroupManager $groupManager, IUserSession $userSession, $UserId) {
        parent::__construct($AppName, $request, $usermanager , $groupManager);
        $this->userId = $UserId;
        $this->userManager = $usermanager;
        $this->groupManager = $groupManager;
        $this->userSession = $userSession;
    }

    /**
     * CAUTION: the @Stuff turns off security checks; for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {

        //$tmpl =  new OCP\Template('WhmcsIntegration', 'index', 'user');
        $search = '';
        $limit = null;
        $offset = null;
       // $groups = OC_Group::getGroups($search, $limit, $offset);
        $parameters = [];
        $searchLike = '';
        if ($search !== '') {
            $parameters[] = '%' . $search . '%';
            $searchLike = ' WHERE LOWER(`gid`) LIKE LOWER(?)';
        }
        $query = \OC::$server->getDatabaseConnection();
       // $query->select('gid')
            //    ->from('groups')
            //    ->where($query->expr()->eq('gid', $query->createNamedParameter($parameters, IQueryBuilder::PARAM_INT_ARRAY)));

      ///  $stmt = \OC_DB::prepare('SELECT `gid` FROM `*PREFIX*groups`' . $searchLike . ' ORDER BY `gid` ASC', $limit, $offset);

      $stmt = "SELECT `gid` FROM `*PREFIX*groups`" . $searchLike . " ORDER BY `gid` ASC";
      $sth =$query->prepare($stmt);
      $result = $sth->execute();
      $result = $result->fetchAll();
        $groups = array();
        foreach ($result as $row) {
            $groups[] = $row['gid'];
        }


        unset($groups[array_search("admin", $groups)]);
        $grouplimit = array();
        foreach ($groups as $group) {
            $accountlimit = \OC::$server->getConfig()->getUserValue($group, 'WhmcsIntegration', 'accountlimit');
            $grouplimit[] = array(
                "group" => $group,
                "limit" => empty($accountlimit) ? 0 : $accountlimit
            );
        }

        $connection = \OC::$server->getDatabaseConnection();
        $cUserId = \OC::$server->getUserSession()->getUser()->getUID();
        $query = "SELECT gid FROM *PREFIX*group_user where uid='".$cUserId."' and gid='admin' ";
        $sth =$connection->prepare($query);
        $sth->execute();
        $checkadmin = $sth->fetchAll();
		$user = "SELECT * FROM *PREFIX*group_user";
		$sth =$connection->prepare($user);
        $sth->execute();
        $userdetail = $sth->fetchAll();
        if(empty($checkadmin)){
            $appPermission = 'no';
        }else{
            $appPermission = 'yes';
        }
        //echo '<pre>'; print_r($appPermission); die();
        $data = array('groups' => $grouplimit,'appPermission'=>$appPermission ,'user' => $userdetail);
        //echo '<pre>'; print_r($data); die();
        return new TemplateResponse('whmcsintegration', 'index', $data);
    }

    private function resDataFormat($data,$status,$message) {
        $params['ocs'] = array();
        $params['ocs']['meta']['status'] = strtolower($status);
        if($message=='OK'){
            $params['ocs']['meta']['statuscode'] = '200';
        }else{
            $params['ocs']['meta']['statuscode'] = '100';
        }
        $params['ocs']['meta']['message'] = $message;
        $params['ocs']['data'] = $data;
        return $params;
    }

    /**
     * @NoCSRFRequired
     */
    public function updatelimit() {
        if(isset($_POST["saveAccountLimit"])){
            foreach($_POST["gaccountlimit"] as $key => $value){
                 \OC::$server->getConfig()->setUserValue($key, 'WhmcsIntegration', 'accountlimit', $value);
            }
        }
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('location:'.$url); die();
    }

    /**
     * @PublicPage
     * @NoCSRFRequired
     */
    public function autoLoginUser(string $username, string $password) {
        $password = base64_decode($password);
        $this->userSession->logout($username);
        if($this->userSession->login($username, $password)){
            $user = $this->userManager->get($username);
            $this->userSession->completeLogin($user, ['loginName' => $user->getUID(), 'password' => $password]);
            $this->userSession->createSessionToken($this->request, $user->getUID(), $user->getUID());
            header('location: ../../../../index.php/apps/files/');die('success');
        }else{
            header('location: ../../../../index.php/login');die('error');
        }
        header('location: ../../../../index.php/login');die('error');
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function groupsWithDetails() {
        $groups = array();
        //$allgroups = \OC_Group::getGroups("", null, null);
        $connection = \OC::$server->getDatabaseConnection();
        //error_reporting(E_ALL); ini_set('display_errors', 1);
        $query = "SELECT gid FROM *PREFIX*groups ";
        $sth =$connection->prepare($query);
        $sth->execute();
        $result = $sth->fetchAll();
        $allgroups = array();
        foreach($result as $row) {
                $allgroups[] = $row['gid'];
        }
        unset($allgroups[array_search("admin", $allgroups)]);
        foreach ($allgroups as $group) {
            $limit = \OC::$server->getConfig()->getUserValue($group, 'WhmcsIntegration', 'accountlimit');

            $query = "SELECT `uid` FROM `*PREFIX*group_user` WHERE `gid` = '" . $group . "' ORDER BY `uid` ASC";
            $sth =$connection->prepare($query);
            $sth->execute();
            $result = $sth->fetchAll();
            $users = array();
            foreach($result as $row) {
                    $users[] = $row['uid'];
            }

            //$users = \OC_Group::usersInGroup($group);
            #this code added for oncloud version 9 -- start
            $query = "SELECT uid FROM *PREFIX*group_admin where gid='".$group."' ";
            $sth =$connection->prepare($query);
            $sth->execute();
            $subAdmins = $sth->fetchAll();
            $subAdminData = array();
            foreach($subAdmins as $subAdmin)
            {
                $subAdmin =  (array) $subAdmin;
                $subAdminData[] = $subAdmin['uid'];
            }
            #this code added for oncloud version 9 -- end

            $groups[] = array("group" => $group,
                "limit" => $limit,
                "countusers" => count($users),
                "users" => $users,
                "groupadmins" => $subAdminData //\OC_Subadmin::getGroupsSubAdmins($group)
            );
        }
        $data = array('groups' => $groups);
        $result = $this->resDataFormat($data,'OK','OK');
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public  function usersWithDetails() {
        $userDetails = array();
        $search = !empty($_GET['search']) ? $_GET['search'] : '';
        $limit = !empty($_GET['limit']) ? $_GET['limit'] : null;
        $offset = !empty($_GET['offset']) ? $_GET['offset'] : null;
        $connection = \OC::$server->getDatabaseConnection();
        $query = "SELECT * FROM *PREFIX*users ";// where uid='".$user."'
        if(!empty($search)){
            $query .= "where uid='".$search."'";
        }
        $sth =$connection->prepare($query);
        $sth->execute();
        $users = $sth->fetchAll();

        foreach ($users as $userData) {
            $user = $userData['uid'];
            # User Type
            $type = \OC::$server->getConfig()->getUserValue($user, 'WhmcsIntegration', 'suspended');
            if ($type) {
                $usertype = 'whmcs';
                $status = 'suspended';
            } else {
                $usertype = 'owncloud';
                $status = 'unsuspended';
            }
            #this code added for oncloud version 9 -- start
            $query = "SELECT gid FROM *PREFIX*group_admin where uid='".$user."' ";
            $sth =$connection->prepare($query);
            $sth->execute();
            $subAdmins = $sth->fetchAll();
            $subAdminData = array();
            foreach($subAdmins as $subAdmin)
            {
                $subAdmin =  (array) $subAdmin;
                $subAdminData[] = $subAdmin['gid'];
            }
            #this code added for oncloud version 9 -- end

            #this code added for oncloud version 12 -- start
            $query = "SELECT gid FROM *PREFIX*group_user where uid='".$user."' ";
            $sth =$connection->prepare($query);
            $sth->execute();
            $groups = $sth->fetchAll();
            $groupData = array();
            foreach($groups as $group)
            {
                $group =  (array) $group;
                $groupData[] = $group['gid'];
            }
            #this code added for oncloud version 12 -- end

            $displayname = $userData['displayname'];
            if(empty($displayname)){ $displayname = $user; }

            $userDetails[] = array(
                "username" => $user,
                "type" => $usertype,
                "status" => $status,
                "displayName" => $displayname,
                "group" => $groupData,
                "quota" => \OC::$server->getConfig()->getUserValue($user, 'files', 'quota'),
                "email" => \OC::$server->getConfig()->getUserValue($user, 'settings', 'email'),
                "subadmin" => $subAdminData,
                "adminuser" => $this->groupManager->isAdmin($user),
                "status" => \OC::$server->getConfig()->deleteUserValue($user, 'core', 'enabled')
            );
        }
        $data = array('users' => $userDetails);
        $result = $this->resDataFormat($data,'OK','OK');
        return new JSONResponse($result);
    }

     /**
     * @NoCSRFRequired
     */
    public function getGroupLimit(string $groupid) {
        $limit = \OC::$server->getConfig()->getUserValue($groupid, 'WhmcsIntegration', 'accountlimit');
        if ($limit) {
            $data = array('limit' => $limit);
            $message = 'OK';
            $status = 'ok';
        } else {
            $message = 'Limit does not have defined for specified group';
             $data = array();
             $status = 'error';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

        /**
	 * @NoCSRFRequired
	 * @param string $limit
	 * @return DataResponse
	 * @throws OCSException
	 */
     public function setGroupLimit(string $groupid, string $limit) {
         //echo $groupid; echo $limit; die('sadfasd');
         \OC::$server->getConfig()->setUserValue($groupid, 'WhmcsIntegration', 'accountlimit', $limit);
        if ($groupid == '' || $limit == '') {
             $message = 'Limit does not have defined for specified group';
             $data = array();
             $status = 'error';
        } else {
            $data = array('groupid' => $groupid, 'limit' => $limit);
            $message = 'OK';
            $status = 'ok';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

   /**
    * @NoCSRFRequired
    * @param string $userid
    * @return DataResponse
    * @throws OCSException
    */
    public function toggleGroups(string $userid, string $groups) {
        $userid = isset($userid) ? $userid : null;
        $groups = isset($groups) ? $groups : null;
        $groups = json_decode($groups);

        #this code added for oncloud version 12 -- start
        if(!empty($userid)){
            $connection = \OC::$server->getDatabaseConnection();
            $query = "DELETE FROM *PREFIX*group_user WHERE uid='".$userid."' ";
            $sth =$connection->prepare($query);
            $sth->execute();
        }

        if (count($groups)) {
            foreach ($groups as $group) {
            $query = "INSERT INTO *PREFIX*group_user (uid,gid) VALUES ('".$userid."','".$group."') ";
            $sth =$connection->prepare($query);
            $sth->execute();
            }
            #this code added for oncloud version 12 -- end
            $data = array();
            $message = 'OK';
            $status = 'ok';
        } else {
             $message = 'No groups are selected';
             $data = array();
             $status = 'error';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

    /**
    * @NoCSRFRequired
    */
    public function toggleGroupAdmins(string $userid, string $groups) {
        $userid = isset($userid) ? $userid : null;
        $groups = isset($groups) ? $groups : null;
        $groups = json_decode($groups);
        #this code added for oncloud version 9 -- start
        if(!empty($userid)){
            $connection = \OC::$server->getDatabaseConnection();
            $query = "DELETE FROM *PREFIX*group_admin WHERE uid='".$userid."' ";
            $sth =$connection->prepare($query);
            $sth->execute();
        }

        if (count($groups)) {
            foreach ($groups as $group) {
            $query = "INSERT INTO *PREFIX*group_admin (uid,gid) VALUES ('".$userid."','".$group."') ";
            $sth =$connection->prepare($query);
            $sth->execute();
            }
            #this code added for oncloud version 9 -- end
            $data = array();
            $message = 'OK';
            $status = 'ok';
        } else {
             $message = 'No groups are selected';
             $data = array();
             $status = 'error';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

    /**
    * @NoCSRFRequired
    */
     public function suspendUser(string $userid) {
        $userid = isset($userid) ? $userid : null;
        $connection = \OC::$server->getDatabaseConnection();
        #this code added for oncloud version 9 -- start
        $query = "SELECT uid FROM *PREFIX*users where uid='".$userid."' ";
        $sth =$connection->prepare($query);
        $sth->execute();
        $users = $sth->fetchAll();
        if (empty($users)) {
             $message = 'The requested user could not be found';
             $data = array();
             $status = 'error';
        } else {
           \OC::$server->getConfig()->setUserValue($userid, 'core', 'enabled', 'false');
           \OCP\Util::writeLog('ocs_api', 'Successful suspendUser call from WHMCS: ' . $userid, 3);
           $data = array();
           $message = 'Successful suspendUser call from WHMCS: ' . $userid;
           $status = 'ok';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

    /**
    * @NoCSRFRequired
    */
     public function unsuspendUser(string $userid) {
        $userid = isset($userid) ? $userid : null;
        $connection = \OC::$server->getDatabaseConnection();
        #this code added for oncloud version 9 -- start
        $query = "SELECT uid FROM *PREFIX*users where uid='".$userid."' ";
        $sth =$connection->prepare($query);
        $sth->execute();
        $users = $sth->fetchAll();
        if (empty($users)) {
             $message = 'The requested user could not be found';
             $data = array();
             $status = 'error';
        } else {
           \OC::$server->getConfig()->setUserValue($userid, 'core', 'enabled', 'true');
           \OCP\Util::writeLog('ocs_api', 'Successful unsuspendUser call from WHMCS: ' . $userid, 3);
           $data = array();
           $message = 'Successful unsuspendUser call from WHMCS: ' . $userid;
           $status = 'ok';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }

    /**
    * @NoCSRFRequired
    */
    public function delUserValue(string $userid, string $app, string $key){

        $userId = isset($userid) ? $userid : null;
        $appId = isset($app) ? $app : null;
        $key = isset($key) ? $key : null;

        $connection = \OC::$server->getDatabaseConnection();
        #this code added for oncloud version 9 -- start
        $query = "SELECT uid FROM *PREFIX*users where uid='".$userid."' ";
        $sth =$connection->prepare($query);
        $sth->execute();
        $users = $sth->fetchAll();
        if (empty($users)) {
            $message = 'The requested user could not be found';
            $data = array();
            $status = 'error';
        } else {
            \OC::$server->getConfig()->deleteUserValue($userId, $appId, $key);
            \OCP\Util::writeLog('ocs_api', 'Successful userValue deleted: ' . $userId, 3);
            $data = array();
            $message = 'Successful userValue deleted: ' . $userId;
            $status = 'ok';
        }
        $result = $this->resDataFormat($data,$status,$message);
        return new JSONResponse($result);
    }


    /**
    * @NoCSRFRequired
    */
    public function changeUserPassword(string $userid, string $password){
        //echo $this->groupManager->isAdmin('somthakur'); die('JMD');
        //error_reporting(E_ALL); ini_set('display_errors', '1');
        $userId = isset($userid) ? $userid : null;
        //$user = $this->userManager->get($userId);
        $targetUser = $this->userManager->get($userId);
        $targetUser->setPassword($password);
    }


}
