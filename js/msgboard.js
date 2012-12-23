/* 
 * MSG BOARD
 * Controls the message board
 */

var msgBoard = {
    divId: "#msgBoard",
    aniTime: 500,
    showTop: 30,
    
    init: function() {
        msgBoard.listen();
    },
    
    //Gets the top center postion of the page
    posTop: function() {
        var top = $(window).height() / 2 - $(this.divId).height() / 2;
        var left = $(window).width() / 2 - $(this.divId).width() / 2;
        return {top: top, left: left};
    },
    
    show: function(content, callback) {
        if(!content) {
            content = "";
        }
        
        $(this.divId).css({zIndex: '1', left: msgBoard.posTop().left});
        $(this.divId + ' #content').html(content);
        this.formListen();
        $(this.divId).stop().animate({top: msgBoard.posTop().top}, this.aniTime, callback);
        $(this.divId + ' #statusBar').stop().animate({bottom: '75px'}, this.aniTime);
        this.hideMenu();
    },
    
    showMenu: function() {
        $(this.divId + ' nav ul').fadeIn(this.aniTime);
        this.addMenuToolTips();
    },
    
    addMenuToolTips: function() {
        $('nav li').hover(function() {
            $(this).children('div').stop().fadeTo(msgBoard.aniTime, 1);
        }, function() {
            $(this).children('div').stop().fadeTo(msgBoard.aniTime, 0);
        })
    },
    
    hideMenu: function() {
        $(this.divId + ' nav ul').fadeOut(this.aniTime);
    },
    
    hide: function(callback) {
        var h = -$(this.divId).height() + this.showTop;
        $(this.divId).css({zIndex: '1'});
        $(this.divId).stop().animate({top:  h, left: msgBoard.posTop().left}, this.aniTime, callback);
        $(this.divId + ' #statusBar').stop().animate({bottom: '12px'}, this.aniTime);
        this.showMenu();
    },
    
    formListen: function() {
        var val;
        
        
        $('.form input').click(function() {
            val = $(this).val();
            $(this).val('');
            
            $(this).focusout(function() {
                if($(this).val() == "") {
                    $(this).val(val);
                }
            })
        });
        
    },
    
    setStatus: function(status, err) {
        $(this.divId + ' #statusMessage').fadeOut(this.aniTime, function() {
            $(this).html(status);
            if(err) {
                $(this).addClass('err');
            } else {
                $(this).removeClass('err');
            }
            $(this).fadeIn(msgBoard.aniTime);
        });
    },
    
    listen: function() {
        $(window).resize( function() {
            $(msgBoard.divId).css({left: msgBoard.posTop().left});
        })
    }
}

msgBoard.init();


