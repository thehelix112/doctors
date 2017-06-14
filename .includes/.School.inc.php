<?php
/*
File: .School.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the School class. This class is responsible for school resources.
	
*/

class School extends Resource {

    /* Data */

    var $OPTaddress;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            School::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            School::edit();
        } else if($action == ACTION_VIEW){
            School::view();
        } else if($action == ACTION_SAVE){
            $ares = new School;
            $ares->load_post(); 
            $ares->save();
            Resource::saved();
            School::edit();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                School::remove();
                unset($_SESSION["confirm"]);
            } else {
                School::view_remove();
            }

        }
                
    }

    function view_remove(){
        $ares = new School;
        $result = $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.SchoolDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_school", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_school") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.SchoolDeleted.html");
        }
    }

    function search(){

        //run the search string on the school schools
        DB::db_query("search_school", "SELECT * FROM resource WHERE resource_type='School'");
        if(DB::db_check_result("search_school") > 0){
            $data = DB::db_get_array("search_school");
            $this->load_array($data);
        }
         

    }



    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $ares = new School;
        $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTop.html");

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
        while(list($name1, $value1) = each($mains)){
            list($name2, $value2) = each($mains);
            include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewMainItem.html");
        }

        $this->display_category_links();

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewBottom.html");
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewActions.html");

    }

    function edit(){

                //allow ppl to change the type of the resource.
                Resource::select_type();

                //load the school in case we're editing
                $ares = new School;
                if(isset($_SESSION["object_id"])){
                    $result = $ares->load_db($_SESSION["object_id"]);
                }
                //$this->OPTmonth = $ares->OPTmonth;                
                $this = $ares;                

                //draw the constant bits at the top 
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

                //draw the School specifics
                include($GLOBALS["draw_includes_path"]."/Resource/.SchoolEdit.html");

                //draw the constant bits at the bottom
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.SchoolOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){
            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, address) VALUES 
                        ('".$this->name."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->OPTaddress."');
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
                        address = '".$this->OPTaddress."'
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

        DB::db_query("load_school", "SELECT * FROM resource WHERE id='$resource_id';");
        if(DB::db_check_result("load_school") > 0){
            $data = DB::db_get_array("load_school");
            $this->load_array($data);
            return TRUE;
        } else {
            error("No school in database with that id\n");
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
        $this->OPTaddress = $array["address"];
        $this->resource_type = get_type_as_string(TYPE_SCHOOL);        
        $this->loaded = TRUE;

    }

}

?>
