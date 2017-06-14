<?php
/*
File: .Conference.inc.php
Author: David Andrews
Date Started: 28/09/2003
Synopsis:

    This file defines the Conference class. This class is responsible for conference paper resources.
	
*/

class Conference extends Resource {

    /* Data */

    var $year;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTmonth;
    var $OPTaddress;
    var $OPTorganisation;
    var $OPTpublisher;
    var $OPTseries;
    var $OPTeditor;
    var $OPTkeyword;
    var $OPTnote;
    var $OPTannote;    

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Conference::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Conference::edit();
        } else if($action == ACTION_VIEW){
            Conference::view();
        } else if($action == ACTION_SAVE){
            $aref = new Conference;
            $aref->load_post(); 
            $aref->save();
            Resource::saved();
            Conference::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Conference::remove();
                unset($_SESSION["confirm"]);
            } else {
                Conference::view_remove();
            }

        }
                
    }

    function export(){

        $startstr = "@Proceedings{".export_name($this->name).",\n";

        $expstr .= "\ttitle = {".$this->name."},\n";
        $expstr .= "\tyear = {".$this->year."},\n";

        if($this->OPTpublisher != "") $expstr .= "\tpublisher = {".$this->OPTpublisher."},\n";
        if($this->OPTeditor != "") $expstr .= "\teditor = {".$this->OPTeditor."},\n";
        if($this->OPTkeyword != "") $expstr .= "\tkey = {".$this->OPTkeyword."},\n";
        if($this->OPTvolume != "") $expstr .= "\tvolume = {".$this->OPTvolume."},\n";
        if($this->OPTnumber != "") $expstr .= "\tnumber = {".$this->OPTnumber."},\n";
        if($this->OPTseries != "") $expstr .= "\tseries = {".$this->OPTseries."},\n";
        if($this->OPTtype != "") $expstr .= "\ttype = {".$this->OPTtype."},\n";
        if($this->OPTaddress != "") $expstr .= "\taddress = {".$this->OPTaddress."},\n";
        if($this->OPTorganisation != "") $expstr .= "\torganization = {".$this->OPTorganisation."},\n";
        if($this->OPTmonth != "") $expstr .= "\tmonth = {".get_month_as_string($this->OPTmonth)."},\n";
        if($this->OPTnote != "") $expstr .= "\tnote = {".$this->OPTnote."},\n";
        if($this->OPTannote != "" ) $expstr .= "\tannote = {".$this->OPTannote."}\n";
        $expstr .= "}";

        return $startstr.export_val($expstr);

    }

    function view_remove(){
        $aref = new Conference;
        $result = $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Resource/.ConferenceDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_conference", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_conference") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.ConferenceDeleted.html");
        }
    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"]

        $aref = new Conference;

        $aref->load_db($_SESSION["object_id"]);
        $this = $aref;

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTop.html");

        //for all the required values (author, journal, year)
        $tops = array("Year:" => $this->year);
        foreach($tops as $name => $value){
            include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTopItem.html");
        }

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTnumber)) $mains["Number:"] = $this->OPTnumber;
        if(valid($this->OPTpublisher)) $mains["Publisher:"] = $this->OPTpublisher;
        if(valid($this->OPTeditor)) $mains["Editor:"] = $this->OPTeditor;
        if(valid($this->OPTseries)) $mains["Series:"] = $this->OPTseries;
        if(valid($this->OPTkeyword)) $mains["Keywords:"] = $this->OPTkeyword;
        if(valid($this->OPTorganisation)) $mains["Organisation:"] = $this->OPTorganisation;
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
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

        //load the article in case we're editing
        if(isset($_SESSION["object_id"])){
            $ares = new Conference;
            $result = $ares->load_db($_SESSION["object_id"]);
        }

        $this = $ares;        

        //draw the constant bits at the top 
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

        //draw the JournalArticle specifics
        include($GLOBALS["draw_includes_path"]."/Resource/.ConferenceEdit.html");

        //draw the constant bits at the bottom
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }

    /* begin instance functions */

    function get_search_haystack(){

        return $this->author." ".$this->booktitle." ";

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.ConferenceOverview.html");

    }

    function save(){


        //write everything from the object to the database

        if(!isset($this->id)){
            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, year, volume, 
                        number, month,
                        publisher, editor, series,
                        address, organisation,
                        note, annote, keywords) VALUES 
                        ('".$this->name."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->year."', '".$this->OPTvolume."',
                        '".$this->OPTnumber."', '".$this->OPTmonth."',
                        '".$this->OPTpublisher."', '".$this->OPTeditor."', '".$this->OPTseries."',
                        '".$this->OPTaddress."', '".$this->OPTorganisation."',
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
                        year = '".$this->year."', month = '".$this->OPTmonth."',
                        volume = '".$this->OPTvolume."', number = '".$this->OPTnumber."',
                        publisher = '".$this->OPTpublisher."', editor = '".$this->OPTeditor."',
                        series = '".$this->OPTseries."', address = '".$this->OPTaddress."',
                        organisation = '".$this->OPTorganisation."',
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

        DB::db_query("load_conference", "SELECT * FROM resource WHERE id='$resource_id';");
        if(DB::db_check_result("load_conference") > 0){
            $data = DB::db_get_array("load_conference");
            $this->load_array($data);
            return TRUE;
        } else {
            error("No conference in database with that id\n");
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
        $this->OPTaddress = $array["address"];
        $this->OPTorganisation = $array["organisation"];
        $this->OPTpublisher = $array["publisher"];
        $this->OPTseries = $array["series"];
        $this->OPTeditor = $array["editor"];
        $this->OPTkeyword = $array["keyword"];
        $this->OPTnote = $array["note"];
        $this->OPTannote = $array["annote"];
        $this->resource_type = get_type_as_string(TYPE_CONFERENCE);        
        $this->loaded = TRUE;

    }


}

?>
