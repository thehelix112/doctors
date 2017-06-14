<?php

/*
File: .User.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the User class. This class is responsible for users.
	
*/

class User {

    /* Data */

    var $id;              //store only the database id of the user.
    var $username;        //the name the user uses to log in.
    var $fullname;        //the name of the user
    var $password;        //the encrypted password.
    var $administrator;   //`true' or `false'.

    function main(){

        switch($_SESSION["action"]){

            case ACTION_DELETE:
                if($_SESSION["confirm"] == "TRUE"){
                    User::remove();
                    unset($_SESSION["confirm"]);
                } else {
                    User::view_remove();
                }
                break;
            case ACTION_SAVE:
                $auser = new User;
                $auser->load_post();
                $auser->save();
                User::saved();
                User::edit();
                break;
            case ACTION_VIEW:
                User::view();
                break;
            case ACTION_EDIT:
                User::edit();
                break;
            case ACTION_ADD:
                unset($_SESSION["object_id"]);
                User::edit();
                break;
            case ACTION_SEARCH:
            case ACTION_BROWSE:
                if($_SESSION["confirm"] == "TRUE"){
                    User::search();
                    unset($_SESSION["confirm"]);
                } else {
                    User::view_search();
                }
                break;
            case ACTION_EDIT_PASSWORD:
                User::edit_password();
                break;
                
            case ACTION_SAVE_PASSWORD:
                $auser = new User;
                $auser->load_post();
                $auser->save_password();
                User::saved();
                User::edit();
                break;
                
            default:
                include($GLOBALS["draw_includes_path"]."/User/.User.html");

        }

    }

    function edit_password(){

        //load the user in case we're editing
        $auser = new User;
        if(isset($_SESSION["object_id"])){
            $result = $auser->load_db($_SESSION["object_id"]);
        }

        //draw the User specifics
        include($GLOBALS["draw_includes_path"]."/User/.UserEditPassword.html");        

    }    

    function save_password(){
    
        if(!isset($this->id)){
            error("Cannot set password on unknown user.");            
        } else {
            DB::db_query("save_user", "UPDATE \"user\" SET
                        password = '".$this->password."'
                        WHERE id = '".$this->id."';
                    ");

        } 
        
   
    }

    function edit(){

        //load the user in case we're editing
        $auser = new User;
        if(isset($_SESSION["object_id"])){
            $result = $auser->load_db($_SESSION["object_id"]);
        }

        //draw the User specifics
        include($GLOBALS["draw_includes_path"]."/User/.UserEdit.html");

    }

    function search_overview($bgcolour){
        include($GLOBALS["draw_includes_path"]."/User/.UserOverview.html");

    }

    function view(){

        $auser = new User;
        if(isset($_SESSION["object_id"])){
            $result = $auser->load_db($_SESSION["object_id"]);
        }

        //user view should list all the references, and resources in it.
        
        include($GLOBALS["draw_includes_path"]."/User/.UserView.html");

    }

    function view_remove(){
        $auser = new User;
        $result = $auser->load_db($_SESSION["object_id"]);
        $this = $auser;

        include($GLOBALS["draw_includes_path"]."/User/.UserDelete.html");

    }

    function remove(){

        //perform User wide remove stuffs
        //this primarily means remove links for this Resource

        //delete all user's references
        //delete all user's reference's documents
        //delete all user's resources
        //delete user from all groups

//         DB::db_query("delete_user_links", "DELETE FROM links WHERE from_id='".$_SESSION["object_id"]."' AND (type = '7' 
//                                                  OR type='11');");
//         if(DB::db_check_result("delete_user_links") > 0){
//             //success
//         }

        DB::db_query("delete_user", "DELETE FROM \"user\" WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_user") > 0){
            include($GLOBALS["draw_includes_path"]."/User/.UserDeleted.html");
        }
    }

    function search(){

        //print the top bit
        include($GLOBALS["draw_includes_path"]."/User/.UserSearchTop.html");

        //begin searching

        //interpret the search needle (into phrases etc) 
        if(strlen($_POST["search_needles"]) > 0){
            $needles = get_needles($_POST["search_needles"]);
        } else {
            $needles = array();
        }

        $dark = TRUE;

        DB::db_query("search", "SELECT * FROM \"user\";");
        if(DB::db_check_result("search") > 0){
            while(($data = DB::db_get_array("search")) != FALSE){

                    //continue checking
                    $res = new User;
                    $res->load_array($data);
                    if($res->match_string($needles)){
                        if($dark){
                            $bgcolour = "search_item_dark";
                        } else {
                            $bgcolour = "search_item_light";
                        }
                        $dark = (!$dark);

                        $res->search_overview($bgcolour);;
                    }
            
            }

        } else {
            error("No users in database\n");
            return FALSE;
        }

        //print the bottom bit
        include($GLOBALS["draw_includes_path"]."/User/.UserSearchBottom.html");

    }

    function view_search(){

        include($GLOBALS["draw_includes_path"]."/User/.UserSearch.html");

    }

    function saved(){

        include($GLOBALS["draw_includes_path"]."/User/.UserSaved.html");

    }

    function select_type(){

        include($GLOBALS["draw_includes_path"]."/User/.UserSelectType.html");

    }

    /* begin instance functions */

    function save(){

        //write everything from the object to the database
        if(!isset($this->id)){
            DB::db_query("save_user", "INSERT INTO \"user\" 
                        (username, fullname, administrator) VALUES 
                        ('".$this->username."', '".$this->fullname."',
                         '".$this->administrator."');
                    ");

        } else {
            DB::db_query("save_user", "UPDATE \"user\" SET
                        username = '".$this->username."',
                        fullname = '".$this->fullname."',
                        administrator = '".$this->administrator."'
                        WHERE id = '".$this->id."';
                    ");

        }

    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);        
        $this->password = crypt($data["password1"]);

    }

    function load_db($user_id){

        DB::db_query("load_user", "SELECT * FROM \"user\" WHERE id='$user_id';");
        if(DB::db_check_result("load_user") > 0){
            $data = DB::db_get_array("load_user");
            $this->load_array($data);
        } else {
            error("No user in database with that id\n");
            return FALSE;
        }

        $this->password = $data["password"];

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->username = strtolower($array["username"]);
        $this->fullname = $array["fullname"];        
        $this->administrator = $array["administrator"];        

    }


    function match_states($filters){

        //test and see if the ticked bits are as they are supposed to be.

        //if the states are ticked that means they are that filter is required.

        //notated means the description is not empty
        //categorised means from links where type="REFERENCE_IN_USER" and from_id= the user_id

        /*if($filters[STATE_NOTATED]){
            //has to be notated
            if(trim($this->description) == ""){
                return FALSE;
            }
        }*/

        return TRUE;

    }

    function match_string($needles){

        if(count($needles) == 0){
            return TRUE;
        }

        //see if this user matches the search criteria
        //get the appropriate string from the subclass instance
        $search_haystack = $this->name." ".$this->description;
            
        $found = FALSE;

        //go through the needles and see if it matches
        foreach ($needles as $keyword) {
            $pos = strpos(strtolower($search_haystack), strtolower($keyword));
            if(is_integer($pos)){
                $found = TRUE;  
                break;
            }
        }

        return $found;

    }

}

?>
