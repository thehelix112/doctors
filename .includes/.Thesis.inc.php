<?php
/*
File: .Thesis.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Thesis class. This class is responsible for both PhD and Masters thesis references.
	
*/

class Thesis extends Reference {

    /* Data */

    var $author;
    var $title;
    var $school;
    var $year;  
    var $thesis_type; // "Phd" or "Masters"
    var $OPTkeyword;
    var $OPTtype;
    var $OPTaddess;
    var $OPTmonth;
    var $OPTnote;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Thesis::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Thesis::edit();
        } else if($action == ACTION_VIEW){
            Thesis::view();
        } else if($action == ACTION_SAVE){
            $aref = new Thesis;
            $aref->load_post(); 
            $aref->save();
            Reference::saved();
            Thesis::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Thesis::remove();
                unset($_SESSION["confirm"]);
            } else {
                Thesis::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "";        

        if($this->thesis_type == "Phd")
            $startstr .= "@PhdThesis{".$this->reference_id.",\n";
        else if($this->thesis_type == "Masters")
            $startstr .= "@MastersThesis{".$this->reference_id.",\n";
        
        $expstr = "\tauthor = {".$this->author."},\n";
        $expstr .= "\ttitle = {".$this->title."},\n";
        $expstr .= "\tschool = {".$this->school."},\n";
        $expstr .= "\tyear = {".$this->year."},\n";
        if($this->OPTkey != "") $expstr .= "\tkey = {".$this->OPTkey."},\n";
        if($this->OPTtype != "") $expstr .= "\ttype = {".$this->OPTtype."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";
        if($this->OPTpages != "") $expstr .= "\tpages = {".$this->OPTpages."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }

    function view_remove(){
        $aref = new Thesis;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        include($GLOBALS["draw_includes_path"]."/Reference/.ThesisDelete.html");

    }

    function remove(){

        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);
        
        DB::db_query("delete_article", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_article") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.ThesisDeleted.html");
        }
    }

    function search(){

        //run the search string on the school articles
        DB::db_query("search_article", "SELECT * FROM reference WHERE reference_type='Thesis'");
        if(DB::db_check_result("search_article") > 0){
            $data = DB::db_get_array("search_article");
            $this->load_array($data);
        }
         

    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $aref = new Thesis;
        $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        if($this->resource_id != "" && $this->resource_id != "-1"){
            //need to get the values: school, year, month, type, address
            $ares = new School;
            $ares->load_db($this->resource_id);
            $this->load_resource($ares);
        }

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, school, year)
        $tops = array("Author:" => $this->author, 
                        "Title:" => $this->title, 
                        "School:" => $this->school, 
                        "Year:" => $this->year);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, type...)
        $mains = array();
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTtype)) $mains["Type:"] = $this->OPTtype;
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTnote)) $mains["Note:"] = $this->OPTnote;
        if(valid($this->OPTannote)) $mains["Annote:"] = $this->OPTannote;
        while(list($name1, $value1) = each($mains)){
            list($name2, $value2) = each($mains);
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewMainItem.html");
        }

        $this->display_links();

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewBottom.html");
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewActions.html");


    }

    function get_schools() {
        return Link::get_to_links(LINK_REFERENCE_FROM_RESOURCE);
    }
    

    function edit(){                

        $_SESSION["action"] = ACTION_EDIT;        

        //allow ppl to change the type of the reference.
        Reference::select_type();
        
        //load the article in case we're editing
        $aref = new Thesis;
        if(isset($_SESSION["object_id"])){
            $result = $aref->load_db($_SESSION["object_id"]);
        }                
        
        $ares = new School;
        //need to get the values: school, year, month, type, address
        if($aref->resource_id != "" && $aref->resource_id != "-1") {           
            $ares->load_db($aref->resource_id);            
            $aref->load_resource($ares);
        }

        $resources = Thesis::get_schools();
        
        //MonthEdit.html is hardcoded to look at $this.
        $this->OPTmonth = $aref->OPTmonth;
        
        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditTop.html");
        
        //draw the Thesis specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.ThesisEdit.html");
        
        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.ThesisOverview.html");

    }

    function save(){

        //save a new School as required

        if($this->resource_id == "new"){
            
            //save the School to the db then re-set the $this->resource_id appropriately

            $ares = new School;

            //copy school's name appropriately.
            $_POST["name"] = $_POST["school"]; 

            //temporarily unset the post["id"] and post["description"] 
            //so that the school will insert.
            $temp = array();            
            $temp["id"] = $_POST["id"];
            unset($_POST["id"]);            
            $temp["description"] = $_POST["description"];
            $_POST["description"] = "Details to be Set. Resource added via Reference.";
           
            $ares->load_post();            
            $ares->save();       

            //reset the post["id"] appropriately
            $_POST["id"] = $temp["id"];            
            $_POST["description"] = $temp["description"];

            //now go and look it up again to get the id.
            DB::db_query("get_school", "SELECT id FROM resource
                           WHERE name='".$ares->name."' AND
                           address='".$ares->OPTaddress."' AND
                           resource_type='".get_type_as_string(TYPE_SCHOOL)."';");
            
            if(DB::db_check_result("get_school") > 0){
                $data = DB::db_get_array("get_school");                
                $this->resource_id = $data["id"];
                $this->load_resource($ares);
            } else {
                error("Could not find the School we just added. Uh-oh.");                
            }            
            
        }        

        //write everything from the object to the database

        if(!isset($this->id)){         

            DB::db_query("save_article", "INSERT INTO reference 
                        (title, 
                        reference_type,
                        reference_id,
                        description, 
                        abstract, 
                        content,
                        weblink, 
                        author,
                        keywords, 
                        year,
                        type,
                        address,
                        month,
                        school,
                        note, 
                        annote,
                        resource_id,
                        thesis_type) VALUES 
                        ('".$this->title."', 
                        '".$this->reference_type."', 
                        '".$this->reference_id."',
                        '".$this->description."', 
                        '".$this->abstract."', 
                        '".$this->content."',
                        '".$this->weblink."', 
                        '".$this->author."',
                        '".$this->OPTkeyword."', 
                        '".$this->year."', 
                        '".$this->OPTtype."', 
                        '".$this->OPTaddress."', 
                        '".$this->OPTmonth."', 
                        '".$this->school."', 
                        '".$this->OPTnote."', 
                        '".$this->OPTannote."',
                        '".$this->resource_id."',
                        '".$this->thesis_type."')
                    ");
        } else {           

            DB::db_query("save_article", "UPDATE reference SET
                        title = '".$this->title."',
                        reference_id = '".$this->reference_id."',
                        reference_type = '".$this->reference_type."',
                        description = '".$this->description."',
                        abstract = '".$this->abstract."',
                        content = '".$this->content."',
                        weblink = '".$this->weblink."',
                        author = '".$this->author."', 
                        keywords = '".$this->OPTkeyword."',
                        year = '".$this->year."', 
                        type = '".$this->OPTtype."', 
                        address = '".$this->OPTaddress."', 
                        month = '".$this->OPTmonth."', 
                        school = '".$this->school."', 
                        note = '".$this->OPTnote."', 
                        annote = '".$this->OPTannote."',
                        resource_id = '".$this->resource_id."',
                        thesis_type = '".$this->thesis_type."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Reference wide save things
        Reference::save($this);        

    }

    function load_post(){

        $data = $_POST;
        $data["resource_id"] = $data["school_select"];
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_article", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_article") > 0){
            $data = DB::db_get_array("load_article");
            $this->load_array($data);

            //get values from assocated resource
            $ares = new School;
            if($ares->load_db($this->resource_id))
                $this->load_resource($ares); 

            return TRUE;            
        } else {
            error("No article in database with that id\n");
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
        $this->author = $array["author"];
        $this->title = $array["title"];
        $this->school = $array["school"];
        $this->year = $array["year"];
        $this->thesis_type = $array["thesis_type"];        
        $this->OPTkeyword = $array["keywords"];
        $this->OPTtype = $array["type"];
        $this->OPTaddress = $array["address"];
        $this->OPTmonth = $array["month"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_id = $array["resource_id"];
        $this->loaded = TRUE;

    }

    function load_resource($ares){


        if($ares->is_valid()){
            
            $this->school = $ares->name;
            $this->OPTaddress = $ares->OPTaddress;
            
        }
        

    }

   

}

?>
