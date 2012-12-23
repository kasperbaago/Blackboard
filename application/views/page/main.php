<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo (isset($title)) ? $title : ""; ?></title>
        
            <?php 
                //Loading in CSS files
                if(isset($css)) { foreach($css as $file) {
                    if(!strstr($file, "http://")) {
                        $file = base_url(CSS_FOLDER. $file);
                    }
                    echo "<link href='$file' rel='stylesheet' />\n";
                }}
            ?>
        
            <?php 
                //Loading in Javascript files
                if(isset($js)) { foreach($js as $file) {
                    if(!strstr($file, "http://")) {
                        $file = base_url(JS_FOLDER. $file);
                    }
                    echo "<script src='$file' type='text/javascript'></script>\n";
                }}
            ?>
    </head>
    <body>
        <img id="bgImg" src="<?php echo base_url(CSS_FOLDER. "/img/bg.jpg"); ?>"/>
        <div id="areaLock"></div>
        <div id="msgBoard">
            <div id="content"></div>
            <div id="statusBar">
                <p id="statusMessage"></p>
            </div>
            <nav>
                <ul id="menuOne">
                    <li id="addNewPost">
                        <div class="tooltip"><p>Add a new post</p></div>
                    </li>
                    <li id="addNewArea">
                        <div class="tooltip"><p>Add a new area</p></div>
                    </li>
                </ul>
                <ul id="menuTwo">
                    <li id="delArea">
                        <div class="tooltip"><p>Delete area and it's content</p></div>
                    </li>
                    <li id="delSpace">
                        <div class="tooltip"><p>Delete all areas</p></div>
                    </li>
                </ul>
            </nav>
        </div>
        <div id="area">
        </div>
    </body>
</html>
