<?php
/*
File: .Unpublished.inc.php
Author: David Andrews
Date Started: 24/09/2003
Synopsis:

    This file defines the Unpublished class. This class is responsible for unpublisheds references.
	
*/

class Unpublished extends Reference {

    /* Data */

    var $author;
    var $title;    
    var $note;
    var $OPTkeyword;
    var $OPTmonth;
    var $OPTyear;
    var $OPTannote;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Unpublished::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Unpublished::edit();
        } else if($action == ACTION_VIEW){
            Unpublished::view();
        } else if($action == ACTION_SAVE){
            $aref = new Unpublished;
            $aref->load_post(); 
            $aref->save();
            Reference::saved();
            Unpublished::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Unpublished::remove();
                unset($_SESSION["confirm"]);
            } else {
                Unpublished::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@Unpublished{".$this->reference_id.",\n";
        
        $expstr = "\tauthor = {".$this->author."},\n";
        $expstr = "\ttitle = {".$this->title."},\n";
        $expstr = "\tnote = {".$this->note."},\n";
        if($this->OPTkeyword != "") $expstr .= "\tkey = {".$this->OPTkeyword."},\n";
        if($this->OPTyear != "") $expstr .= "\tyear = {".$this->OPTyear."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }

    function view_remove(){
        $aref = new Unpublished;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        include($GLOBALS["draw_includes_path"]."/Reference/.UnpublishedDelete.html");

    }

    function remove(){

        //do reference wide delete
        Reference::remove($_SESSION["object_id"]);
        
        DB::db_query("delete_unpublished", "DELETE FROM reference WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_unpublished") > 0){
            include($GLOBALS["draw_includes_path"]."/Reference/.UnpublishedDeleted.html");
        }
    }

    function search(){

        //run the search string on the journal unpublisheds
        DB::db_query("search_unpublished", "SELECT * FROM reference WHERE reference_type='Unpublished'");
        if(DB::db_check_result("search_unpublished") > 0){
            $data = DB::db_get_array("search_unpublished");
            $this->load_array($data);
        }
         

    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $aref = new Unpublished;
        $aref->load_db($_SESSION["object_id"]);
        $this = $aref; 

        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTop.html");

        //for all the required values (author, journal, year)
        $tops = array("Title:" => $this->title, 
                      "Author:" => $this->author,
                      "Note: " => $this->note);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceViewTopItem.html");
        }

        //for all the optional values (key, address...)
        $mains = array();
        if(valid($this->OPTkeyword)) $mains["Keyword:"] = $this->OPTkeyword;
        if(valid($this->OPTyear)) $mains["Year:"] = $this->OPTyear;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
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
        
        //load the unpublished in case we're editing
        $aref = new Unpublished;
        if(isset($_SESSION["object_id"])){
            $result = $aref->load_db($_SESSION["object_id"]);
        }        
        
        //MonthEdit.html is hardcoded to look at $this.
        $this->OPTmonth = $aref->OPTmonth;
        
        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditTop.html");
        
        //draw the Unpublished specifics
        include($GLOBALS["draw_includes_path"]."/Reference/.UnpublishedEdit.html");
        
        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Reference/.ReferenceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Reference/.UnpublishedOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){         

            DB::db_query("save_unpublished", "INSERT INTO reference 
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
                        month,
                        note, 
                        annote) VALUES 
                        ('".$this->title."', 
                        '".$this->reference_type."', 
                        '".$this->reference_id."',
                        '".$this->description."', 
                        '".$this->abstract."', 
                        '".$this->content."',
                        '".$this->weblink."', 
                        '".$this->author."',
                        '".$this->OPTkeyword."', 
                        '".$this->OPTyear."', 
                        '".$this->OPTmonth."', 
                        '".$this->note."', 
                        '".$this->OPTannote."')
                    ");
        } else {           

            DB::db_query("save_unpublished", "UPDATE reference SET
                        title = '".$this->title."',
                        reference_id = '".$this->reference_id."',
                        reference_type = '".$this->reference_type."',
                        description = '".$this->description."',
                        abstract = '".$this->abstract."',
                        content = '".$this->content."',
                        weblink = '".$this->weblink."',
                        author = '".$this->author."', 
                        keywords = '".$this->OPTkeyword."',
                        year = '".$this->OPTyear."', 
                        month = '".$this->OPTmonth."', 
                        note = '".$this->note."', 
                        annote = '".$this->OPTannote."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Reference wide save things
        Reference::save($this);        

    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);

    }

    function load_db($reference_id){

        DB::db_query("load_unpublished", "SELECT * FROM reference WHERE id='$reference_id';");
        if(DB::db_check_result("load_unpublished") > 0){
            $data = DB::db_get_array("load_unpublished");
            $this->load_array($data);

            return TRUE;            
        } else {
            error("No unpublished references in database with that id\n");
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
        $this->OPTyear = $array["year"];
        $this->OPTkeyword = $array["keywords"];
        $this->OPTmonth = $array["month"];
        $this->note = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->loaded = TRUE;

    }

   

}

?>
