<?php

/*
File: .Reference.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Reference class. This class is extended for each different reference type.
	
*/

class Reference {

    /* Data */

    var $id;            //store only the database id of the reference.
    var $reference_id;  //store only the database id of the reference.
    var $title;         //store only the title of the reference.
    var $description;   //the description of the reference.
    var $abstract;      //the abstract of the reference.
    var $content;       //the content of the reference.
    var $weblink;       //the weblink of the reference.
    var $reference_type;//the type of the reference.
    var $loaded;        //determines whether the reference data has been loaded from the db.
    var $resource_id;   //the database id oF the resource which this reFerence is From

    function main(){

        switch($_SESSION["action"]){

            case ACTION_ADD:
                Reference::select_type();
                break;
            case ACTION_SEARCH:
                if($_SESSION["confirm"] == "TRUE"){
                    Reference::search();
                    unset($_SESSION["confirm"]);
                } else {
                    Reference::view_search();
                }
                break;
            case ACTION_BROWSE:
                Reference::browse();
                break;
            default:
                include($GLOBALS["draw_includes_path"]."/Reference/.Reference.html");

        }

    }

    function browse(){

        //in essence tick all types then do a search
        $_POST["mask_".get_type_as_string(TYPE_ARTICLE)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_INPROCEEDINGS)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_INCOLLECTION)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_INBOOK)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_BOOKLET)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_THESIS)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_TECHREPORT)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_MANUAL)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_UNPUBLISHED)] = "on";
        $_POST["mask_".get_type_as_string(TYPE_MISC)] = "on";

        Reference::search();

    }

    function search(){

        //print the top bit
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceSearchTop.html");

        //begin searching
        $get_types = Reference::get_types();
        $filters = Reference::get_filters();
        $counter = count($get_types);

        //interpret the search needle (into phrases etc) 
        if(strlen($_POST["search_needles"]) > 0){
            $needles = get_needles($_POST["search_needles"]);
        } else {
            $needles = array();
        }

        $dark = TRUE;

        //get the users reference
        $reference_links = Link::get_links(ANY, $_SESSION["user_id"], LINK_REFERENCE_OF_USER);

        for($i = 0; $i < count($reference_links); $i++){

            DB::db_query("get", "SELECT * FROM reference WHERE id='".$reference_links[$i]["from_id"]."';");
            if(DB::db_check_result("get") > 0){

                //check that its in the gets_type list
                if($counter > 0 && ($data = DB::db_get_array("get")) != FALSE){

                    if($get_types[$data["reference_type"]]){
                        //continue checking
                        //change this data variable to an instance of the appropriate class                        

                        switch($data["reference_type"]){

                            case get_type_as_string(TYPE_ARTICLE): 
                                $ref = new Article;
                                break;

                            case get_type_as_string(TYPE_INPROCEEDINGS):
                                $ref = new InProceedings;
                                break;

                            case get_type_as_string(TYPE_INCOLLECTION):
                                $ref = new InCollection;
                                break;

                            case get_type_as_string(TYPE_INBOOK):
                                $ref = new InBook;
                                break;

                            case get_type_as_string(TYPE_BOOKLET):
                                $ref = new Booklet;
                                break;

                            case get_type_as_string(TYPE_THESIS):
                                $ref = new Thesis;
                                break;

                            case get_type_as_string(TYPE_TECHREPORT):
                                $ref = new TechReport;
                                break;

                            case get_type_as_string(TYPE_MANUAL):
                                $ref = new Manual;
                                break;

                            case get_type_as_string(TYPE_UNPUBLISHED):
                                $ref = new Unpublished;
                                break;

                            case get_type_as_string(TYPE_MISC):
                                $ref = new Misc;
                                break;
                        }

                        $ref->load_array($data);
                        if($ref->match_string($needles) &&
                            $ref->match_states($filters)){
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
                error("No references in database\n");
                return FALSE;
            }
            
        }

        //print the bottom bit
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceSearchBottom.html");

    }

    function get_filters(){

        $get_filters = array();

        if($_POST["mask_".get_state_as_string(STATE_NOTATED)] == "on"){
            $get_filters[STATE_NOTATED] = true;
        }
        if($_POST["mask_".get_state_as_string(STATE_LINKED)] == "on"){
            $get_filters[STATE_LINKED] = true;
        }
        if($_POST["mask_".get_state_as_string(STATE_CATEGORISED)] == "on"){
            $get_filters[STATE_CATEGORISED] = true;
        }
        if($_POST["mask_".get_state_as_string(STATE_HASDOCUMENT)] == "on"){
            $get_filters[STATE_HASDOCUMENT] = true;
        }

        return $get_filters;

    }
 
    function get_types(){

        $get_types = array();

        if($_POST["mask_".get_type_as_string(TYPE_ARTICLE)] == "on"){
            $get_types[get_type_as_string(TYPE_ARTICLE)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_INPROCEEDINGS)] == "on"){
            $get_types[get_type_as_string(TYPE_INPROCEEDINGS)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_INCOLLECTION)] == "on"){
            $get_types[get_type_as_string(TYPE_INCOLLECTION)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_INBOOK)] == "on"){            
            $get_types[get_type_as_string(TYPE_INBOOK)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_PROCEEDINGS)] == "on"){
            $get_types[get_type_as_string(TYPE_PROCEEDINGS)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_BOOKLET)] == "on"){
            $get_types[get_type_as_string(TYPE_BOOKLET)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_THESIS)] == "on"){
            $get_types[get_type_as_string(TYPE_THESIS)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_TECHREPORT)] == "on"){
            $get_types[get_type_as_string(TYPE_TECHREPORT)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_MANUAL)] == "on"){
            $get_types[get_type_as_string(TYPE_MANUAL)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_UNPUBLISHED)] == "on"){
            $get_types[get_type_as_string(TYPE_UNPUBLISHED)] = true;
        }
        if($_POST["mask_".get_type_as_string(TYPE_MISC)] == "on"){
            $get_types[get_type_as_string(TYPE_MISC)] = true;
        }

        return $get_types;

    }

    function view_search(){

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceSearch.html");

    }

    function saved(){

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceSaved.html");

    }

    function select_type(){

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceSelectType.html");

    }

    function is_reference_object_type($object_type){

        switch($object_type){

        case TYPE_REFERENCE:
        case TYPE_ARTICLE:
        case TYPE_INPROCEEDINGS:
        case TYPE_INCOLLECTION:
        case TYPE_INBOOK:
        case TYPE_BOOKLET:
        case TYPE_THESIS:
        case TYPE_TECHREPORT:
        case TYPE_MANUAL:
        case TYPE_UNPUBLISHED:
        case TYPE_MISC:
        case TYPE_DOCUMENT: //implicitly
            return TRUE;
            break;            
            
        default:
            return FALSE;
        }


    }

    function remove($reference_id){
     
        //peform Reference wide remove stuffs
        //this primarily means remove links for this reference        

        DB::db_query("delete_reference_links", "DELETE FROM links WHERE from_id='".$reference_id."' AND type <= '5';");
        if(DB::db_check_result("delete_reference_links") > 0){
            //success
        }
        
   
    }    

    function save($ref){

        //perform Reference wide save stuffs.
        //this primarily means add/update links

        //lookup user id.
        DB::db_query("user_id_lookup", "SELECT id FROM \"user\" WHERE username='".$_SESSION["username"]."';");
        if(DB::db_check_result("user_id_lookup") > 0){
            $user_id = DB::db_get_field("user_id_lookup", "id");                
            if($user_id == "") error("Bad User id.");                
        }
        
        //lookup reference id
        DB::db_query("reference_id_lookup", "SELECT id FROM reference WHERE reference_id='".$ref->reference_id."';");
        if(DB::db_check_result("reference_id_lookup") > 0){
            $reference_id = DB::db_get_field("reference_id_lookup", "id");
            if($reference_id == "") error("Bad Reference id.");                
        }

        if(!isset($ref->id)){
        
            //this means we are inserting so we need to: look up the
            //inserted reference's id, look up the users id, then
            //insert the REFERENCE_OF_USER link as appropriate.

            $link = new Link;
            $link->from_id = $reference_id;
            $link->to_id = $user_id;
            $link->type = LINK_REFERENCE_OF_USER;
            $link->save();
            
        }

        /* This is not being used atm as resource_id is in the reference table

        //we are updating hence we need to: check if the
        //REFERENCE_FROM_RESOURCE appropriate link exists, if it doesn't
        //then add it.

        if($ref->resource_id != "" && $ref->resource_id != "-1"){

            echo "here";
            

            DB::db_query("link_id_lookup", "SELECT id FROM links WHERE from_id='".$ref->reference_id."' 
                                              AND to_id='".$ref->resource_id."' 
                                              AND type='".LINK_REFERENCE_FROM_RESOURCE."';");
            if(DB::db_num_rows("link_id_lookup") <= 0){
            
                $link = new Link;
                $link->id = $link_id;
                $link->from_id = $reference_id;
                $link->to_id = $ref->resource_id;
                $link->type = LINK_REFERENCE_FROM_RESOURCE;
                $link->save();        
                
            }
            
        } */
        
    }

    function get_links($from_id, $to_id, $link_type){

        return Link::get_links($from_id, $to_id, $link_type);

    }

    function display_links(){
            
        $this->display_document_links();
        $this->display_category_links();
        $this->display_cites_links();        
        $this->display_cited_links();        
            
    }
    

    function display_document_links(){   

        echo "
                <tr><td class=\"article_bottom\" valign=\"top\" align=\"right\">
                    <p class=\"resource_main\">
                    Documents:
                    </td>
                    <td class=\"article_bottom\" colspan=\"3\" align=\"left\">
                    <p class=\"view_categories\">";

        $data = $this->get_links(ANY, $this->id, LINK_DOCUMENT_OF_REFERENCE);
        if(count($data) > 0){
            echo Link::display_links($data, LINK_FROM);
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

        $data = $this->get_links($this->id, ANY, LINK_REFERENCE_IN_CATEGORY);
        if(count($data) > 0){
            echo Link::display_links($data, LINK_SELF, "<br/>");
        }
        echo "      </p>";
        //echo Link::display_add("Add", $this->id, LINK_REFERENCE_IN_CATEGORY);

        echo "  </td></tr>";
        
    }

    function display_cited_links(){   

        echo "

                <tr><td class=\"article_bottom\" valign=\"top\" align=\"right\">
                    <p class=\"resource_main\">
                    Cited By:
                    </td>
                    <td class=\"article_bottom\" colspan=\"3\" align=\"left\">
                    <p class=\"view_categories\">";

        $data = $this->get_links(ANY, $this->id, LINK_REFERENCE_CITES_REFERENCE);
        if(count($data) > 0){
            echo Link::display_links($data, LINK_SELF, "<br/>", LINK_FROM );
        }
        echo "      </p>";

        echo "  </td></tr>";
        
    }

    function display_cites_links(){   

        echo "
                <tr><td class=\"article_bottom\" valign=\"top\" align=\"right\">
                    <p class=\"resource_main\">
                    Cites:
                    </td>
                    <td class=\"article_bottom\" colspan=\"3\" align=\"left\">
                    <p class=\"view_categories\">";

        $data = $this->get_links($this->id, ANY, LINK_REFERENCE_CITES_REFERENCE);
        if(count($data) > 0){
            echo Link::display_links($data, LINK_SELF, "<br/>");
        }
        echo "      </p>";

        echo "  </td></tr>";
        
    }

        

    /* begin instance functions */

    function match_states($filters){

        //test and see if the ticked bits are as they are supposed to be.

        //if the states are ticked that means they are that filter is required.

        //notated means the description is not empty
        //categorised means from links where type="REFERENCE_IN_CATEGORY" and from_id= the reference_id

        if($filters[STATE_NOTATED]){
            //has to be notated
            if(trim($this->description) == ""){
                return FALSE;
            }
        }

        if($filters[STATE_CATEGORISED]){
            //has to be categorised
            DB::db_query("categorised", 
                "SELECT * 
                    FROM links 
                    WHERE type='".LINK_REFERENCE_IN_CATEGORY."' 
                        AND from_id='".$this->id."';");
            if(DB::db_check_result("categorised")){
                if(DB::db_num_rows("categorised") < 1){
                    return FALSE;
                }
            }
        }

        if($filters[STATE_LINKED]){
            //has to be have been linked
            DB::db_query("linked", 
                "SELECT * 
                    FROM links 
                    WHERE (type='".LINK_REFERENCE_CITES_REFERENCE."' 
                        OR type='".LINK_REFERENCE_CITES_REFERENCE."')
                        AND from_id='".$this->id."';");
            if(DB::db_check_result("linked")){
                if(DB::db_num_rows("linked") < 1){
                    return FALSE;
                }
            }
        }

        if($filters[STATE_HASDOCUMENT]){
            //has to have a document associated with it
            DB::db_query("hasdocument", 
                "SELECT * 
                    FROM documents
                    WHERE reference_id='".$this->id."';");
            if(DB::db_check_result("hasdocument")){
                if(DB::db_num_rows("hasdocument") < 1){
                    return FALSE;
                }
            }
        }

        return TRUE;

    }

    function match_string($needles){

        if(count($needles) == 0){
            return TRUE;
        }

        //see if this reference matches the search criteria
        //get the appropriate string from the subclass instance
        $search_haystack = $this->title." ".$this->content." ".$this->abstract." ".
            $this->description." ".$this->get_search_haystack();
            
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

    function load_db($reference_id){

        DB::db_query("load_article", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_article") > 0){
            $data = DB::db_get_array("load_article");
            $this->load_array($data);
            return TRUE;            
        } else {
            error("No reference in database with that id\n");
            return FALSE;
        }

    }


   function load_array($array){

       $this->id = $array["id"];
       $this->reference_id = $array["reference_id"];
       $this->reference_type = $array["reference_type"];
       $this->content = $array["content"];
       $this->abstract = $array["abstract"];
       $this->description = $array["description"];
       $this->weblink = $array["weblink"];
       $this->title = $array["title"];
       
   }
   

}

?>
