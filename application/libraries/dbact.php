<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dbact
 *
 * @author Baagoe
 */
class DbAct {

    public function __construct() {
        $this->ci = get_instance();
        return true;
    }

    /* SPACE METHODS */

    //Creates a new space in the database
    public function createSpace($spaceName, $hash) {
        if (!is_string($spaceName) && empty($spaceName) && empty($hash)) {
            return false;
        }

        $ins = array("Name" => $spaceName, "HashID" => $hash);
        $this->ci->db->insert('Spaces', $ins);
        return true;
    }

    //Returns a specific space by name
    public function getSpaceByName($spaceName) {
        if (!is_string($spaceName) && empty($spaceName)) {
            return false;
        }

        $res = $this->ci->db->get_where('Spaces', array("Name" => $spaceName), 1);
        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }

    //Returns a specific space by id
    public function getSpaceById($spaceID) {
        if (!is_numeric($spaceID)) {
            return false;
        }

        $res = $this->ci->db->get_where('Spaces', array("ID" => $spaceID), 1);
        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }

    //Returns a specific space by hash value
    public function getSpaceByHash($hashID) {
        if (empty($hashID)) {
            return false;
        }

        $res = $this->ci->db->get_where('Spaces', array("HashID" => $hashID));

        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }

    //Delete a space by id
    public function deleteSpaceById($spaceID) {
        if (!is_numeric($spaceID)) {
            return false;
        }

        $this->ci->db->delete('space', array("ID" => $spaceID));
        return true;
    }
    
    //Edits a space by its id
    public function editSpaceByID($spaceID, $inp) {
        if(!is_numeric($spaceID) || !is_array($inp)) {
            return false;
        }
        
        $this->ci->db->update('spaces', $inp, array("ID" => $inp));
        $this->newSpaceVersion($spaceID);
        return true;
    }
    
    //Updates a space version by 1
    public function newSpaceVersion($spaceID) {
        if(!is_numeric($spaceID)) {
            return false;
        }
        
        $space = $this->getSpaceById($spaceID);
        if($space == false) {
            return false;
        }
        
        $spaceVersion = $space->row()->ver;
        $spaceVersion++;
        $this->ci->db->update('spaces', array("ver" => $spaceVersion), array("ID" => $spaceID));
        
        return true;
    }

    /* AREA METHODS */

    //Add a new area
    public function createArea($inp) {
        if (!is_array($inp) || !is_numeric($inp['SpaceID']) || !is_string($inp['Name'])) {
            return false;
        }

        $this->ci->db->insert('areas', $inp);
        $this->newSpaceVersion($inp['SpaceID']);
        return true;
    }

    //Returns a list of all areas connected to a specific space
    public function getAreaBySpaceID($spaceID) {
        if (!is_numeric($spaceID)) {
            return false;
        }

        $res = $this->ci->db->get_where('areas', array("SpaceID" => $spaceID));
        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }

    //Returns an area by ID
    public function getAreaByID($areaID) {
        if (!is_numeric($areaID)) {
            return false;
        }

        $res = $this->ci->db->get_where('areas', array("ID" => $areaID));
        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }

    //Removes all areas by specific space ID
    public function deleteAreaBySpaceID($spaceID) {
        if (!is_numeric($spaceID)) {
            return false;
        }

        $this->ci->db->delete('areas', array("SpaceID" => $spaceID));
        $this->newSpaceVersion($spaceID);
        return true;
    }

    //Remove an area by AreaID
    public function deleteAreaByID($areaID = null) {
        if (is_null($areaID)) {
            return false;
        }
        
        $area = $this->getAreaByID($areaID);
        if($area == false) {
            return false;
        }
        
        $spaceID = $area->row()->SpaceID;

        $this->ci->db->delete('areas', array("ID" => $areaID));
        $this->newSpaceVersion($spaceID);
        return true;
    }
    
    //Edit area by ID
    public function editAreaByID($areaID, $inp) {
        if(!is_numeric($areaID) || !is_array($inp)) {
            return false;
        }
        
        $area = $this->getAreaByID($areaID);
        if($area == false) {
            return false;
        }
        
        $spaceID = $area->row()->SpaceID;
        
        $this->ci->db->update('areas', $inp, array("ID" => $areaID));
        $this->newSpaceVersion($spaceID);
        $this->newAreaVersion($areaID);
        return true;
    }
    
    //Updates a area version by 1
    public function  newAreaVersion($areaID) {
        if(!is_numeric($areaID)) {
            return false;
        }
        
        $area = $this->getAreaByID($areaID);
        if($area == false) {
            return false;
        }
        
        $version = $area->row()->ver;
        $version++;
        $this->ci->db->update('areas', array("ver" => $version), array("ID" => $areaID));
        
        return true;
    }


    /* MESSAGE METHODS */

    //Creates a new messages on a area
    public function createMsg($inp) {
        if (!is_array($inp) || !is_numeric($inp['AreaID']) || empty($inp['ContentData'])) {
            return false;
        }
        
        $inp['Status'] = STATUS_NEW_MSG;
        $this->ci->db->insert('areaContent', $inp);
        $this->newAreaVersion($inp['AreaID']);
        return true;
    }

    //Removes message by AreaID
    public function deleteMsgByAreaID($areaID) {
        if (!is_numeric($areaID)) {
            return false;
        }
        
        $inp = array("Updated" => STATUS_UPDATED, "Status" => STATUS_DELETE);
        $this->ci->db->update('areaContent', $inp, array("AreaID" => $areaID));
        return true;
    }

    //Remove a message by ID
    public function deleteMsgByID($msgID) {
        if (!is_numeric($msgID)) {
            return false;
        }
        
        $msg = $this->getMsgByID($msgID);
        if($msg == false) {
            return false;
        }
        
        $areaID = $msg->row()->AreaID;
        
        $this->ci->db->delete('areaContent',array("ID" => $msgID));
        $this->newAreaVersion($areaID);
        return true;
    }

    //Returns all messages by areaID
    public function getMsgByAreaID($areaID) {
        if (!is_numeric($areaID)) {
            return false;
        }

        $res = $this->ci->db->get_where('areaContent', array("AreaID" => $areaID));
        if ($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }
    
    //Returns a message by messageID
    public function getMsgByID($msgID) {
        if(!is_numeric($msgID)) {
            return false;
        }
        
        $res = $this->ci->db->get_where('areaContent', array("ID" => $msgID));
        if($res->num_rows > 0) {
            return $res;
        } else {
            return false;
        }
    }
    
    //Edits a message by ID
    public function editMsgByID($msgID, $inp) {
        if(!is_numeric($msgID) || !is_array($inp)) {
            return false;
        }
        
        $msg = $this->getMsgByID($msgID);
        if($msg == false) {
            return false;
        }
        
        $areaID = $msg->row()->AreaID;
        
        $inp['Status'] = STATUS_EDIT_MSG;
        $this->ci->db->update('areaContent', $inp, array("ID" => $msgID));
        $this->newAreaVersion($areaID);
        $this->newMsgVersion($msgID);
        
        return true;
    } 
    
   //Updates a message by version
    public function  newMsgVersion($msgID) {
        if(!is_numeric($msgID)) {
            return false;
        }
        
        $msg = $this->getMsgByID($msgID);
        if($msg == false) {
            return false;
        }
        
        $version = $msg->row()->ver;
        $version++;
        $this->ci->db->update('areaContent', array("ver" => $version), array("ID" => $msgID));
        
        return true;
    }
    
    
    /* POLLING METHODS */
    
    //Returns space row, where version dont match
    public function newVersionOfSpace($spaceID, $version) {
        if(!is_numeric($spaceID) || !is_numeric($version)) {
            return false;
        }
        
        $this->ci->db->select('ID')->from('spaces')->where("ID == $spaceID && ver > $version");
        $res = $this->ci->db->get();
        if($res->num_rows > 0) {
            return $this->getSpaceById($spaceID);
        } else {
            return false;
        }
    }
    
    //Returns area row, where version dont match
    public function newVersionOfArea($areaID, $version) {
        if(!is_numeric($areaID) || !is_numeric($version)) {
            return false;
        }
        
        $sql = "CALL getAreaUpdates(?, ?)";
        $parms = array($areaID, $version);
        $res = $this->ci->db->query($sql, $parms);
        
        if($res->num_rows > 0) {
            $res->next_result();
            $res->free_result();
            return $this->getAreaByID($areaID);
        } else {
            $res->next_result();
            $res->free_result();
            return false;
        }
    }
    
    //Returns messages by version
    public function newVersionOfMsg($msgID, $version) {
        if(!is_numeric($msgID) || !is_numeric($version)) {
            return false;
        }
        
        $this->ci->db->select('ID')->from('areaContent')->where("ID == $msgID && ver > $version");
        $res = $this->ci->db->get();
        
        if($res->num_rows > 0) {
            return $this->getMsgByID($msgID);
        } else {
            return false;
        }
    }

}

?>
