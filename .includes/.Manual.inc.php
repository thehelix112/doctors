<?php
/*
File: .Manual.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Manual class. This class is responsible for Technical Report references.
	
*/

class Manual extends Reference {

    /* Data */

    var $OPTauthor;
    var $title;
    var $OPTorganisation;
    var $OPTyear;    
    var $OPTkeyword;
    var $OPTtype;
    var $OPTnumber;
    var $OPTaddress;
    var $OPTmonth;
    var $OPTnote;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Manual::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Manual::edit();
        } else if($action == ACTION_VIEW){
            Manual::view();
        } else if($action == ACTION_SAVE){
            $aref = new Manual;
            $aref->load_post(); 
            $aref->save();
            Reference::saved();
            Manual::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Manual::remove();
                unset($_SESSION["confirm"]);
            } else {
                Manual::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@Manual{".$this->reference_id.",\n";

        $expstr .= "\ttitle = {".$this->title."},\n";        
        if($this->OPTauthor != "") $expstr .= "\tauthor = {".$this->OPTauthor."},\n";
        if($this->OPTorganisation != "") $expstr .= "\torganization = {".$this->OPTorganisation."},\n";
        if($this->OPTyear != "") $expstr .= "\tyear = {".$this->OPTyear."},\n";
        if($this->OPTkeyword != "") $expstr .= "\tkey = {".$this->OPTkeyword."},\n";
        if($this->OPTnumber != "") $expstr .= "\tnumber = {".$this->OPTnumber."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }

    function view_remove(){
        $aref = new Manual;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        include($GLOBALS["draw_includes_path"]."/Reference/.ManualDelete.html");

    }

    function remove(){

        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);
        
        DB::db_query("delete_manual", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_manual") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.ManualDeleted.html");
        }
    }

    function search(){

        //run the search string on the manuals
        DB::db_query("search_manual", "SELECT * FROM reference WHERE reference_type='Manual'");
        if(DB::db_check_result("search_manual") > 0){
            $data = DB::db_get_array("search_manual");
            $this->load_array($data);
        }
         

    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $aref = new Manual;
        $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        if($this->resource_id != "" && $this->resource_id != "-1"){
            //need to get the values: institution, year, month, type, number
            $ares = new Company;
            $ares->load_db($this->resource_id);
            $this->load_resource($ares);
        }

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, institution, year)
        $tops = array();
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, type...)
        $mains = array();
        if(valid($this->OPTauthor)) $mains["Author:"] = $this->OPTauthor;
        if(valid($this->OPTorganisation)) $mains["Organisation:"] = $this->OPTorganisation;
        if(valid($this->OPTyear)) $mains["Year:"] = $this->OPTyear;
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
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

    function get_institutions() {
        return Link::get_to_links(LINK_REFERENCE_FROM_RESOURCE);
    }
    

    function edit(){                

        $_SESSION["action"] = ACTION_EDIT;        

        //allow ppl to change the type of the reference.
        Reference::select_type();
        
        //load the manual in case we're editing
        $aref = new Manual;
        if(isset($_SESSION["object_id"])){
            $result = $aref->load_db($_SESSION["object_id"]);
        }                
        
        $ares = new Company;
        //need to get the values: journal, year, month, type, number
        if($aref->resource_id != "" && $aref->resource_id != "-1") {           
            $ares->load_db($aref->resource_id);            
            $aref->load_resource($ares);
        }

        $resources = Manual::get_institutions();
        //sort the resources alphabetically according to title
        
        //$name = new Array();
        //$year = new Array();        

        foreach($resources as $key => $row){
            $name[$key] = $row["name"];
            $year[$key] = $row["year"];
        }

        array_multisort($name, SORT_ASC, $year, SORT_ASC, $resources);
        
        //MonthEdit.html is hardcoded to look at $this.
        $this->OPTmonth = $aref->OPTmonth;

        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditTop.html");
        
        //draw the Manual specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.ManualEdit.html");
        
        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.ManualOverview.html");

    }

    function save(){

        //save a new Institution as required

        if($this->resource_id == "new"){
            
            //save the Journal to the db then re-set the $this->resource_id appropriately

            $ares = new Company;

            //copy institution's name appropriately.
            $_POST["name"] = $_POST["organisation"]; 

            //temporarily unset the post["id"] and post["description"] 
            //so that the institution will insert.
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
            DB::db_query("get_organisation", "SELECT id FROM resource
                           WHERE name='".$ares->name."' AND
                           resource_type='".get_type_as_string(TYPE_COMPANY)."';");
            
            if(DB::db_check_result("get_organisation") > 0){
                $data = DB::db_get_array("get_organisation");                
                $this->resource_id = $data["id"];
                $this->load_resource($ares);
            } else {
                error("Could not find the Organisation we just added. Uh-oh.");                
            }            
            
        }        

        //write everything from the object to the database

        if(!isset($this->id)){         

            DB::db_query("save_manual", "INSERT INTO reference 
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
                        number,
                        month,
                        organisation,
                        address,
                        note, 
                        annote,
                        resource_id) VALUES 
                        ('".$this->title."', 
                        '".$this->reference_type."', 
                        '".$this->reference_id."',
                        '".$this->description."', 
                        '".$this->abstract."', 
                        '".$this->content."',
                        '".$this->weblink."', 
                        '".$this->OPTauthor."',
                        '".$this->OPTkeyword."', 
                        '".$this->OPTyear."', 
                        '".$this->OPTnumber."', 
                        '".$this->OPTmonth."', 
                        '".$this->OPTorganisation."', 
                        '".$this->OPTaddress."',
                        '".$this->OPTnote."', 
                        '".$this->OPTannote."',
                        '".$this->resource_id."')
                    ");
        } else {           

            DB::db_query("save_manual", "UPDATE reference SET
                        title = '".$this->title."',
                        reference_id = '".$this->reference_id."',
                        reference_type = '".$this->reference_type."',
                        description = '".$this->description."',
                        abstract = '".$this->abstract."',
                        content = '".$this->content."',
                        weblink = '".$this->weblink."',
                        author = '".$this->OPTauthor."', 
                        keywords = '".$this->OPTkeyword."',
                        year = '".$this->OPTyear."',  
                        number = '".$this->OPTnumber."', 
                        month = '".$this->OPTmonth."', 
                        organisation = '".$this->OPTorganisation."', 
                        address = '".$this->OPTaddress."',
                        note = '".$this->OPTnote."', 
                        annote = '".$this->OPTannote."',
                        resource_id = '".$this->resource_id."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Reference wide save things
        Reference::save($this);        

    }

    function load_post(){

        $data = $_POST;
        $data["resource_id"] = $data["company_select"];
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_manual", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_manual") > 0){
            $data = DB::db_get_array("load_manual");
            $this->load_array($data);

            //get values from assocated resource
            $ares = new Journal;
            if($ares->load_db($this->resource_id))
                $this->load_resource($ares); 

            return TRUE;            
        } else {
            error("No technical report in database with that id\n");
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
        $this->OPTauthor = $array["author"];
        $this->title = $array["title"];
        $this->OPTorganisation = $array["organisation"];
        $this->OPTyear = $array["year"];
        $this->OPTkeyword = $array["keywords"];
        $this->OPTnumber = $array["number"];
        $this->OPTaddress = $array["address"];
        $this->OPTmonth = $array["month"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_id = $array["resource_id"];
        $this->loaded = TRUE;

    }

    function load_resource($ares){

        if($ares->is_valid()){

            $this->OPTorganisation = $ares->name;
            $this->OPTaddress = $ares->OPTaddress;

        }        

    }

   

}

?>
