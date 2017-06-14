<?php
/*
File: .InBook.inc.php
Author: David Andrews
Date Started: 28/09/2003
Synopsis:

    This file defines the InBook class. This class is responsible for portions of book references.
	
*/

class InBook extends Reference {

    /* Data */

    var $author;
    var $title;
    var $editor;
    var $publisher;
    var $year;
    var $chapter;
    var $OPTkey;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTseries;
    var $OPTtype;
    var $OPTaddress;
    var $OPTedition;
    var $OPTmonth;
    var $OPTpages;
    var $OPTnote;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            InBook::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            InBook::edit();
        } else if($action == ACTION_VIEW){
            InBook::view();
        } else if($action == ACTION_SAVE){
            $aref = new InBook;
            $aref->load_post(); 
            $aref->save();

            Reference::saved();
            InBook::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                InBook::remove();
                unset($_SESSION["confirm"]);
            } else {
                InBook::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@InBook{".$this->reference_id.",\n";

        $expstr = "\tauthor = {".$this->author."},\n";
        $expstr .= "\ttitle = {".$this->title."},\n";
        $expstr .= "\tchapter = {".$this->chapter."},\n";
        $expstr .= "\tyear = {".$this->year."},\n";
        $expstr .= "\teditor = {".$this->editor."},\n";
        $expstr .= "\tpublisher = {".$this->publisher."},\n";
        if($this->OPTkey != "") $expstr .= "\tkey = {".$this->OPTkey."},\n";
        if($this->OPTvolume != "") $expstr .= "\tvolume = {".$this->OPTvolume."},\n";
        if($this->OPTnumber != "") $expstr .= "\tnumber = {".$this->OPTnumber."},\n";
        if($this->OPTseries != "") $expstr .= "\tseries = {".$this->OPTseries."},\n";
        if($this->OPTtype != "") $expstr .= "\ttype = {".$this->OPTtype."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";        
        if($this->OPTedition != "") $expstr .= "\tedition = {".$this->OPTedition."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTpages != "") $expstr .= "\tpages = {".$this->OPTpages."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }


    function view_remove(){
        $aref = new InBook;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Reference/.InBookDelete.html");

    }

    function remove(){
        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);

        DB::db_query("delete_inbook", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_inbook") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.InBookDeleted.html");
        }
    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"]

        $aref = new InBook;

        $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, journal, year)
        $tops = array("Author:" => $this->author,
                      "Editor:" => $this->editor,
                      "Chapter:" => $this->chapter,
                      "Publisher:" => $this->publisher,
                      "Year:" => $this->year,);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
        if(valid($this->OPTpages)) $mains["Pages:"] = $this->OPTpages;
        if(valid($this->OPTseries)) $mains["Series:"] = $this->OPTseries;
        if(valid($this->OPTtype)) $mains["Type:"] = $this->OPTtype;
        if(valid($this->OPTedition)) $mains["Edition:"] = $this->OPTedition;
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

    function get_books() {
        return Link::get_to_links(LINK_REFERENCE_FROM_RESOURCE);
    }

    function edit(){

        //allow ppl to change the type of the reference.
        Reference::select_type();

        //load the article in case we're editing
        if(isset($_SESSION["object_id"])){
            $aref = new InBook;
            $result = $aref->load_db($_SESSION["object_id"]);
        }
        
        $ares = new Book;
        //need to get the values: journal, year, month, volume, number
        if($aref->resource_id != "" && $aref->resource_id != "-1") {
            if(!$ares->load_db($aref->resource_id))
                $aref->resource_id = "-1";            
            else 
                $aref->load_resource($ares);
        }           
        
        $resources = InBook::get_books(); 

        $this->OPTmonth = $aref->OPTmonth;

        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditTop.html");

        //draw the JournalArticle specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.InBookEdit.html");

        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");
        
    }

    /* begin instance functions */

    function get_search_haystack(){

        return "";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.InBookOverview.html");

    }

    function save(){

        if($this->resource_id == "new"){            

            //save the Book to the db then re-set the $this->resource_id appropriately

            $ares = new Book;

            //copy book's name appropriately.
            $_POST["name"] = $this->title; 

            //temporarily unset the post["id"] and post["description"] 
            //so that the journal will insert.
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
            DB::db_query("get_book", "SELECT id FROM resource
                           WHERE name='".$ares->name."' AND
                           year='".$ares->year."' AND
                           resource_type='".get_type_as_string(TYPE_BOOK)."';");
            
            if(DB::db_check_result("get_book") > 0){
                $data = DB::db_get_array("get_book");                
                $this->resource_id = $data["id"];
                $this->load_resource($ares);  
            } else {
                error("Could not find the Book we just added. Uh-oh.");                
            }            

        }

        //write everything from the object to the database

        if(!isset($this->id)){

            DB::db_query("save_inbook", "INSERT INTO reference 
                        (title, reference_type, reference_id,
                        description, abstract, content,
                        weblink, author, year,
                        keywords, volume,
                        number, pages, month, 
                        publisher, editor, series,
                        type, chapter, address,
                        edition, note, annote,
                        resource_id) VALUES
                        ('".$this->title."', '".$this->reference_type."', '".$this->reference_id."',
                        '".$this->description."', '".$this->abstract."', '".$this->content."',
                        '".$this->weblink."', '".$this->author."', '".$this->year."',
                        '".$this->OPTkey."', '".$this->OPTvolume."', 
                        '".$this->OPTnumber."', '".$this->OPTpages."', '".$this->OPTmonth."', 
                        '".$this->publisher."', '".$this->editor."', '".$this->OPTseries."', 
                        '".$this->OPTtype."', '".$this->chapter."', '".$this->OPTaddress."', 
                        '".$this->OPTedition."', '".$this->OPTnote."', '".$this->OPTannote."',
                        '".$this->resource_id."');
                    ");
        } else {
            
            DB::db_query("save_inbook", "UPDATE reference SET
                        reference_id = '".$this->reference_id."',
                        reference_type = '".$this->reference_type."',
                        description = '".$this->description."',
                        abstract = '".$this->abstract."',
                        content = '".$this->content."',
                        weblink = '".$this->weblink."',
                        author = '".$this->author."', title = '".$this->title."', 
                        year = '".$this->year."', 
                        keywords = '".$this->OPTkey."', volume = '".$this->OPTvolume."', 
                        number = '".$this->OPTnumber."', pages = '".$this->OPTpages."',
                        publisher = '".$this->publisher."', editor = '".$this->editor."',
                        series = '".$this->OPTseries."', type = '".$this->OPTtype."',
                        chapter = '".$this->chapter."', address = '".$this->OPTaddress."',
                        month = '".$this->OPTmonth."', note = '".$this->OPTnote."', 
                        annote = '".$this->OPTannote."', edition = '".$this->OPTedition."',
                        resource_id = '".$this->resource_id."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Reference wide save things
        Reference::save($this); 

    }

    function load_post(){

        $data = $_POST;
        $data["resource_id"] = $data["book_select"];
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_inbook", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_inbook") > 0){
            $data = DB::db_get_array("load_inbook");
            $this->load_array($data);

            //get values from assocated resource
            $ares = new Book;
            if($ares->load_db($this->resource_id))
                $this->load_resource($ares); 

            return TRUE;
        } else {
            error("No book reference in database with that id\n");
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
        $this->year = $array["year"];
        $this->editor = $array["editor"];
        $this->publisher = $array["publisher"];
        $this->chapter = $array["chapter"];
        $this->OPTseries = $array["series"];
        $this->OPTtype = $array["type"];
        $this->OPTaddress = $array["address"];
        $this->OPTkey = $array["keywords"];
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTpages = $array["pages"];
        $this->OPTmonth = $array["month"];
        $this->OPTedition = $array["edition"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_id = $array["resource_id"];
        $this->loaded = TRUE;

    }

    
    function load_resource($ares){

        if($ares->is_valid()){
            
            $this->year = $ares->year;
            $this->author = $ares->author;
            $this->editor = $ares->editor;
            $this->publisher = $ares->publisher;
            $this->title = $ares->name;        
            $this->OPTvolume = $ares->OPTvolume;
            $this->OPTnumber = $ares->OPTnumber;
            $this->OPTseries = $ares->OPTseries;
            $this->OPTtype = $ares->OPTtype;
            $this->OPTaddress = $ares->OPTaddress;        
            $this->OPTedition = $ares->OPTedition;
            $this->OPTmonth = $ares->OPTmonth;
            
        }

    }

}

?>
