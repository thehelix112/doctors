<?php
/*
File: .InProceedings.inc.php
Author: David Andrews
Date Started: 28/09/2003
Synopsis:

    This file defines the InProceedings class. This class is responsible for conference paper references.
	
*/

class InProceedings extends Reference {

    /* Data */

    var $author;
    var $booktitle;
    var $OPTkey;
    var $OPTpages;
    var $OPTpublisher;
    var $OPTyear;
    var $OPTeditor;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTseries;
    //var $OPTtype;
    //var $OPTchapter;
    var $OPTaddress;
    var $OPTmonth;
    //var $OPTedition;
    var $OPTorganisation;
    var $OPTnote;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            InProceedings::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            InProceedings::edit();
        } else if($action == ACTION_VIEW){
            InProceedings::view();
        } else if($action == ACTION_SAVE){
            $aref = new InProceedings;
            $aref->load_post(); 
            $aref->save();

            Reference::saved();
            InProceedings::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                InProceedings::remove();
                unset($_SESSION["confirm"]);
            } else {
                InProceedings::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@InProceedings{".$this->reference_id.",\n";

        $expstr = "\tauthor = {".$this->author."},\n";
        $expstr .= "\ttitle = {".$this->title."},\n";
        $expstr .= "\tbooktitle = {Proceedings of the ".$this->booktitle."},\n";
        if($this->OPTyear != "") $expstr .= "\tyear = {".$this->OPTyear."},\n";
        if($this->OPTeditor != "") $expstr .= "\teditor = {".$this->OPTeditor."},\n";
        if($this->OPTkey != "") $expstr .= "\tkey = {".$this->OPTkey."},\n";
        if($this->OPTseries != "") $expstr .= "\tseries = {".$this->OPTseries."},\n";
        //if($this->OPTtype != "") $expstr .= "\ttype = {".$this->OPTtype."},\n";
        //if($this->OPTchapter != "") $expstr .= "\tchapter = {".$this->OPTchapter."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";
        if($this->OPTorganisation != "") $expstr .= "\torganization = {".$this->OPTorganisation."},\n";
        if($this->OPTpublisher != "") $expstr .= "\tpublisher = {".$this->OPTpublisher."},\n";
        //if($this->OPTedition != "") $expstr .= "\tedition = {".$this->OPTedition."},\n";
        if($this->OPTvolume != "") $expstr .= "\tvolume = {".$this->OPTvolume."},\n";
        if($this->OPTnumber != "") $expstr .= "\tnumber = {".$this->OPTnumber."},\n";
        if($this->OPTpages != "") $expstr .= "\tpages = {".$this->OPTpages."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }


    function view_remove(){
        $aref = new InProceedings;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Reference/.InProceedingsDelete.html");

    }

    function remove(){
        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);

        DB::db_query("delete_inproceedings", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_inproceedings") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.InProceedingsDeleted.html");
        }
    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"]

        $aref = new InProceedings;

        $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, journal, year)
        $tops = array("Author:" => $this->author,
                    "Booktitle:" => $this->booktitle);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTyear)) $mains["Year:"] = $this->OPTyear;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
        if(valid($this->OPTpages)) $mains["Pages:"] = $this->OPTpages;
        if(valid($this->OPTpublisher)) $mains["Publisher:"] = $this->OPTpublisher;
        if(valid($this->OPTeditor)) $mains["Editor:"] = $this->OPTeditor;
        if(valid($this->OPTseries)) $mains["Series:"] = $this->OPTseries;
        //if(valid($this->OPTtype)) $mains["Type:"] = $this->OPTtype;
        //if(valid($this->OPTedition)) $mains["Edition:"] = $this->OPTedition;
        //if(valid($this->OPTchapter)) $mains["Chapter:"] = $this->OPTchapter;
        if(valid($this->OPTorganisation)) $mains["Organisation:"] = $this->OPTorganisation;
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
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

    function get_conferences() {
        return Link::get_to_links(LINK_REFERENCE_FROM_RESOURCE);
    }

    function edit(){

        //allow ppl to change the type of the reference.
        Reference::select_type();

        //load the article in case we're editing
        if(isset($_SESSION["object_id"])){
            $aref = new InProceedings;
            $result = $aref->load_db($_SESSION["object_id"]);
        }

        $ares = new Conference;
        //need to get the values: journal, year, month, volume, number
        if($aref->resource_id != "" && $aref->resource_id != "-1") {
            $ares->load_db($aref->resource_id);
            $aref->load_resource($ares);
        }           
        
        $resources = InProceedings::get_conferences();
        //sort the resources alphabetically according to title
        
        //$name = new Array();
        //$year = new Array();        

        foreach($resources as $key => $row){
            //extract the name portion

            //if the first letter is a number then
            //trim the first word
            $fullname = $row["name"];
            

            if(is_numeric(substr($fullname, 0, 1))){
                $correctname = substr($fullname, strpos($fullname, " "), strlen($fullname)-strpos($fullname, " "));
                //echo $correctname."<br>";
                
            }
            

            $name[$key] = $correctname;
            $year[$key] = $row["year"];
        }

        array_multisort($name, SORT_ASC, $year, SORT_ASC, $resources);
        //array_multisort($name, SORT_ASC, $resources);

        $this->OPTmonth = $aref->OPTmonth;

        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditTop.html");

        //draw the JournalArticle specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.InProceedingsEdit.html");

        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");
        
    }

    /* begin instance functions */

    function get_search_haystack(){

        return $this->author." ".$this->booktitle." ";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.InProceedingsOverview.html");

    }

    function save(){

        if($this->resource_id == "new"){            

            //save the Conference to the db then re-set the $this->resource_id appropriately

            $ares = new Conference;

            //copy conference's name appropriately.
            $_POST["name"] = $this->booktitle; 

            //temporarily unset the post["id"] and post["description"] 
            //so that the journal will insert.
            $temp = array();            
            $temp["id"] = $_POST["id"];
            unset($_POST["id"]);            
            $temp["note"] = $_POST["note"];
            unset($_POST["note"]);            
            $temp["annote"] = $_POST["annote"];
            unset($_POST["annote"]);            

            $temp["description"] = $_POST["description"];
            $_POST["description"] = "Details to be Set. Resource added via Reference.";

            $ares->load_post();            
            $ares->save();

            //reset the post["id"] appropriately
            $_POST["id"] = $temp["id"];            
            $_POST["note"] = $temp["note"];            
            $_POST["annote"] = $temp["annote"];            
            $_POST["description"] = $temp["description"];

            //now go and look it up again to get the id.
            DB::db_query("get_conference", "SELECT id FROM resource
                           WHERE name='".$ares->name."' AND
                           resource_type='".get_type_as_string(TYPE_CONFERENCE)."';");
            
            if(DB::db_check_result("get_conference") > 0){
                $data = DB::db_get_array("get_conference");                
                $this->resource_id = $data["id"];
                $this->load_resource($ares);                
            } else {
                error("Could not find the Conference we just added. Uh-oh.");                
            }            

        }

        //write everything from the object to the database

        if(!isset($this->id)){

            //type, chapter, edition,
            //'".$this->OPTtype."', '".$this->OPTchapter."', '".$this->OPTedition."', 
            DB::db_query("save_inproceedings", "INSERT INTO reference 
                        (title, reference_type, reference_id,
                        description, abstract, content,
                        weblink, author, year,
                        booktitle, keywords, volume,
                        number, pages, month, 
                        publisher, editor, series,
                        address, note, annote,
                        organisation, resource_id) VALUES
                        ('".$this->title."', '".$this->reference_type."', '".$this->reference_id."',
                        '".$this->description."', '".$this->abstract."', '".$this->content."',
                        '".$this->weblink."', '".$this->author."', '".$this->OPTyear."',
                        '".$this->booktitle."', '".$this->OPTkey."', '".$this->OPTvolume."', 
                        '".$this->OPTnumber."', '".$this->OPTpages."', '".$this->OPTmonth."', 
                        '".$this->OPTpublisher."', '".$this->OPTeditor."', '".$this->OPTseries."', 
                        '".$this->OPTaddress."', '".$this->OPTnote."', '".$this->OPTannote."',
                        '".$this->OPTorganisation."', '".$this->resource_id."');
                    ");
        } else {
           
            //type = '".$this->OPTtype."',
            //chapter = '".$this->OPTchapter."', edition = '".$this->OPTedition."',
            DB::db_query("save_inproceedings", "UPDATE reference SET
                        reference_id = '".$this->reference_id."',
                        reference_type = '".$this->reference_type."',
                        description = '".$this->description."',
                        abstract = '".$this->abstract."',
                        content = '".$this->content."',
                        weblink = '".$this->weblink."',
                        author = '".$this->author."', title = '".$this->title."', 
                        year = '".$this->OPTyear."', booktitle = '".$this->booktitle."', 
                        keywords = '".$this->OPTkey."', volume = '".$this->OPTvolume."', 
                        number = '".$this->OPTnumber."', pages = '".$this->OPTpages."',
                        publisher = '".$this->OPTpublisher."', editor = '".$this->OPTeditor."',
                        series = '".$this->OPTseries."', 
                        address = '".$this->OPTaddress."',
                        month = '".$this->OPTmonth."', note = '".$this->OPTnote."', 
                        annote = '".$this->OPTannote."', 
                        resource_id = '".$this->resource_id."', organisation = '".$this->OPTorganisation."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Reference wide save things
        Reference::save($this); 

    }

    function load_post(){

        $data = $_POST;
        $data["resource_id"] = $data["conference_select"];
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_inproceedings", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_inproceedings") > 0){
            $data = DB::db_get_array("load_inproceedings");
            $this->load_array($data);
            
            //get values from assocated resource
            $ares = new Conference;
            if($ares->load_db($this->resource_id))
                $this->load_resource($ares);            

            return TRUE;
        } else {
            error("No conference paper in database with that id\n");
            return FALSE;
        }

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->reference_id = $array["reference_id"];
        $this->reference_type = $array["reference_type"];
        $this->content = $array["content"];
        $this->weblink = $array["weblink"];
        $this->abstract = $array["abstract"];
        $this->description = $array["description"];
        $this->author = $array["author"];
        $this->title = $array["title"];
        $this->booktitle = $array["booktitle"];
        $this->OPTyear = $array["year"];
        $this->OPTeditor = $array["editor"];
        $this->OPTseries = $array["series"];
        //$this->OPTtype = $array["type"];
        //$this->OPTchapter = $array["chapter"];
        $this->OPTaddress = $array["address"];
        $this->OPTorganisation = $array["organisation"];
        $this->OPTpublisher = $array["publisher"];
        $this->OPTkey = $array["keywords"];
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTpages = $array["pages"];
        $this->OPTmonth = $array["month"];
        //$this->OPTedition = $array["edition"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_id = $array["resource_id"];
        $this->loaded = TRUE;

    }

    function load_resource($ares){

        if($ares->is_valid()){
            
            $this->OPTyear = $ares->year;
            $this->OPTvolume = $ares->OPTvolume;
            $this->OPTnumber = $ares->OPTnumber;
            $this->OPTmonth = $ares->OPTmonth;
            $this->OPTaddress = $ares->OPTaddress;
            $this->OPTorganisation = $ares->OPTorganisation;
            $this->OPTpublisher = $ares->OPTpublisher;
            $this->OPTseries = $ares->OPTseries;
            $this->OPTeditor = $ares->OPTeditor;
            
        }           

    }

}

?>
