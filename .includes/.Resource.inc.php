<?php

/*
File: .Resource.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Resource class. This class is extended for each different resource type.
	
*/

class Resource {

    /* Data */

    var $id;            //store only the database id of the resource.
    var $name;          //the name of the resource
    var $description;   //the description of the resource.
    var $urlmain;       //the main url of the resource.
    var $watch;         //whether or not to watch this resource
    var $watchurl;      //the url to keep track of for any new changes
    var $watchkeys;     //they keywords to search any changes to see if they're intersting.
    var $resource_type;    
    var $loaded;
    

    function main(){

        switch($_SESSION["action"]){

            case ACTION_ADD:
                Resource::select_type();
                break;
            case ACTION_SEARCH:
                if($_SESSION["confirm"] == "TRUE"){
                    Resource::search();
                    unset($_SESSION["confirm"]);
                } else {
                    Resource::view_search();
                }
                break;
            case ACTION_BROWSE:
                Resource::browse();
                break;
            default:
                include($GLOBALS["draw_includes_path"]."/Resource/.Resource.html");

        }

    }

    function is_valid(){

        if(isset($this->id) &&
           isset($this->name) &&
           isset($this->resource_type))
            return true;
        else
            return false;        

    }
    

    function browse(){

        //in essence tick all types then do a search
        $_POST["mask_".get_type_as_string(TYPE_JOURNAL)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_CONFERENCE)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_COLLECTION)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_BOOK)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_SCHOOL)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_COMPANY)] = "on";

        Resource::search();

    }

    function search(){

        //print the top bit
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceSearchTop.html");

        //begin searching
        $get_types = Resource::get_types();
        $filters = Resource::get_filters();
        $counter = count($get_types);

        //interpret the search needle (into phrases etc) 
        if(strlen($_POST["search_needles"]) > 0){
            $needles = get_needles($_POST["search_needles"]);
        } else {
            $needles = array();
        }

        $dark = TRUE;

        //get the users resource
        $resource_links = Link::get_links(ANY, $_SESSION["user_id"], LINK_RESOURCE_OF_USER);

        for($i = 0; $i < count($resource_links); $i++){

            DB::db_query("get", "SELECT * FROM resource WHERE id='".$resource_links[$i]["from_id"]."';");
            if(DB::db_check_result("get") > 0){

                //check that its in the gets_type list
                if($counter > 0 && ($data = DB::db_get_array("get")) != FALSE){
                    if($get_types[$data["resource_type"]]){
                        //continue checking
                        //change this data variable to an instance of the appropriate class
                        
                        switch($data["resource_type"]){

                        case get_type_as_string(TYPE_JOURNAL):                            
                            $ref = new Journal;
                            break;
                            
                        case get_type_as_string(TYPE_CONFERENCE):
                            $ref = new Conference;
                            break;
                            
                        case get_type_as_string(TYPE_COLLECTION):
                            $ref = new Collection;                            
                            break;

                        case get_type_as_string(TYPE_BOOK):
                            $ref = new Book;                            
                            break;

                        case get_type_as_string(TYPE_SCHOOL):
                            $ref = new School;                            
                            break;

                        case get_type_as_string(TYPE_COMPANY):
                            $ref = new Company;                            
                            break;
                            

                        default:
                            $ref = NULL;                            

                        }
                        
                        if($ref == NULL) continue;
                        
                        $ref->load_array($data);
                        if($ref->match_string($needles)){                            
                            if($dark){
                                $bgcolour = "search_item_dark";
                            } else {
                                $bgcolour = "search_item_light";
                            }
                            $dark = (!$dark);

                            $ref->search_overview($bgcolour);;
                        }

                    }
                } 

            } else {
                error("No resources in database\n");
                return FALSE;
            }           

        }

/*        DB::db_query("search", "SELECT * FROM resource;");
        if(DB::db_check_result("search") > 0){
            while(){
                

            
            }

*/

        //print the bottom bit
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceSearchBottom.html");

    }

    function get_filters(){

        $get_filters = array();

        return $get_filters;

    }
 
    function get_types(){

        $get_types = array();

        if($_POST["mask_".get_type_as_string(TYPE_JOURNAL)] == "on"){
            $get_types[get_type_as_string(TYPE_JOURNAL)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_CONFERENCE)] == "on"){
            $get_types[get_type_as_string(TYPE_CONFERENCE)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_COLLECTION)] == "on"){
            $get_types[get_type_as_string(TYPE_COLLECTION)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_BOOK)] == "on"){
            $get_types[get_type_as_string(TYPE_BOOK)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_SCHOOL)] == "on"){
            $get_types[get_type_as_string(TYPE_SCHOOL)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_COMPANY)] == "on"){
            $get_types[get_type_as_string(TYPE_COMPANY)] = true;
        }

        return $get_types;

    }



    function display_add($link, $from_id, $link_type){
        
        echo Link::display_add($link, $from_id, $link_type);        
 
    } 

    function get_category_links($from_id){

        return Link::get_links($this->id, ANY, LINK_RESOURCE_IN_CATEGORY);

    }

    function display_reference_links(){   

        echo "
                <tr><td class=\"article_bottom\" valign=\"top\" align=\"right\">
                    <p class=\"resource_main\">
                    References:
                    </td>
                    <td class=\"article_bottom\" colspan=\"3\" align=\"left\">
                    <p class=\"view_categories\">";

        
        DB::db_query("get", "SELECT * FROM reference WHERE resource_id='".$this->id."';"); 
        if(DB::db_check_result("get") > 0){

            //get all references where resource_id = $this->id
            while(($row = DB::db_get_array("get")) != FALSE){
                
                echo get_action_as_link($row["title"], $row["id"], 
                                        get_type_as_constant($row["reference_type"]), 
                                        ACTION_VIEW)."<br>";
                
            }
        }
            

        echo "      </p>";
        echo "  </td></tr>";
        
    }

    function display_category_links(){   

        echo "
                <tr><td class=\"article_bottom\" valign=\"top\" align=\"right\">
                    <p class=\"resource_main\">
                    Categories:
                    </td>
                    <td class=\"article_bottom\" colspan=\"3\" align=\"left\">
                    <p class=\"view_categories\">";

        $data = $this->get_category_links($this->id);
        if(count($data) > 0){
            echo Link::display_links($data, LINK_SELF, "<br/>");
        }
        echo "      </p>";
        //echo Link::display_add("Add", $this->id, LINK_REFERENCE_IN_CATEGORY);

        echo "  </td></tr>";
        
    }

    function view_search(){

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceSearch.html");

    }

    function saved(){

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceSaved.html");

    }

    function select_type(){

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceSelectType.html");

    }

    function is_resource_object_type($object_type){

        switch($object_type){

        case TYPE_RESOURCE:
        case TYPE_JOURNAL:
        case TYPE_CONFERENCE:
        case TYPE_COLLECTION:
        case TYPE_BOOK:
        case TYPE_SCHOOL:
        case TYPE_COMPANY:
            return TRUE;
            break;

        default:
            return FALSE;
        }
        
    }

    function remove($resource_id){
     
        //peform Resource wide remove stuffs
        //this primarily means remove links for this Resource

        DB::db_query("delete_resource_links", "DELETE FROM links WHERE from_id='".$resource_id."' AND (
                                                type = '".get_link_as_constant("RESOURCE_IN_CATEGORY")."' 
                                                 OR type='".get_link_as_constant("RESOURCE_OF_USER")."'
                                                 OR type='".get_link_as_constant("RESOURCE_OF_GROUP")."');");
        if(DB::db_check_result("delete_resource_links") > 0){
            //success
        }        
   
    }

    function save($res){

        //perform Reference wide save stuffs.
        //this primarily means add/update links

        //lookup user id.
        DB::db_query("user_id_lookup", "SELECT id FROM \"user\" WHERE username='".$_SESSION["username"]."';");
        if(DB::db_check_result("user_id_lookup") > 0){
            $user_id = DB::db_get_field("user_id_lookup", "id");                
            if($user_id == "") error("Bad User id.");                
        }

        //lookup reference id
        DB::db_query("resource_id_lookup", "SELECT id FROM resource WHERE name='".$res->name."' 
                                               AND resource_type='".$res->resource_type."';");
        if(DB::db_check_result("resource_id_lookup") > 0){
            $resource_id = DB::db_get_field("resource_id_lookup", "id");
            if($resource_id == "") error("Bad Resource id.");                
        }

        if(!isset($res->id)){
        
            //this means we are inserting so we need to: look up the
            //inserted resource's id, look up the users id, then
            //insert the RESOURCE_OF_USER link as appropriate.

            $link = new Link;
            $link->from_id = $resource_id;
            $link->to_id = $user_id;
            $link->type = LINK_RESOURCE_OF_USER;
            $link->save();
            
        }        
        
    }

    /* begin instance functions */

    function match_string($needles){

        if(count($needles) == 0){        
            return TRUE;
        }

        //see if this resource matches the search criteria
        //get the appropriate string from the subclass instance
        $search_haystack = $this->name." ".$this->description." ".$this->urlmain." ".
            $this->watchkeys." ".$this->get_search_haystack();
            
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
