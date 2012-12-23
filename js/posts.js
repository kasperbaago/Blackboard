/* 
 * HANDLES ALL POSTS ON A GIVEN AREA
 */

var posts = {
   aniTime: 500,
   editID: [], //Contins the ID for the post that is being edited
   postSize: 200,
   lengthOfPostBeingEdited: null, //Saves the length of post being edited
   editingMode: false, //Tells if editing is in place
    
    //Loads all posts on the current board
    loadPosts: function() {
        server.apiCall('getPostsByArea', board.areaID, function(d) {
            d = d[0];
            if(d.ret == false) {
                return false;
            }
            
            areaPosts = d.posts;
            
            if(!areaPosts) {
                return false;
            }
            
            postLength = areaPosts.length;
            for(var i = 0; i <= postLength; i++) {
                posts.putOnBoard(areaPosts[i]);
            }
            
            posts.listen(); //Sets up listeners
            return true;
        })
    },
    
    //Puts up a post on the board
    putOnBoard: function(post) {
        if(!post) {
            return false;
        }
        
        console.log(post);
        var content = JSON.parse(post.ContentData)
        content = content[0];
        content.id = post.ID;
        content.ver = post.ver;
        $('#area').append(this.createHTMLPost(content, false));
        $('#' + post.ID).hide();
        $('#' + post.ID).css({top: content.position.top, left: content.position.left});
        $('#' + post.ID).fadeIn(posts.aniTime);
        $('#' + post.ID).linkify({handleLinks: posts.linkHandler});
        return true;
    },
    
    //Creates a new post on the board
    createPost: function() {
        board.lockArea();
        var msgData = {
            position: posts.calcPostInitPos(),
            message: '',
            author: '',
        }
        
        var str = this.createJSONSting([msgData]);
        var dataString = 'area=' + board.areaID + '&msg=' + str; 
        
        server.apiPostCall('putPost', dataString);  
    },
    
    //Updates posts when something new has happened
    update: function() {
        $('.updated').removeClass('updated');
         server.apiCall('getPostsByArea', board.areaID, function(d) {
            d = d[0];
            if(d.ret == false) {
                return false;
            }
            
            areaPosts = d.posts;
            
            if(!areaPosts) { //If there are no posts at all, then remove all posts!
                $('.post').remove();
                return false;
            }
            
            if(areaPosts.length < 0) {
                return false;
            }
            
            postLength = areaPosts.length;
            
            
            for(var i = 0; i <= postLength + 1; i++) {
              if(i > postLength) {
                  posts.listen(); //Sets up listeners
                  $('.post').each(function() {
                      if($(this).hasClass('updated') != true) {
                          $(this).fadeOut(posts.aniTime, function() {
                              $(this).remove();
                          })
                      }
                  })
              } else {
                posts.updatePostOnBoard(areaPosts[i]);
              }
            }
            return true;
        })
    },
    
    //Updates the given post on the board
    updatePostOnBoard: function(post) {
        if(!post) {
            return false;
        }
        
        if($('#' + post.ID).attr('id')) {
            var content = JSON.parse(post.ContentData)
            content = content[0];
            $('#' + post.ID).addClass('updated');
            
            if($('#' + post.ID).attr('data-version') < post.ver && $('#' + post.ID).hasClass('beingEdited') == false) {
                $('#' + post.ID).html(this.createHTMLPost(content, true)).linkify({handleLinks: posts.linkHandler});
                $('#' + post.ID).animate({top: content.position.top, left: content.position.left}, posts.aniTime);
                $('#' + post.ID).attr('data-version', post.ver);
            }
        } else {
            this.putOnBoard(post); //Put a new post on the board
        }
        
        return true;
    },
    
    //Returns a html form of a given post
    createHTMLPost: function(data, noWrapper) {
        if(!data) {
            return false;
        }
        
        var html = "";
        
        if(noWrapper == false) {
            html += "<div id='" + data.id + "' class='post updated' contentEditable='false' data-version='" + data.ver + "'>\n";
        }
        html += "   <div class='postMessage'>";
        
        html += (noWrapper == false) ?  "<p>" : "";
        html += data.message;
        html += (noWrapper == false) ?  "</p>" : "";
        html += "</div>\n";
        html += "   <p class='poster'>" + "</p>\n";
        html += "   <div class='postDrag'></div>\n";
        html += "   <button class='savePost postBtn'>Save</button>\n";
        html += "   <button class='deletePost postBtn'>Delete Post</button>";
        
        if(noWrapper == false) {
            html += "</div>\n";
        }
        return html;
    },
    
        
    //Listens after clicks on posts
    listen: function() {
        
      $('.postMessage').click(function() {
          if(posts.editingMode == false) {
            var parent = $(this).parent('div').attr('id');
            posts.showPostBtn(parent);
            $(this).attr('contenteditable', true);
          }
      });
      
      //Saves the post
      $('.savePost').click(function() {
          var parent = $(this).parent('div').attr('id');
          posts.hidePostBtn(parent);
          $('#' + parent + ' .postMessage').attr('contenteditable', false);
          posts.updatePost(parent);
      })
      
      //Deltee a post
      $('.deletePost').click(function() {
          var parent = $(this).parent('div').attr('id');
          posts.hidePostBtn(parent);
          posts.deletePost(parent);
      })
      
      //Post moves
      $('.post').draggable({handle:".postDrag", stop: function(event, ui) {
              posts.updatePost(event.target.id, true);
      }});
  
      $('.postBtn').hover(function() {
          $(this).stop().animate({bottom: "-35px"}, this.aniTime);
      }, function() {
          $(this).stop().animate({bottom: "-30px"}, this.aniTime);
      })
      
            
      //On drop event
      if(typeof window.FileReader !== "undefined") { //If filereader exists
          $('.postMessage').bind('dragover dragend', function() {
              return false;
          })
          
          $('.postMessage').bind('drop', function(e) {
              if(posts.editingMode == false) {
                  return false;
              }
              console.log("POST!");
              e = e || window.event;
              e.preventDefault();
              e = e.originalEvent || e;
              
              var div = $(this);
              var imgTag = $("<img src='' title='' alt='' width='480' />");
              var file = (e.files || e.dataTransfer.files[0]), reader = new FileReader();
              reader.onload = function(event) {
                  //console.log(event.target.result);
                  var img = imgTag.clone().attr({
                      src: event.target.result.replace('"',"'"),
                      title: file.name,
                      alt: file.name
                  });
                  
                  div.append(img);
                  
              }
              
              reader.readAsDataURL(file)
              return false;
          })
      }
    },
    
    //Shows the post editing buttons
    showPostBtn: function(post) {
      if(!post) {
          return false;
      }
      
      $('#' + post).addClass('beingEdited');
      $('#' + post).children('.postBtn').stop().animate({bottom: "-30px"});
      this.editingMode = true;
      return true;
    },
    
    //Hides the post bottons
    hidePostBtn: function(post) {
        if(!post) {
            return false;
        }
        
        $('#' + post).removeClass('beingEdited');
        $('#' + post).children('.postBtn').stop().animate({bottom: "0px"});
        this.editingMode = false;
        return true;
    },
    
    updatePost: function(id, move, callback) {
        if(!id) {
            return false;
        }
        
        if(!move) {
            move = false;
        }
        
        if(move == true || posts.lengthOfPostBeingEdited != $('#' + id + ' .postMessage').html().length) {
        
        var msgData = {
            position: $('#' + id).position(),
            message: $('#' + id + ' .postMessage').html(),
            author: $('#' + id + ' .poster').html()
        }
        msgData = [msgData];
        
        var str = this.createJSONSting(msgData);
        var dataString = {postid: id, msg: str}
        dataString = $.param(dataString);
        
        server.apiPostCall('editPost', dataString, callback);
        board.lockArea();
        
        } else {
            posts.lengthOfPostBeingEdited = null;
        }
        
        return true;
    },
    
    deletePost: function(postid) {
      if(!postid) {
          return false;
      }  
      
      board.lockArea();
      server.apiCall('deletePost', postid);
      return true;
    },
    
    createJSONSting: function(obj) {
        if(!obj) {
            return false;
        }
        
        return JSON.stringify(obj);
    },
    
    calcPostInitPos: function() {
        var left = $(window).width() / 2 - this.postSize;
        var top = $(window).height() / 2 - this.postSize;
        
        return {top: top, left: left}
    },
    
    linkHandler: function(link) {
        
        $(link).attr('target', '_blank');
    }
}


