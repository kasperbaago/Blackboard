/* 
 * SERVER CONTROLLER
 * Controls the interaction with the server
 */

var server = {
    contentUrl: "index.php/board/",
    apiUrl: "index.php/api/",
    pollUrl: "index.php/api/poll/",
    talkink: false,
    showError: true,
    
    callContent: function(content, callback) {
        if(!content || !callback) {
            return false;
        }
        
        var url = this.contentUrl + content;
        
        $.ajax({
            method: "get",
            url: url,
            success: callback
        });
        return true;
    },
    
    apiCall: function(action, dataString, callback) {
        var url = this.apiUrl + action + "/" + dataString;
        
        $.ajax({
            type: "get",
            url: url,
            dataType: 'json',
            success: function(d) {
                    server.talkink = false;
                    e = d[0];
                    
                    if(e.ret == false) {
                        server.error(e.ErrorMsg);
                        return false;
                    }
                    
                    if(callback) {
                        callback(d);
                    }
                    return true;
              }
        });
    },
    
    apiPostCall: function(action, postData, callback) {
          var url = this.apiUrl + action;
          $.ajax({
              type: "post",
              url: url,
              data: postData,
              daataType: 'json',
              success: function(d) {
                    e = d[0];
                    
                    if(e.ret == false) {
                        server.error(e.ErrorMsg);
                        return false;
                    }
                    
                    if(callback) {
                        callback(d);
                    }
                    return true;
              }
          })
    },
    
    pollArea: function() { 
        var url = this.apiUrl + 'pollArea/' + board.areaID + "/" + board.areaVersion;
        
        $.ajax({
            method: "get",
            url: url,
            dataType: 'json',
            success: board.updateArea
        })
        
        return true;
    },
    
    error: function(errMsg) {
        if(this.showError && errMsg) {
            console.log("Server Error: " + errMsg);
        }
    }
}


