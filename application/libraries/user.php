<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user
 *
 * @author Baagoe
 */
class user {
    public function __construct() {
        $this->ci = get_instance();
    }
    
    //Creates a new user session
    public function createUser($spaceId = null, $usename = null) {
        if($this->getUserID() != false) { //If user already is created in the system
            //return false;
        }
        
        if(!is_numeric($spaceId)) {
            return false;
        }
        
        if($usename == null) {
            $usename = uniqid('User-');
        }
        
        $d = array("SessionID" => $this->makeUserId(), "Username" => $usename, "SpaceID" => $spaceId);
        $this->ci->session->set_userdata($d);
        return true;
    }
    
    //Sets a users username
    public function setUsername($username = null) {
        if(!is_string($username)) {
            return false;
        }
        
        $this->ci->session->set_userdata('Username', $username);
        return true;
    }
    
    //Returns the user
    public function getUserData() {
        if($this->getUserID() == false) {
            return false;
        }
        
        $user = $this->ci->session->all_userdata();
        return $user;
    }
    
    //Returns a specific user item
    public function getUserItem($item = null) {
        if(!is_string($item) || $this->getUserID() == false) {
            return false;
        }
        
        return $this->ci->session->userdata($item);
    }
    
    //Returns session space ID
    public function getSpaceID() {
        if($this->getUserID()) {
            $id = $this->getUserItem('SpaceID');
            return $id;
        }
        return false;
    }
    
    //Returns the current userID
    public function getUserID() {
        $userId = $this->ci->session->userdata("SessionID");
        if(!empty($userId)) {
            return $userId;
        } else {
            return false;
        }
    }
    
    //Delete user
    public function removeUser() {
        $this->ci->session->sess_destroy();
    }
    
    //Creates a unique userid
    public function makeUserId() {
        $id = uniqid(null, true);
        $id = md5($id);
        return $id;
    }
}

?>
