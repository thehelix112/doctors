<?php

/*
File: .Category.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the Category class. This class is responsible for categorys.
	
*/

class Category {

    /* Data */

    var $id;            //store only the database id of the category.
    var $name;          //the name of the category
    var $description;   //the description of the category.

    function main(){

        switch($_SESSION["action"]){

            case ACTION_DELETE:
                if($_SESSION["confirm"] == "TRUE"){
                    Category::remove();
                    unset($_SESSION["confirm"]);
                } else {
                    Category::view_remove();
                }
                break;
            case ACTION_SAVE:
                $acat = new Category;
                $acat->load_post();
                $acat->save();
                Category::saved();
                Category::view();
                break;
            case ACTION_VIEW:
                Category::view();
                break;
            case ACTION_EDIT:
                Category::edit();
                break;
            case ACTION_ADD:
                unset($_SESSION["object_id"]);
                Category::edit();
                break;
            case ACTION_SEARCH:
            case ACTION_BROWSE:
                if($_SESSION["confirm"] == "TRUE"){
                    Category::search();
                    unset($_SESSION["confirm"]);
                } else {
                    Category::view_search();
                }
                break;
            default:
                include($GLOBALS["draw_includes_path"]."/Category/.Category.html");

        }

    }

    function edit(){

        //load the category in case we're editing
        $acat = new Category;
        if(isset($_SESSION["object_id"])){
            $result = $acat->load_db($_SESSION["object_id"]);
        }

        //draw the Category specifics
        include($GLOBALS["draw_includes_path"]."/Category/.CategoryEdit.html");

    }

    function search_overview($dark, $level){

        if($dark%2 == 1){
            $bgcolour = "search_item_dark";
        } else {
            $bgcolour = "search_item_light";
        }

        include($GLOBALS["draw_includes_path"]."/Category/.CategoryOverview.html");

        $dark++;

        //print all our children
        $children = $this->get_children();
        
        if($children != FALSE){

            for($j = 0; $j < count($children); $j++){
                
                $achild = new Category;
                $achild->load_db($children[$j]["from_id"]);
                if($achild->match_string($needles)){
                    $dark = $achild->search_overview($dark, $level+1);                    
                }            
                
            }       
            
        }        

        return $dark;        

    }

    function view(){

        $acat = new Category;
        if(isset($_SESSION["object_id"])){
            $result = $acat->load_db($_SESSION["object_id"]);
        }

        //category view should list all the references, and resources in it.
        
        include($GLOBALS["draw_includes_path"]."/Category/.CategoryView.html");

    }

    function view_remove(){
        $acat = new Category;
        $result = $acat->load_db($_SESSION["object_id"]);
        $this = $acat;

        include($GLOBALS["draw_includes_path"]."/Category/.CategoryDelete.html");

    }

    function remove(){

        //perform Category wide remove stuffs
        //this primarily means remove links for this Resource
        DB::db_query("delete_category_links", "DELETE FROM links WHERE from_id='".$_SESSION["object_id"]."' AND (type = '7' 
                                                 OR type='11');");
        if(DB::db_check_result("delete_category_links") > 0){
            //success
        }
        
        //delete all links which state that this category is a parent
        $delete = Link::get_links(ANY, $_SESSION["object_id"], LINK_CATEGORY_SUB_OF_CATEGORY);
        foreach($delete as $entry){
         
            $link = new Link;
            $link->load_db($entry["id"]);
            $link->remove();
                
        }

        DB::db_query("delete_category", "DELETE FROM category WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_category") > 0){
            include($GLOBALS["draw_includes_path"]."/Category/.CategoryDeleted.html");
        }
    }

    function search(){

        //print the top bit
        include($GLOBALS["draw_includes_path"]."/Category/.CategorySearchTop.html");

        //begin searching

        //interpret the search needle (into phrases etc) 
        if(strlen($_POST["search_needles"]) > 0){
            $needles = get_needles($_POST["search_needles"]);
        } else {
            $needles = array();
        }

        $dark = 1;
        $level = 0;

        //get the users categories

        $category_links = Link::get_links(ANY, $_SESSION["user_id"], LINK_CATEGORY_OF_USER);        

        for($i = 0; $i < count($category_links); $i++){

            $acat = new Category;
            $success = $acat->load_db($category_links[$i]["from_id"]);
        
            //if the link is broken delete it
            if(!$success){                
                $alink = new Link;
                $alink->load_db($category_links[$i]["id"]);
                $alink->remove();
                continue;
            }

            if($acat->match_string($needles)){    

                //skip all those which aren't root
                if(!$acat->get_parent()){                    

                    //root so print it and all others under it
                    $dark = $acat->search_overview($dark, $level);
                    
                }
                
            }            

        }

        //print the bottom bit
        include($GLOBALS["draw_includes_path"]."/Category/.CategorySearchBottom.html");

    }

    function view_search(){

        include($GLOBALS["draw_includes_path"]."/Category/.CategorySearch.html");

    }

    function saved(){

        include($GLOBALS["draw_includes_path"]."/Category/.CategorySaved.html");

    }

    function select_type(){

        include($GLOBALS["draw_includes_path"]."/Category/.CategorySelectType.html");

    }

    /* begin instance functions */

    function is_parent($category_id){

        if($this->get_parent() == $category_id)
            return TRUE;
        else
            return FALSE;        
   
    }

    function is_child($category_id){

        $children = $this->get_children();
        if(is_array($children)){
          
            return in_array($category_id, $children);            
            
        }
   
    }    


    function get_parent(){
        
        //return the id of the parent category
        $parent = Link::get_links($this->id, ANY, LINK_CATEGORY_SUB_OF_CATEGORY);

        if(count($parent) == 1){
            return $parent[0]["to_id"];
        } else if(count($parent) == 0){
            return FALSE;
        } else {
            error("Multiple Category parents.");
        }

    }

    function get_children(){

        //return an array of the ids of the children category
        $children = Link::get_links(ANY, $this->id, LINK_CATEGORY_SUB_OF_CATEGORY);
        if(count($children) >= 1){
            return $children;
        } else {
            return FALSE;
            
        }

    }
    

    function save(){

        //write everything from the object to the database
        if(!isset($this->id)){
            DB::db_query("save_category", "INSERT INTO category 
                        (name, description) VALUES 
                        ('".$this->name."', '".$this->description."');
                    ");

            //add a link
            $from_id = DB::db_last_id("save_category", "category");

        } else {
            DB::db_query("save_category", "UPDATE category SET
                        name = '".$this->name."',
                        description = '".$this->description."'
                        WHERE id = '".$this->id."';
                    ");

            //update the link
            $from_id = $this->id;
        }

        //save the link to the parent
        $this->save_parent($from_id);

        //save the link to user
        $this->save_user($this);        

    }

    Function save_user($cat){
            
        //perform user link save stuffs.

        //lookup user id.
        DB::db_query("user_id_lookup", "SELECT id FROM \"user\" WHERE username='".$_SESSION["username"]."';");
        if(DB::db_check_result("user_id_lookup") > 0){
            $user_id = DB::db_get_field("user_id_lookup", "id");                
            if($user_id == "") error("Bad User id.");                
        }

        //lookup category id
        DB::db_query("category_id_lookup", "SELECT id FROM category WHERE name='".$cat->name."';");
        if(DB::db_check_result("category_id_lookup") > 0){
            $category_id = DB::db_get_field("category_id_lookup", "id");
            if($category_id == "") error("Bad Category id.");                
        }

        if(!isset($cat->id)){
        
            //this means we are inserting so we need to: look up the
            //inserted resource's id, look up the users id, then
            //insert the CATEGORY_OF_USER link as appropriate.

            $link = new Link;
            $link->from_id = $category_id;
            $link->to_id = $user_id;
            $link->type = LINK_CATEGORY_OF_USER;
            $link->save();
            
        }            
    }
    

    function save_parent($from_id){

        $alink = new Link;

        DB::db_query("check_save", "SELECT * FROM links
                                        WHERE from_id='".$from_id."'
                                        AND type='".$_POST["link_type"]."';");
        
        if(DB::db_num_rows("check_save") > 0){

            $id = DB::db_get_field("check_save", "id");
            //load it in then update it.
            $alink->load_db($id);
            $alink->to_id = $_POST["to_id"];
            $alink->save();            

        } else {

            $_POST["from_id"] = $from_id;
            
            //copy link_type to type
            $temp_type = $_POST["type"];
            $_POST["type"] = $_POST["link_type"];
            
            //unset id and description from category so it doesn't
            //interfere with loading the link
            {
                $temp_id = $_POST["id"];
                $temp_desc = $_POST["description"];
                unset($_POST["id"]);
                unset($_POST["description"]);
            }        
            
            $alink->load_post();
            
            //un do the temporary changes
            {
                $_POST["type"] = $temp_type;
                $_POST["id"] = $temp_id;
                $_POST["description"] = $temp_desc;
            }
            
            //add the link
            $alink->save();

            
        }

        if($_POST["to_id"] == "-1"){

            //delete the link
            $rows = Link::get_links($alink->from_id, ANY, $alink->type);
            if($rows != FALSE && count($rows) == 1){

                    //set $_SESSION["object_id"] temporarily
                    $temp_object_id = $_SESSION["object_id"];
                    $_SESSION["object_id"] = $rows[0]["id"];
                    $alink->remove();
                    $_SESSION["object_id"] = $temp_object_id;
            }
        }
        

    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);

    }

    function load_db($category_id){

        DB::db_query("load_category", "SELECT * FROM category WHERE id='$category_id';");
        if(DB::db_num_rows("load_category") > 0){
            $data = DB::db_get_array("load_category");
            $this->load_array($data);
            return TRUE;
        } else {
            error("No category in database with that id\n");
            return FALSE;
        }

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->name = $array["name"];
        $this->description = $array["description"];

    }


    function match_states($filters){

        //test and see if the ticked bits are as they are supposed to be.

        //if the states are ticked that means they are that filter is required.

        //notated means the description is not empty
        //categorised means from links where type="REFERENCE_IN_CATEGORY" and from_id= the category_id

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

        //see if this category matches the search criteria
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
