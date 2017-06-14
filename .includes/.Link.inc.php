<?php

/*
File: .Link.inc.php
Author: David Andrews
Date Started: 10/10/2003
Synopsis:

    This file defines the Link class. This class provides
    methods for manipulating links between other objects.
	
*/

class Link {

    /* Data */

    var $id;
    var $from_id;
    var $to_id;
    var $type;
    var $description;
    var $loaded;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Link::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Link::add();
        } else if($action == ACTION_VIEW){
            Link::view();
        } else if($action == ACTION_SAVE){
            $alink = new Link;
            $alink->load_post(); 
            if($alink->save()){
                Link::saved();
            }
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Link::remove();
                unset($_SESSION["confirm"]);
            } else {
                Link::view_remove();
            }

        }
                
    }

    function view_remove(){
        $alink = new Link;
        $result = $alink->load_db($_SESSION["object_id"]);
        $this = $alink; 

        include($GLOBALS["draw_includes_path"]."/Link/.LinkDelete.html");

    }

    function remove(){
        $alink = new Link;
        $result = $alink->load_db($_SESSION["object_id"]);
        
        DB::db_query("delete_link", "DELETE FROM links WHERE id='".$alink->id."'");
        if(DB::db_check_result("delete_link") > 0){
            include($GLOBALS["draw_includes_path"]."/Link/.LinkDeleted.html");
        }
    }

    function view(){

        $alink = new Link;
        if(isset($_SESSION["object_id"])){
            $result = $alink->load_db($_SESSION["object_id"]);
        }

        include($GLOBALS["draw_includes_path"]."/Link/.LinkView.html");
        include($GLOBALS["draw_includes_path"]."/Link/.LinkViewActions.html");

    }

    function add(){
        Link::gather_link_info();
        $alink = new Link;
        $alink->from_id = $_SESSION["from_id"];
        $alink->type = $_SESSION["link_type"];

        //draw the Link specifics
        include($GLOBALS["draw_includes_path"]."/Link/.LinkAdd.html");

    }

    function edit(){

                //load the article in case we're editing
                $alink = new Link;
                if(isset($_SESSION["object_id"])){
                    $result = $alink->load_db($_SESSION["object_id"]);
                }

                //draw the Link specifics
                include($GLOBALS["draw_includes_path"]."/Link/.LinkEdit.html");

    }

    function saved(){

        include($GLOBALS["draw_includes_path"]."/Link/.LinkSaved.html");

    }


    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){

            DB::db_query("check_save", "SELECT * FROM links
                                        WHERE from_id='".$this->from_id."'
                                            AND to_id='".$this->to_id."'
                                            AND type='".$this->type."';");

            if(DB::db_num_rows("check_save") > 0){
                //error("That link already exists."); 
                return FALSE;
            }

            DB::db_query("save_link", "INSERT INTO links 
                        (from_id, to_id, 
                        type, description) VALUES
                        ('".$this->from_id."', '".$this->to_id."',
                        '".$this->type."', '".$this->description."')
                    ");

        } else {
            DB::db_query("save_link", "UPDATE links SET
                        from_id = '".$this->from_id."',
                        to_id = '".$this->to_id."',
                        type = '".$this->type."',
                        description = '".$this->description."'
                        WHERE id = '".$this->id."';
                    ");
        }

        return TRUE;

    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);

    }

    function load_db($link_id){

        DB::db_query("load_link", "SELECT * FROM links WHERE id='$link_id';");
        if(DB::db_num_rows("load_link") > 0){
            $data = DB::db_get_array("load_link");
            $this->load_array($data);
        } else {
            error("No link in database with that id\n");
            return FALSE;
        }

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->from_id = $array["from_id"];
        $this->to_id = $array["to_id"];
        $this->type = $array["type"];
        $this->description = $array["description"];
        $this->loaded = TRUE;

    }

    //pull the appropriate links from the database and return as an array
    function get_links($from_id, $to_id, $link_type){

        if($from_id == ANY){
            $from_clause = "1=1 ";
        } else {
            $from_clause = "from_id='$from_id'";
        }
        if($to_id == ANY){
            $to_clause = "1=1 ";
        } else {
            $to_clause = "to_id='$to_id'";
        }

        DB::db_query("get", "SELECT * FROM links WHERE $from_clause
                            AND $to_clause
                            AND type='$link_type';");

        if(DB::db_check_result("get") > 0){
            $result = array();
            while(($data = DB::db_get_array("get")) != FALSE){
                $result[] = $data;
            }
            return $result;
        } else
            return FALSE;

    }

    //get be used to print the add button for a given link
    function display_add($link, $from_id, $link_type){

        $additional = array("link_type" => $link_type,
                            "from_id" => $from_id);

        return get_action_as_link($link, ANY, TYPE_LINK, ACTION_ADD, "FALSE", $additional);

    }


    function get_to_type($link_type){

        switch($link_type){

            case LINK_REFERENCE_OF_USER:
            case LINK_RESOURCE_OF_USER:
            case LINK_CATEGORY_OF_USER:
                $to_type = TYPE_USER;
                break;
            case LINK_REFERENCE_OF_GROUP:
            case LINK_RESOURCE_OF_GROUP:
            case LINK_CATEGORY_OF_GROUP:
                $to_type = TYPE_GROUP;
                break;
            case LINK_RESOURCE_IN_CATEGORY:
            case LINK_CATEGORY_SUB_OF_CATEGORY:
            case LINK_REFERENCE_IN_CATEGORY:
                $to_type = TYPE_CATEGORY;
                break;
            case LINK_REFERENCE_CITES_REFERENCE:
            case LINK_REFERENCE_CITES_REFERENCE:
            case LINK_DOCUMENT_OF_REFERENCE:
                $to_type = TYPE_REFERENCE;
                break;
            case LINK_REFERENCE_FROM_RESOURCE:
                $to_type = TYPE_RESOURCE;
                break;

        }

        return $to_type;


    }


    function get_from_type($link_type){

        switch($link_type){

            case LINK_RESOURCE_IN_CATEGORY:
            case LINK_RESOURCE_OF_USER:
            case LINK_RESOURCE_OF_GROUP:
                $from_type = TYPE_RESOURCE;
                break;                
            case LINK_CATEGORY_SUB_OF_CATEGORY:
            case LINK_CATEGORY_OF_USER:
            case LINK_CATEGORY_OF_GROUP:
                $from_type = TYPE_CATEGORY;
                break;
            case LINK_REFERENCE_OF_USER:
            case LINK_REFERENCE_FROM_RESOURCE:
            case LINK_REFERENCE_IN_CATEGORY:
            case LINK_REFERENCE_OF_GROUP:
            case LINK_REFERENCE_CITES_REFERENCE:
            case LINK_REFERENCE_CITES_REFERENCE:
                $from_type = TYPE_REFERENCE;
                break;
            case LINK_DOCUMENT_OF_REFERENCE:
                $from_type = TYPE_DOCUMENT;

        }

        return $from_type;

    }

    function get_user_link_type($type){
            
        switch($type){

        case TYPE_REFERENCE:
            return LINK_REFERENCE_OF_USER;
            break;

        case TYPE_RESOURCE:
            return LINK_RESOURCE_OF_USER;
            break;

        case TYPE_CATEGORY:
            return LINK_CATEGORY_OF_USER;
            break;

        default:
            return TYPE_NULL;
            break;

        }

        
    }

    function get_name_from_type($type){

        switch($type){
            case TYPE_USER:
                return "username";
                break;

            case TYPE_GROUP:
            case TYPE_RESOURCE:
            case TYPE_CATEGORY:
            case TYPE_DOCUMENT:
                return "name";
                break;

            case TYPE_REFERENCE:
                return "title";
                break;

        }

    }

    function get_table_from_type($type){

        switch($type){
            case TYPE_USER:
                return "user";
                break;

            case TYPE_GROUP:
                return "group";
                break;

            case TYPE_CATEGORY:
                return "category";
                break;

            case TYPE_REFERENCE:
                return "reference";
                break;

            case TYPE_RESOURCE:
                return "resource";
                break;

            case TYPE_DOCUMENT:
                return "document";
                break;

        }

    }

    function get_from_links($link_type){
        return Link::get_links_to_types($link_type, LINK_FROM);
    }

    function get_to_links($link_type){
        return Link::get_links_to_types($link_type, LINK_TO);
    }

    function get_links_to_types($link_type, $link_direction){

        switch($link_direction){

            case LINK_TO:
                $type = Link::get_to_type($link_type);
                break;
            case LINK_FROM:
                $type = Link::get_from_type($link_type);
                break;

            default: 
                return TRUE;

        }

        $table_name = Link::get_table_from_type($type);
        $array = array();

        //from a `type' get the appropriate user link_type E.g LINK_REFERENCE_FROM_USER
        $user_link_type = Link::get_user_link_type($type);

        if($user_link_type == TYPE_NULL){

            DB::db_query("get", "SELECT * FROM ".$table_name.";");
            if(DB::db_check_result("get") > 0){
                while(($row = DB::db_get_array("get")) != FALSE){
                    $array[] = $row;        
                }
            }
            
        } else {                

            //get links
            $user_objects = Link::get_links(ANY, $_SESSION["user_id"], $user_link_type);
            
            //for each of these select the appropriate entry from the appropriate table
            for($i = 0; $i < count($user_objects); $i++){
                
                DB::db_query("get", "SELECT * FROM ".$table_name." WHERE id=".$user_objects[$i]["from_id"].";");
                if(DB::db_check_result("get") > 0){
                    while(($row = DB::db_get_array("get")) != FALSE){
                        $array[] = $row;        
                    }
                }            
            
            }
            
        }
        

        return $array;
    }

    function display_from($from_id, $link_type){

        $type = Link::get_from_type($link_type);
        $table = Link::get_table_from_type($type);
        $var = Link::get_name_from_type($type);        

        DB::db_query("get", "SELECT * FROM ".$table." WHERE id='".$from_id."';");
        if(DB::db_check_result("get") > 0){
            if(($row = DB::db_get_array("get")) != FALSE){
                //put in some smarts to allow for differen Reference types
                if($type == TYPE_REFERENCE){
                    $type = get_type_as_constant($row["reference_type"]);
                }
                if($type == TYPE_RESOURCE){
                    $type = get_type_as_constant($row["resource_type"]);
                }

                $str = get_action_as_link($row[$var], $from_id, $type, ACTION_VIEW); // to or from

            }
        }

        return $str;
    
    }

    function display_to($to_id, $link_type){

        $type = Link::get_to_type($link_type);
        $table = Link::get_table_from_type($type);
        $var = Link::get_name_from_type($type);

        DB::db_query("get", "SELECT * FROM ".$table." WHERE id='".$to_id."';");
        if(DB::db_check_result("get") > 0){
            if(($row = DB::db_get_array("get")) != FALSE){
                //put in some smarts to allow for differen Reference types
                if($type == TYPE_REFERENCE){
                    $type = get_type_as_constant($row["reference_type"]);
                }

                $str = get_action_as_link($row[$var], $to_id, $type, ACTION_VIEW); // to or from

            }
        }

        return $str;
 
    }

    //used to delete all the links to something when that something disappears
    function delete_links($array){

        foreach($array as $data){

            DB::db_query("delete", "DELETE FROM links WHERE id='".$data['id']."';");
            if(DB::db_affected_rows("delete") < 0){
                error("No such Link.");
            }

        }        
        
    }


    //get be used to get the `to' identifier after having get_links'd
    function display_links($array, $what, $seperator = ",", $link=""){

        if(count($array) > 0){

            //echo "type: ".$array[0]["type"]."<br>";
            //echo "from: ".$array[0]["from_id"]."<br>";
            //echo "to: ".$array[0]["to_id"]."<br>";
           
            $to_type = Link::get_to_type($array[0]["type"]);
            $from_type = Link::get_from_type($array[0]["type"]);
            $to_table = Link::get_table_from_type($to_type);
            $to_var = Link::get_name_from_type($to_type);
            $from_table = Link::get_table_from_type($from_type);
            $from_var = Link::get_name_from_type($from_type);

            $count = count($array);
            $i = 1;
            $str = "";

            foreach($array as $data){

                if($link == "")
                    $link = $what;

                switch($link){

                    case LINK_SELF:

                    case LINK_TO:
                        $table = $to_table;
                        $type = $to_type;
                        $id = $data["to_id"];
                        $var = $to_var;
                        break;

                    case LINK_FROM:
                        $table = $from_table;
                        $type = $from_type;
                        $id = $data["from_id"];
                        $var = $from_var;
                        break;

                }

                DB::db_query("get", "SELECT * FROM ".$table." WHERE id='".$id."';");
                if(DB::db_check_result("get") > 0){
                    $str .= "&nbsp;"; 

                    if(($row = DB::db_get_array("get")) != FALSE){
                        //what do we want
                        if($what == LINK_TO || $what == LINK_FROM){

                            //put in some smarts to allow for differen Reference types
                            if($type == TYPE_REFERENCE){
                                $type = get_type_as_constant($row["reference_type"]);
                            }
                            if($type == TYPE_RESOURCE){
                                $type = get_type_as_constant($row["resource_type"]);
                            }

                            $str .= get_action_as_link($row[$var], $id, $type, ACTION_VIEW); // to or from
                        } else {
                            $str .= get_action_as_link($row[$var], $data["id"], TYPE_LINK, ACTION_VIEW); // self
                        }

                        if(($count-$i) >= 1){
                            $str .= $seperator; 
                        }
                        $i++;
                    }
                }
            }
        }
        return $str;

    }

    //gather functions

    function gather_link_info(){

        if(isset($_POST["from_id"])) $_SESSION["from_id"] = $_POST["from_id"];
        if(isset($_GET["from_id"])) $_SESSION["from_id"] = $_GET["from_id"];
        if(isset($_POST["to_id"])) $_SESSION["to_id"] = $_POST["to_id"];
        if(isset($_GET["to_id"])) $_SESSION["to_id"] = $_GET["to_id"];
        if(isset($_POST["link_type"])) $_SESSION["link_type"] = $_POST["link_type"];
        if(isset($_GET["link_type"])) $_SESSION["link_type"] = $_GET["link_type"];

    }
}
