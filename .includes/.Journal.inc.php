<?php
/*
File: .Journal.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the Journal class. This class is responsible for journal resources.
	
*/

class Journal extends Resource {

    /* Data */

    var $year;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTmonth;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Journal::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Journal::edit();
        } else if($action == ACTION_VIEW){
            Journal::view();
        } else if($action == ACTION_SAVE){
            $ares = new Journal;
            $ares->load_post(); 
            $ares->save();
            Resource::saved();
            Journal::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Journal::remove();
                unset($_SESSION["confirm"]);
            } else {
                Journal::view_remove();
            }

        }
                
    }

    function view_remove(){
        $ares = new Journal;
        $result = $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.JournalDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_journal", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_journal") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.JournalDeleted.html");
        }
    }

    function search(){

        //run the search string on the journal journals
        DB::db_query("search_journal", "SELECT * FROM resource WHERE resource_type='Journal'");
        if(DB::db_check_result("search_journal") > 0){
            $data = DB::db_get_array("search_journal");
            $this->load_array($data);
        }
         

    }



    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $ares = new Journal;
        $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTop.html");

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->year)) $mains["Year:"] = $this->year;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
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

                //load the journal in case we're editing
                $ares = new Journal;
                if(isset($_SESSION["object_id"])){
                    $result = $ares->load_db($_SESSION["object_id"]);
                }
                //$this->OPTmonth = $ares->OPTmonth;                
                $this = $ares;                

                //draw the constant bits at the top 
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

                //draw the Journal specifics
                include($GLOBALS["draw_includes_path"]."/Resource/.JournalEdit.html");

                //draw the constant bits at the bottom
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.JournalOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){
            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, year, volume, 
                        number, month) VALUES 
                        ('".$this->name."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->year."', '".$this->OPTvolume."',
                        '".$this->OPTnumber."', '".$this->OPTmonth."');
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
                        year = '".$this->year."', month = '".$this->OPTmonth."',
                        volume = '".$this->OPTvolume."', number = '".$this->OPTnumber."'
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

        DB::db_query("load_journal", "SELECT * FROM resource WHERE id='$resource_id';");
        if(DB::db_check_result("load_journal") > 0){
            $data = DB::db_get_array("load_journal");
            $this->load_array($data);
            return TRUE;
        } else {
            error("No journal in database with that id\n");
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
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTmonth = $array["month"];
        $this->resource_type = get_type_as_string(TYPE_JOURNAL);        
        $this->loaded = TRUE;

    }

}

?>
