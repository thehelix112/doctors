<?php
/*
File: .Menu.inc.php
Author: David Andrews
Date Started: 25/09/2003
Synopsis:

    This file defines the Menu class. This class is responsible for drawing
    the menu part of the screen.

*/

class Menu {

    function getLevel($newlevel=NULL){
        static $level = 1;
        if($newlevel != NULL){
            $level = $newlevel;
        }
        return $level;
    }

    function setLevel($newLevel=1){
        return Menu::getLevel($newLevel);
    }

    function incLevel(){

        $curlevel = Menu::getLevel();
        Menu::setLevel($curlevel+1);

    }

    function decLevel(){

        $curlevel = Menu::getLevel();
        Menu::setLevel($curlevel-1);

    }

    function draw(){

        Menu::draw_top();
        Menu::draw_divider();
        Menu::draw_home();
        Menu::draw_divider();
        Menu::draw_references();
        Menu::draw_divider();
        Menu::draw_resources();
        Menu::draw_divider();
        Menu::draw_categories();
        Menu::draw_administration();
        Menu::draw_divider();
        Menu::draw_data();
        Menu::draw_divider();
        Menu::draw_logout();
        Menu::draw_divider();
        Menu::draw_bottom();

    }

    function draw_home() {
        Menu::draw_cell("Home", 
                        ANY,
                        TYPE_NULL,
                        ACTION_MAIN);
    }

    function draw_logout() {
        Menu::draw_cell("Logout",                            
                        ANY,
                        TYPE_NULL,
                        ACTION_LOGOUT);
    }

    function draw_references() {
        Menu::draw_cell("References", 
                        ANY,
                        TYPE_REFERENCE);
        if(Reference::is_reference_object_type($_SESSION["object_type"])){
            Menu::draw_begin_sub();
            Menu::draw_sub_divider();
            Menu::draw_cell("Add",
                            ANY,
                            TYPE_REFERENCE, 
                            ACTION_ADD);
            Menu::draw_sub_divider();
            Menu::draw_cell("Search", 
                            ANY,
                            TYPE_REFERENCE, 
                            ACTION_SEARCH);
            Menu::draw_sub_divider();
            Menu::draw_cell("Browse", 
                            ANY,
                            TYPE_REFERENCE, 
                            ACTION_BROWSE);
            Menu::draw_end_sub(); 
        }
    }

    function draw_resources() {
        Menu::draw_cell("Resources", 
                        ANY,
                        TYPE_RESOURCE);
         if(Resource::is_resource_object_type($_SESSION["object_type"])){
            Menu::draw_begin_sub();
            Menu::draw_sub_divider();
            Menu::draw_cell("Add", 
                            ANY,
                            TYPE_RESOURCE, 
                            ACTION_ADD);
            Menu::draw_sub_divider();
            Menu::draw_cell("Search", 
                            ANY,
                            TYPE_RESOURCE, 
                            ACTION_SEARCH);
            Menu::draw_sub_divider();
            Menu::draw_cell("Browse", 
                            ANY,
                            TYPE_RESOURCE, 
                            ACTION_BROWSE);
            Menu::draw_end_sub(); 
        }
    }

    function draw_categories() {
        Menu::draw_cell("Categories",
                        ANY,
                        TYPE_CATEGORY);
        if($_SESSION["object_type"] == TYPE_CATEGORY){
            Menu::draw_begin_sub();
            Menu::draw_sub_divider();
            Menu::draw_cell("Add", 
                            ANY,
                            TYPE_CATEGORY, 
                            ACTION_ADD);
            Menu::draw_sub_divider();
            Menu::draw_cell("Search", 
                            ANY,
                            TYPE_CATEGORY, 
                            ACTION_SEARCH);
            Menu::draw_sub_divider();
            Menu::draw_cell("Browse", 
                            ANY,
                            TYPE_CATEGORY, 
                            ACTION_BROWSE,
                            "TRUE");
            Menu::draw_end_sub(); 
        }
    }

    function draw_administration(){

        if($_SESSION["administrator"] == TRUE){
            Menu::draw_divider();
            Menu::draw_cell("Administration", 
                            ANY,
                            TYPE_ADMINISTRATION);
            switch($_SESSION["object_type"]){
                case TYPE_ADMINISTRATION:
                case TYPE_USER:                
                case TYPE_GROUP:                    
                    Menu::draw_begin_sub();
                    Menu::draw_sub_divider();
                    Menu::draw_cell("Users", 
                                    ANY,
                                    TYPE_USER);
                    if($_SESSION["object_type"] == TYPE_USER){
                        Menu::draw_begin_sub();
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Add",
                                        ANY,
                                        TYPE_USER,
                                        ACTION_ADD);
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Search", 
                                        ANY,
                                        TYPE_USER, 
                                        ACTION_SEARCH);
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Browse",
                                        ANY,
                                        TYPE_USER,
                                        ACTION_BROWSE,
                                        "TRUE");
                        Menu::draw_end_sub();
                    }
                    Menu::draw_sub_divider();
                    Menu::draw_cell("Groups", 
                                    ANY,
                                    TYPE_GROUP);
                    if($_SESSION["object_type"] == TYPE_GROUP){
                        Menu::draw_begin_sub();
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Add",
                                        ANY,
                                        TYPE_GROUP,
                                        ACTION_ADD);
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Search", 
                                        ANY,
                                        TYPE_GROUP, 
                                        ACTION_SEARCH);
                        Menu::draw_sub_divider();
                        Menu::draw_cell("Browse",
                                        ANY,
                                        TYPE_GROUP,
                                        ACTION_BROWSE,
                                        "TRUE");
                        Menu::draw_end_sub();
                    }
                    Menu::draw_end_sub();
                    break;
                    
                 default:
            }
        } else {
            
            //draw the ability For people to set their passwords
            Menu::draw_divider();
            Menu::draw_cell("Settings", 
                            ANY,
                            TYPE_ADMINISTRATION);
            switch($_SESSION["object_type"]){

            case TYPE_ADMINISTRATION:
            case TYPE_USER:                
                Menu::draw_begin_sub();
                Menu::draw_sub_divider();
                Menu::draw_cell("Password", 
                                $_SESSION["user_id"], 
                                TYPE_USER, 
                                ACTION_EDIT_PASSWORD);
                Menu::draw_end_sub();
                break;
            }
            
        }
        
    }

    function draw_data() {
        Menu::draw_cell("Data", 
                        ANY,
                        TYPE_DATA);
        if($_SESSION["object_type"] == TYPE_DATA){
            Menu::draw_sub_divider();
            Menu::draw_begin_sub();
            Menu::draw_cell("Import", 
                            ANY,
                            TYPE_DATA, 
                            ACTION_IMPORT);
            Menu::draw_sub_divider();
            Menu::draw_cell("Export", 
                            ANY,
                            TYPE_DATA, 
                            ACTION_EXPORT);
            Menu::draw_end_sub(); 
        }
    }

    function draw_sub_divider(){

        echo "
<tr><td class=\"menu_divider\" width=\"1\" height=\"1\">
      <img src=\"images/clear.gif\">
    </td>
    <td class=\"menu_sub_divider\" width=\"1\">     
      <img src=\"images/clear.gif\" width=\"1\" height=\"1\">
    </td>
    <td class=\"menu_divider\" width=\"1\" height=\"1\">
      <img src=\"images/clear.gif\">
    </td></tr>
"; 
            
    }


    function draw_divider(){

        echo "
<tr><td colspan=\"15\" class=\"menu_divider\" width=\"1\">     
     <img src=\"images/clear.gif\" width=\"1\" height=\"1\">
</td></tr>
"; 
            
    }

    function draw_begin_sub(){

        Menu::incLevel();

        //Menu::draw_divider();
        /*echo "
        <tr><td class=\"menu_divider\" width=\"1\" height=\"25\">
                    <img src=\"images/clear.gif\">
            </td>
            <td class=\"menu_item\">
            <table class=\"menu_subitem\" cellpadding=\"0\" cellspacing=\"0\">";*/

    }

    function draw_end_sub(){

        /*echo "
            </table>
        </td></tr>";*/
        //Menu::draw_divider();
        Menu::decLevel();
 
    }

    function draw_cell($name, $object_id, $object_type, $action=ACTION_MAIN, $confirm="FALSE", $class="menu_item", $target=""){

        echo "
                <tr>        
                <td class=\"menu_divider\" width=\"1\" height=\"25\">
                    <img src=\"images/clear.gif\">
                </td>";
        Menu::draw_link($name, $object_id, $object_type, $action, $confirm, $class, $target);
        echo "
                <td class=\"menu_divider\" width=\"1\">
                    <img src=\"images/clear.gif\">
                </td>
                </tr>";
       
    }

    function draw_link($name, $object_id, $object_type, $action, $confirm, $class, $target=""){

        echo "
        <td class=\"".$class."\" onMouseOver=\"javascript:this.style.background='#FFCEE9';\" onMouseOut=\"javascript:this.style.background='#F9EFFF';\">&nbsp;&nbsp;";
        
        //write a nbsp for each level
        for($i = 0; $i < (Menu::getLevel()-1); $i++){
            
            echo "&nbsp;&nbsp;";            
                
        }

        echo get_action_as_link($name, $object_id, $object_type, $action, $confirm, NULL, $target)."&nbsp;&nbsp;
        </td>";
           // <a href=\"index.php?action=$action&object_type=$object_type\">$name</a>
    }

    function draw_top(){

        include($GLOBALS["draw_includes_path"]."/.MenuTop.html");

    }

    function draw_bottom(){

        include($GLOBALS["draw_includes_path"]."/.MenuBottom.html");

    }

}

?>
