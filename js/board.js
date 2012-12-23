/* 
 * BOARD CONTROLLER
 * Main controler for the site
 */

var board = {
    boardHash: null,
    spaceID: null,
    spaceVersion: null,
    areaID: null,
    areaName: null,
    areaVersion: null,
    locked: false,
    aniTime: 500,
    
    //What happens onload
    onLoad: function() {
        var url = this.readURL();
        
        if(url != false) {
            this.joinSpace(url);
        } else {
            this.showLogin();
        }
        
        this.addListeners();
    },
    
    //Shows login
    showLogin: function(status, err) {
        if(!status) {
            status = 'Create a dropboard';
        }
        
        if(!err) {
            err = false;
        }
        
        server.callContent('loadform', function(d) {
               msgBoard.show(d);
               msgBoard.setStatus(status, err);
         }) 
    },
    
    //Creates a new board from the form
    createSpace: function() {
        var boardName = $('#makeBoard input').val();
        if(boardName == "Name your board" || boardName.length <= 0) {
            msgBoard.setStatus('Please enter something!', true);
            return false;
        }
        
        server.apiCall('createSpace', boardName, function(d) {
           d = d[0];
           if(d.ret == true) {
              board.setBoardHash(d.Hash);
              board.setSpaceID(d.SpaceID);
              board.setSpaceVersion(d.SpaceVersion);
              board.openArea(d.AreaID);
              msgBoard.hide();
           } else {
               msgBoard.setStatus(d.ErrorMsg, true);
           }
        })
        
        return true;
    },
    
    //Joins a space
    joinSpace: function(urlData) {
        if(!urlData) {
            return false;
        }
        
        //Remembers the data
        this.setBoardHash(urlData.h);
        
   
        //Get board data from server
        server.apiCall('joinSpace', this.boardHash, function(d) {
           d = d[0];
           if(d.ret == true) {
               board.setSpaceID(d.SpaceID);
               board.setSpaceVersion(d.SpaceVersion);
               board.openArea(urlData.a);
           } else {
               board.showLogin(d.ErrorMsg, true);
           }
         });
         
         return true;
    },
    
    //Opens up an area
    openArea: function(areaID) {
        if(!areaID) {
            return false;
        }
        
        server.apiCall('getAreaByID', areaID, function(d) {
            d = d[0];
            
            if(d.ret == false) {
                board.showLogin(d.ErrorMsg, true);
            }
            
            board.setAreaName(d.Name);
            board.setAreaID(d.ID);
            board.setAreaVersion(d.Version);
            msgBoard.hide();
            posts.loadPosts();
            server.pollArea();
            board.makeURL();
        })
        
        return true;
    },
    
    //Is called when new version of area is returned
    updateArea: function(d) {
        if(d.length < 0) {
            return false;
        }
        
        d = d[0][0];
        
        if(this.areaName != d.Name) {
            board.setAreaName(d.Name);
        }
        
        if(this.areaID != d.ID) {
            board.setAreaID(d.ID);
        }
        
        board.setAreaVersion(d.ver);
        board.unlockArea();
        posts.update();
        server.pollArea();
    },
    
    //Locks area down for editing
    lockArea: function() {
        $('#areaLock').fadeTo(this.aniTime, 0.5);
        msgBoard.setStatus("Updating board...", true);
    },
    
    unlockArea: function() {
        $('#areaLock').fadeOut(this.aniTime);
    },
    
    //Sets value of board hash
    setBoardHash: function(hash) {
        if(hash.length <= 0) {
            return false;
        }
        
        board.boardHash = hash;
        return true;
    },
    
    //Sets value of spaceID
    setSpaceID: function(spaceid) {
        if(spaceid < 0) {
            return false;
        }
        
        board.spaceID = spaceid;
        return true;
    },
    
    //Sets space version
    setSpaceVersion: function(version) {
        if(version < 0) {
            return false;
        }
        
        this.spaceVersion = version;
        return true;
    },
    
    //Sets value of area id
    setAreaID: function(areaID) {
        if(areaID < 0) {
            return false;
        }
        
        board.areaID = areaID;
        return true;
    },
    
    //Sets the area version
    setAreaVersion: function(version) {
        if(version < 0) {
            return false;
        }
        
        this.areaVersion = version;
        return true;
    },
    
    
   //Sets area name
    setAreaName: function(areaName) {
        if(!areaName) {
            return false;
        }
        
        board.areaName = areaName;
        msgBoard.setStatus(this.areaName, false);
        return true;
    },
    
    //Renders the URL to the user
    makeURL: function() {
        var url = "";
        if(board.boardHash) {
            url += "h/" + board.boardHash + "/";
        }
        
        if(board.areaID) {
            url += "a/" + board.areaID + "/";
        }
        
        window.location.hash = url;
        return true;
    },
    
    //Returns URL data
    readURL: function() {
        var url = window.location.hash;
        url = url.replace("#", "");
        url = url.split("/");
        
        var l = url.length;
        if(l <= 1) { //Return false if the URL has nothing in it
            return false;
        }
        
        var ret = {};
        
        for(var i = 0; i <= l; i++) {
            var option = url[i];
            i++;
            var value = url[i];
            
            if(option && value) {
                ret[option] = value;
            }
        }
        
        return ret;
    },
    
    //Adds listeners on the board
    addListeners: function() {
        
      //New post
      $('#addNewPost').click(function() {
          posts.createPost();
          console.log('Event fired');
      })
    }
}

$('document').ready(function() {
   board.onLoad();  //Run the board
   
   $(window).bind('hashchange', function() {
       board.onLoad();
   })
   
});
