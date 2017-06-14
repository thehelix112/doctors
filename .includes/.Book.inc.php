<?php
/*
File: .Book.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the Book class. This class is responsible for collection resources.
	
*/

class Book extends Resource {

    /* Data */


    var $year;
    var $author;
    var $editor;
    var $publisher;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTseries;
    var $OPTtype;
    var $OPTaddress;        
    var $OPTedition;
    var $OPTmonth;
    var $OPTnote;
    var $OPTannote;
    var $OPTkeyword;


    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Book::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Book::edit();
        } else if($action == ACTION_VIEW){
            Book::view();
        } else if($action == ACTION_SAVE){
            $ares = new Book;
            $ares->load_post(); 
            $ares->save();
            Resource::saved();
            Book::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Book::remove();
                unset($_SESSION["confirm"]);
            } else {
                Book::view_remove();
            }

        }
                
    }

    function export(){

        //generate reference id
        $author = $this->author;
        $title = $this->name;
        
        $array = explode(" ", $author);
        for($i = 0; $i < count($array); $i++){
            if(strlen($array[$i]) > 1 && substr($array[$i], -1, 1) != "."){
                $firstauthorsurname = strtolower($array[$i]);                
                break;
            }                    
        }

        $name = "";
        $array = explode(" ", $title);
        foreach($array as $portion){                
            if(strlen($portion) > 1)
                $name .= strtolower($portion);
        }
        

        $startstr = "@Book{".$firstauthorsurname.$this->year.$name.",\n";

        $expstr .= "\ttitle = {".$this->name."},\n";
        $expstr .= "\tpublisher = {".$this->publisher."},\n";
        $expstr .= "\tyear = {".$this->year."},\n";
        if($this->author != "") $expstr .= "\tauthor = {".$this->author."},\n";
        if($this->editor != "") $expstr .= "\teditor = {".$this->editor."},\n";
        if($this->OPTkeyword != "") $expstr .= "\tkey = {".$this->OPTkeyword."},\n";
        if($this->OPTvolume != "") $expstr .= "\tvolume = {".$this->OPTvolume."},\n";
        if($this->OPTnumber != "") $expstr .= "\tnumber = {".$this->OPTnumber."},\n";
        if($this->OPTseries != "") $expstr .= "\tseries = {".$this->OPTseries."},\n";
        if($this->OPTtype != "") $expstr .= "\ttype = {".$this->OPTtype."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";
        if($this->OPTedition != "") $expstr .= "\tedition = {".$this->OPTedition."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }

    function view_remove(){
        $ares = new Book;
        $result = $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.BookDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_collection", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_collection") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.BookDeleted.html");
        }
    }

    function search(){

        //run the search string on the collection journals
        DB::db_query("search_collection", "SELECT * FROM resource WHERE resource_type='Book'");
        if(DB::db_check_result("search_collection") > 0){
            $data = DB::db_get_array("search_collection");
            $this->load_array($data);
        }
         

    }



    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $ares = new Book;
        $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTop.html");


        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->author)) $mains["Author:"] = $this->author;
        if(valid($this->editor)) $mains["Editor:"] = $this->editor;
        if(valid($this->title)) $mains["Title:"] = $this->title;
        if(valid($this->year)) $mains["Year:"] = $this->year;
        if(valid($this->publisher)) $mains["Publisher:"] = $this->publisher;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
        if(valid($this->OPTseries)) $mains["Series:"] = $this->OPTseries;
        if(valid($this->OPTtype)) $mains["Type:"] = $this->OPTtype;
        if(valid($this->OPTkeyword)) $mains["Keywords:"] = $this->OPTkeyword;
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
        if(valid($this->OPTedition)) $mains["Edition:"] = $this->OPTedition;
        if(valid($this->OPTnote)) $mains["Note:"] = $this->OPTnote;
        if(valid($this->OPTannote)) $mains["Annote:"] = $this->OPTannote;
        while(list($name1, $value1) = each($mains)){
            list($name2, $value2) = each($mains);
            include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewMainItem.html");
        }

        $this->display_category_links();
        $this->display_reference_links();

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewBottom.html");
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewActions.html");

    }

    function edit(){

                //allow ppl to change the type of the resource.
                Resource::select_type();

                //load the collection in case we're editing
                $ares = new Book;
                if(isset($_SESSION["object_id"])){
                    $result = $ares->load_db($_SESSION["object_id"]);
                }
                //$this->OPTmonth = $ares->OPTmonth;                
                $this = $ares;                

                //draw the constant bits at the top 
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

                //draw the Book specifics
                include($GLOBALS["draw_includes_path"]."/Resource/.BookEdit.html");

                //draw the constant bits at the bottom
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.BookOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){

            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, year, 
                        author, editor,
                        publisher, volume,
                        number, month,
                        series, type,
                        address, edition,
                        note, annote,
                        keywords) VALUES 
                        ('".$this->name."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->year."', 
                        '".$this->author."', '".$this->editor."', 
                        '".$this->publisher."', '".$this->OPTvolume."', 
                        '".$this->OPTnumber."', '".$this->OPTmonth."',
                        '".$this->OPTseries."', '".$this->OPTtype."',
                        '".$this->OPTaddress."', '".$this->OPTedition."',
                        '".$this->OPTnote."', '".$this->OPTannote."',
                        '".$this->OPTkeywords."');
                    ");

        } else {

            DB::db_query("save_resource", "UPDATE resource SET
                        name = '".$this->name."',
                        description = '".$this->description."',
                        urlmain = '".$this->urlmain."',
                        watch = '".$this->watch."',
                        watchurl = '".$this->watchurl."',
                        watchkeys = '".$this->watchkeys."',
                        resource_type = '".$this->resource_type."',
                        publisher = '".$this->publisher."', editor = '".$this->editor."',
                        author = '".$this->author."',
                        year = '".$this->year."', month = '".$this->OPTmonth."',
                        volume = '".$this->OPTvolume."', number = '".$this->OPTnumber."',
                        series = '".$this->OPTseries."', type = '".$this->OPTtype."',
                        address = '".$this->OPTaddress."', edition = '".$this->OPTedition."',
                        note = '".$this->OPTnote."', annote = '".$this->OPTannote."',
                        keywords = '".$this->OPTkeyword."'
                        WHERE id = '".$this->id."';
                    ");
        }

        //do Resource wide save things
        Resource::save($this); 
    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);

    }

    function load_db($resource_id){

        DB::db_query("load_collection", "SELECT * FROM resource WHERE id='$resource_id';");
        if(DB::db_check_result("load_collection") > 0){
            $data = DB::db_get_array("load_collection");
            $this->load_array($data);
            return TRUE;            
        } else {
            error("No collection in database with that id\n");
            return FALSE;
        }

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->name = $array["name"];
        $this->description = $array["description"];
        $this->urlmain = $array["urlmain"];
        $this->watch = $array["watch"];
        $this->watchurl = $array["watchurl"];
        $this->watchkeys = $array["watchkeys"];
        $this->year = $array["year"];
        $this->publisher = $array["publisher"];
        $this->editor = $array["editor"];
        $this->author = $array["author"];
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTmonth = $array["month"];
        $this->OPTseries = $array["series"];
        $this->OPTtype = $array["type"];
        $this->OPTaddress = $array["address"];
        $this->OPTedition = $array["edition"];
        $this->OPTkeyword = $array["keyword"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_type = get_type_as_string(TYPE_BOOK);        
        $this->loaded = TRUE;

    }

}

?>
