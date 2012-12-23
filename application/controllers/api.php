<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * API CONTROLLER
 */

class API extends CI_Controller {
    private $outputToScrren = true;
    private $headersPrintet = false; //Is true of headers is already printed
    
    public function __construct() {
        parent::__construct();
        $this->load->library(array('dbact', 'user'));
    }
    
	public function index()
	{
		$this->apiReturn(array("Hallo" => "BlackBoard API!"));
	}
        
        /* SPACES */
        //Creates a new space, user and returns SpaceID and a AreaID
        public function createSpace($name = null, $output = true) {
            if($this->getUrl('name')) {
                $name = $this->getUrl('name');   
            }
            
            if(!is_string($name) && empty($name)) {
                $this->apiReturn(array(ERROR_MESSAGE => SPACE_NAME_NOT_GIVEN, RET => false), $output);
                return false;
            }
            
            
            if($this->dbact->getSpaceByName($name) != false) { //Cheking if board name already has been taken
                $this->apiReturn(array(ERROR_MESSAGE => SPACE_ALREADY_EXISTS));
                return false;
            }
            
            $hash = $this->makeHash(); // Creates a unique hash value for the space
            $this->dbact->createSpace($name, $hash);
            $space = $this->dbact->getSpaceByHash($hash);
            
            if($space == false) {
                $this->apiReturn(array(ERROR_MESSAGE => SPACE_NOT_CREATED), $output);
                return false;
            } else {
                $spaceId = $space->row()->ID;
                if(is_numeric($spaceId) == false) {
                    $this->apiReturn(array(ERROR_MESSAGE => NO_SPACEID), $output);
                    return false;
                }
            }
            
            if(!$this->user->createUser($spaceId)) {
                $this->removeSpace($spaceId, false);
                $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_CREATED), $output);
                return false;
            } else {
                if(!$this->createArea($name, false)) {
                    $this->apiReturn(array(ERROR_MESSAGE => AREA_NOT_CEREATED));
                    return false;
                }
            }
            
            $space = $this->dbact->getSpaceByHash($hash);
            $area = $this->dbact->getAreaBySpaceID($spaceId)->row();
            
            $ret = array(STATUS_MESSAGE => SPACE_CREATED, 'SpaceID' => $spaceId, 'SpaceVersion' => $space->row()->ver, 'Hash' => $hash, 'AreaID' => $area->ID, 'AreaName' => $area->Name, "AreaVersion" => $area->ver);
            $this->apiReturn($ret, $output);
            return true;
        }
        
        //Removes a space and all its boards
        public function removeSpace($hash = null, $output = true) {
            if($this->getUrl('hash')) {
                $hash = $this->getUrl('hash');
            }
            
            
            if(!is_numeric($hash) || $this->dbact->getSpaceByHash($hash)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_HASH), $output);
                return false;
            }   
            
            //Gets the space data
            $space = $this->dbact->getSpaceByHash($hash);
            $space = $space->row();
            
            //Delete areas and it's content
            $areas = $this->dbact->getAreaBySpaceID($space->ID);
            if($areas != false) {
                foreach($areas as $row) {
                    $this->dbact->deleteMsgByAreaID($row->ID);
                }
                $this->dbact->deleteAreaBySpaceID($space->ID);
            }
            
            //Deletes the space
            $this->dbact->deleteSpaceById($space->ID);
            $this->apiReturn(array(STATUS_MESSAGE => SPACE_DELETED), $output);
            return true;
        }
        
        //Joins a specific space
        public function joinSpace($hash = null, $output = true) {
            if($this->getUrl('hash')) {
                $hash = $this->getUrl('hash');
            }
            
            if(empty($hash)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_HASH), $output);
                return false;
            }
            
            $space = $this->dbact->getSpaceByHash($hash);
            if($space == false) {
                $this->apiReturn(array(ERROR_MESSAGE => SPACE_NOT_EXIST), $output);
                return false;
            }
            
            $space = $space->row();
            $this->user->createUser($space->ID);
            $this->apiReturn(array(STATUS_MESSAGE => SPACE_JOINED, "SpaceID" => $space->ID, "SpaceVersion" => $space->ver), $output);
            return true;
        }
        
       //Logs user out
      public function exitSpace() {
          if(!$this->user->getUserID()) {
              $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_EXIST));
              return false;
          }
          
          $this->user->removeUser();
          $this->apiReturn(array(STATUS_MESSAGE => USERNAME_LOG_OUT));
          return true;
      }
        
        
        /* AREAS */
        //Creates a new area in the space
        public function createArea($areaName = null, $output = true) {
            $spaceID = $this->user->getUserItem('SpaceID');
            
            if($this->getUrl('name')) {
                $areaName = $this->getUrl('name');
            }
            
            if($areaName == null || is_numeric($spaceID) != true) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_SPACE_OR_NAME_GIVEN));
                return false;
            }
            
            if($this->dbact->createArea(array("Name" => $areaName, "SpaceID" => $spaceID))) {
                $this->apiReturn(array(STATUS_MESSAGE => AREA_CREATED), $output);
                return true;  
            } else {
                $this->apiReturn(array(ERROR_MESSAGE => DB_ERROR), $output);
                return false;
            };
        }
        
        //Returns all areas for this session
        public function getAreas() {
            if(!$this->user->getUserID()) {
                $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_EXIST));
                return false;
            }
            
            $spaceID = $this->user->getSpaceID();
            $areas = $this->dbact->getAreaBySpaceID($spaceID);
            $areas = $areas->result_array();
            $this->apiReturn($areas);
            return true;
        }
        
        //Returns a specific area
        public function getAreaByID($area = null) {
            if($this->getUrl('area')) {
                $area = $this->getUrl('area');
            }
            
            if(!is_numeric($area)) {
                $this->apiReturn(array(ERROR_MESSAGE => AREA_ID_NOT_GIVEN));
                return false;
            }
            
            if(!$this->userHasAcess($area)) {
                return false;
            }
            
            $area = $this->dbact->getAreaByID($area);
            
            if($area == false) {
                $this->apiReturn(array(ERROR_MESSAGE => AREA_NOT_EXIST));
                return false;
            }
            
            $area = $area->row();
            $this->apiReturn(array("ID" => $area->ID, "Name" => $area->Name, "SpaceID" => $area->SpaceID, "Version" => $area->ver));
            return true;
        }
        
        //Removes a area by its ID
        public function removeArea($areaID = null, $output = true) {
            if($this->getUrl('areaid')) {
                $areaID = $this->getUrl('areaid');
            }
            
            if($areaID == null) {
                $this->apiReturn(array(ERROR_MESSAGE => AREA_ID_NOT_GIVEN));
                return false;
            }
            
            $this->dbact->deleteAreaByID($areaID);
            $this->apiReturn(array(STATUS_MESSAGE => AREA_DELETED));
            return true;
        }
        
        /* Area messagefunctions */
        //Returns a list of all messages on a board
        public function getPostsByArea($areaID = null) {
            if($this->getUrl('area')) {
                $areaID = $this->getUrl('area');
            }
            
            if(!is_numeric($areaID)) {
                $this->apiReturn(array(ERROR_MESSAGE => AREA_ID_NOT_GIVEN));
                return false;
            }
            
            if(!$this->userHasAcess($areaID)) {
                return false;
            }
            
            $posts = $this->dbact->getMsgByAreaID($areaID);
            if($posts) {
                $posts = $posts->result_array();
                $this->apiReturn(array("posts" => $posts));
            } else {
                $this->apiReturn(array(STATUS_MESSAGE => NO_MESSAGES));
            }
            
            return true;
        }
        
        //Puts a post on the board
        public function putPost() {
            
            if(!$_POST) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            //Test URL for input
            if(isset($_POST['msg'])) {
                $msg = $_POST['msg'];
            }
            
            if(isset($_POST['area'])) {
                $areaID = $_POST['area'];
            }
            
            
            //If input not is given
            if(!is_numeric($areaID) || empty($msg)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            //Checks to see if user has access to area
            if(!$this->userHasAcess($areaID)) {
                return false;
            }
            
            $inp = array("AreaID" => $areaID, "ContentData" => $msg);
            $this->dbact->createMsg($inp);
            $this->apiReturn(array(STATUS_MESSAGE => MESSAGE_SUCCESSFULLY_CREATED));
            
            return true;
        }
        
        //Edit post onboard
        public function editPost() {
            
            if(!$_POST) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            //Test URL for input
            if(isset($_POST['postid'])) {
                $postID = $_POST['postid'];
            }
            
            if(isset($_POST['msg'])) {
                $msg = $_POST['msg'];
            }
            
            //Return false if no input is detected
            if(!is_numeric($postID) || empty($msg)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            
            $post = $this->dbact->getMsgByID($postID);
            if($post == false) {
                $this->apiReturn(array(ERROR_MESSAGE => MESSAGE_NOT_EXIST));
                return false;
            }
            
            $post = $post->row(); //Fetching a row
            $areaID = $post->AreaID;
            
            if(!$this->userHasAcess($areaID)) {
                return false;
            }
            
            $inp = array("ContentData" => $msg);
            $this->dbact->editMsgByID($postID, $inp);
            
            $this->apiReturn(array(STATUS_MESSAGE => MESSAGE_SUCCESSFULLE_EDITED));
            return true;
        }
        
        //Deltes a post on the board
        public function deletePost($postid) {
            if($this->getUrl('postid')) {
                $postid = $this->getUrl('postid');
            }
            
            if(!is_numeric($postid)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            $post = $this->dbact->getMsgByID($postid);
            if($post == false) {
                $this->apiReturn(array(ERROR_MESSAGE => MESSAGE_NOT_EXIST));
                return false;
            }
            
            $post = $post->row();
            $areaid = $post->AreaID;
            
            if(!$this->userHasAcess($areaid)) {
                return false;
            }
            
            $this->dbact->deleteMsgByID($postid);
            $this->apiReturn(array(STATUS_MESSAGE => MESSAGE_SUCCESSFULLY_DELETED));
            return true;
        }
        
        /* POLL Functions */
        
        //Pools a specific area for version updates
        public function pollArea($areaID = null, $version = null) {
            if($this->getUrl('areaid')) {
                $areaID = $this->getUrl('areaid');
            }
            
            if($this->getUrl('version')) {
                $version = $this->getUrl('version');
            }
            
            if(!is_numeric($areaID) || !is_numeric($version)) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_PARM));
                return false;
            }
            
            if(!$this->userHasAcess($areaID)) {
                return false;
            }
            
            set_time_limit(0); //Time cannot run out, persistant connection
            $this->printHeader('json', true);
            
            //Updating the DB
            for(;;) {
                
                $area = $this->dbact->newVersionOfArea($areaID, $version); //Chekking for updated version
                if($area != false) {
                    break;
                }
                sleep(2);
            }
            
            
            $area = $area->result_array();
            $area[STATUS_MESSAGE] = AREA_UPDATED;
            $this->apiReturn($area);
            return true;
        }
        
        private function printHeader($format = "json", $noCache = false) {
            $this->headersPrintet = true;
            header("HTTP/1.1 200 OK");
            if($noCache) {
            header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate"); // HTTP/1.1
            header('pragma: no-cache');
            }
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            
            switch($format) {
                case 'xml':
                    header('Content-type: application/xml');
                    break;
                case 'json':
                    header('Content-type: application/json');
                    break;
                defualt:
                    break;
            }
            flush();
        }
        
        
       /* USER FUNCTIONS */
      //Returns all the info the systems knows about the current user
      public function getUserInfo() {
          if($this->user->getUserID()) {
              $username = $this->user->getUserItem('Username');
              $spaceID = $this->user->getUserItem('SpaceID');
              $sessionID = $this->user->getUserItem('SessionID');
              
              $user = array("Username" => $username, "SpaceID" => $spaceID, "SessionID" => $sessionID);
              $this->apiReturn(array("UserData" => $user));
              return true;
          } else {
              $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_EXIST));
              return false;
          }
      }
      
      //Sets the username
      public function setUsername($username = null, $output = true) {
          if($this->getUrl('username')) {
              $username = $this->getUrl('username');
          }
          
          if(!is_string($username)) {
              $this->apiReturn(array(ERROR_MESSAGE => USERNAME_NOT_GIVEN), $output);
              return false;
          }
          
          if(!$this->user->getUserID()) {
              $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_EXIST), $output);
              return false;
          }
          
          $this->user->setUsername($username);
          $this->apiReturn(array(STATUS_MESSAGE => USERNAME_SET), $output);
          return true;
      }
      
      //Returns true/false if user has access to write to an area
      private function userHasAcess($areaID) {
            if(!is_numeric($areaID)) {
                return false;
            }
          
            $spaceID = $this->user->getSpaceID();
            if(!is_numeric($spaceID)) {
                $this->apiReturn(array(ERROR_MESSAGE => USER_NOT_CREATED));
                return false;
            }
            
            $area = $this->dbact->getAreaByID($areaID);
            if($area == false) { //Does the given area exists in the database
                $this->apiReturn(array(ERROR_MESSAGE => AREA_NOT_EXIST));
                return false;
            }
            
            $area = $area->row(); //Get the first row
            if($area->SpaceID != $spaceID) {
                $this->apiReturn(array(ERROR_MESSAGE => NO_ACCESS));
                return false;
            }
            
        return true;
      }


      /* API RETURN */
        
      //Returns API call to browser
      private function apiReturn($inp = array(), $output = true) {
          if(is_array($inp) == false) {
              $this->apiReturn(array(RET => false));
          }
          
          //Return status
          if(isset($inp[RET])) {
              //Nothing happens if its already set!
          } else if(isset($inp[ERROR_MESSAGE])) {
              $inp[RET] = false;
          } else if(isset($inp[STATUS_MESSAGE])) {
              $inp[RET] = true;
          } else {
              $inp[RET] = true;
          }
          
          if($output == false) {
              return false;
          }
          
          $inp = array($inp);
          switch($this->getUrl('format')) {
              case "xml":
                  $ret = xmlrpc_encode($inp);
                  $format = 'xml';
                  break;
              default:
                  $ret = json_encode($inp);
                  $format = 'json';
              break;
          }
          
          if(!$this->headersPrintet) {
              $this->printHeader($format);
          }
          
          $this->load->view('api/return', array('data' => $ret));
          return true;
      }
      
      //Returns an element from the url
      private function getUrl($elm = null) {
          $url = $this->uri->uri_to_assoc();
          
          if($elm == null) {
              return $url;
          }
          
          
          if(isset($url[$elm])) {
              return $url[$elm];
          } else {
              return false;
          }
      }
      
      //Creates a unique hash value for the space
      private function makeHash() {
          $h = uniqid('', true);
          $h = hash("sha256", $h);
          return $h;
      }
}