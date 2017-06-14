<?php
/*
File: .Article.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Article class. This class is responsible for journal articles references.
	
*/

class Article extends Reference {

    /* Data */

    var $author;
    var $OPTkey;
    var $OPTpages;
    var $OPTnote;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Article::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Article::edit();
        } else if($action == ACTION_VIEW){
            Article::view();
        } else if($action == ACTION_SAVE){
            $aref = new Article;
            $aref->load_post(); 
            $aref->save();
            Reference::saved();
            Article::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Article::remove();
                unset($_SESSION["confirm"]);
            } else {
                Article::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@Article{".$this->reference_id.",\n";
        
        $expstr = "\tauthor = {".$this->author."},\n";
        $expstr .= "\ttitle = {".$this->title."},\n";
        $expstr .= "\tjournal = {".$this->journal."},\n";
        $expstr .= "\tyear = {".$this->year."},\n";
        if($this->OPTkey != "") $expstr .= "\tkey = {".$this->OPTkey."},\n";
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
        $aref = new Article;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        include($GLOBALS["draw_includes_path"]."/Reference/.ArticleDelete.html");

    }

    function remove(){

        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);
        
        DB::db_query("delete_article", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_article") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.ArticleDeleted.html");
        }
    }

    function search(){

        //run the search string on the journal articles
        DB::db_query("search_article", "SELECT * FROM reference WHERE reference_type='Article'");
        if(DB::db_check_result("search_article") > 0){
            $data = DB::db_get_array("search_article");
            $this->load_array($data);
        }
         

    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $aref = new Article;
        $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        if($this->resource_id != "" && $this->resource_id != "-1"){
            //need to get the values: journal, year, month, volume, number
            $ares = new Journal;
            $ares->load_db($this->resource_id);
            $this->load_resource($ares);
        }

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, journal, year)
        $tops = array("Author:" => $this->author, 
                        "Journal:" => $this->journal, 
                        "Year:" => $this->year);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
        if(valid($this->OPTpages)) $mains["Pages:"] = $this->OPTpages;
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

    function get_journals() {        

        return Link::get_to_links(LINK_REFERENCE_FROM_RESOURCE);

    }
    

    function edit(){                

        $_SESSION["action"] = ACTION_EDIT;        

        //allow ppl to change the type of the reference.
        Reference::select_type();
        
        //load the article in case we're editing
        $aref = new Article;
        if(isset($_SESSION["object_id"])){
            $result = $aref->load_db($_SESSION["object_id"]);
        }                
        
        $ares = new Journal;
        //need to get the values: journal, year, month, volume, number
        if($aref->resource_id != "" && $aref->resource_id != "-1") {           
            $ares->load_db($aref->resource_id);            
            $aref->load_resource($ares);
        }

        $resources = Article::get_journals();
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
        
        //draw the Article specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.ArticleEdit.html");
        
        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return $this->author." ".$this->journal." ".$this->year." ";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.ArticleOverview.html");

    }

    function save(){

        //save a new Journal as required

        if($this->resource_id == "new"){
            
            //save the Journal to the db then re-set the $this->resource_id appropriately

            $ares = new Journal;

            //copy journal's name appropriately.
            $_POST["name"] = $_POST["journal"]; 

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
            DB::db_query("get_journal", "SELECT id FROM resource
                           WHERE name='".$ares->name."' AND
                           year='".$ares->year."' AND
                           month='".$ares->OPTmonth."' AND
                           volume='".$ares->OPTvolume."' AND
                           number='".$ares->OPTnumber."' AND
                           resource_type='".get_type_as_string(TYPE_JOURNAL)."';");
            
            if(DB::db_check_result("get_journal") > 0){
                $data = DB::db_get_array("get_journal");                
                $this->resource_id = $data["id"];
                $this->load_resource($ares);
            } else {
                error("Could not find the Journal we just added. Uh-oh.");                
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
                        volume,
                        number,
                        month,
                        journal,
                        pages,
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
                        '".$this->author."',
                        '".$this->OPTkey."', 
                        '".$this->year."', 
                        '".$this->OPTvolume."', 
                        '".$this->OPTnumber."', 
                        '".$this->OPTmonth."', 
                        '".$this->journal."', 
                        '".$this->OPTpages."',
                        '".$this->OPTnote."', 
                        '".$this->OPTannote."',
                        '".$this->resource_id."')
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
                        keywords = '".$this->OPTkey."',
                        year = '".$this->year."', 
                        volume = '".$this->OPTvolume."', 
                        number = '".$this->OPTnumber."', 
                        month = '".$this->OPTmonth."', 
                        journal = '".$this->journal."', 
                        pages = '".$this->OPTpages."',
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
        $data["resource_id"] = $data["journal_select"];
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_article", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_article") > 0){
            $data = DB::db_get_array("load_article");
            $this->load_array($data);

            //get values from assocated resource
            $ares = new Journal;
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
        $this->journal = $array["journal"];
        $this->year = $array["year"];
        $this->OPTkey = $array["keywords"];
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTpages = $array["pages"];
        $this->OPTmonth = $array["month"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_id = $array["resource_id"];
        $this->loaded = TRUE;

    }

    function load_resource($ares){

        if($ares->is_valid()){

            $this->journal = $ares->name;
            $this->year = $ares->year;
            $this->OPTvolume = $ares->OPTvolume;
            $this->OPTnumber = $ares->OPTnumber;
            $this->OPTmonth = $ares->OPTmonth;
            
        }
        

    }

   

}

?>
