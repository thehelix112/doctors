<?php
/*
File: .Group.inc.php
Author: David Andrews
Date Started: 19/11/2003
Synopsis:

    This file defines the Group class. This class is responsible for groups of users.
	
*/

class Group {

    /* Data */

    var $year;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTmonth;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Group::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Group::edit();
        } else if($action == ACTION_VIEW){
            Group::view();
        } else if($action == ACTION_SAVE){
            $ares = new Group;
            $ares->load_post(); 
            $ares->save();
            Resource::saved();
            Group::edit();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Group::remove();
                unset($_SESSION["confirm"]);
            } else {
                Group::view_remove();
            }

        }
                
    }

    function view_remove(){
        $ares = new Group;
        $result = $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.GroupDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_group", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_group") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.GroupDeleted.html");
        }
    }

    function search(){

        //run the search string on the group groups
        DB::db_query("search_group", "SELECT * FROM resource WHERE resource_type='Group'");
        if(DB::db_check_result("search_group") > 0){
            $data = DB::db_get_array("search_group");
            $this->load_array($data);
        }
         

    }



    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $ares = new Group;
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

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewBottom.html");
        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewActions.html");

    }

    function edit(){

                //allow ppl to change the type of the resource.
                Resource::select_type();

                //load the group in case we're editing
                $ares = new Group;
                if(isset($_SESSION["object_id"])){
                    $result = $ares->load_db($_SESSION["object_id"]);
                }
                //$this->OPTmonth = $ares->OPTmonth;                
                $this = $ares;                

                //draw the constant bits at the top 
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

                //draw the Group specifics
                include($GLOBALS["draw_includes_path"]."/Resource/.GroupEdit.html");

                //draw the constant bits at the bottom
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.GroupOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){
            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, type, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, year, volume, 
                        number, month) VALUES 
                        ('".$this->name."', '".TYPE_GROUP."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->year."', '".$this->OPTvolume."',
                        '".$this->OPTnumber."', '".$this->OPTmonth."');
                    ");

        } else {

            DB::db_query("save_resource", "UPDATE resource SET
                        name = '".$this->name."',
                        type = '".TYPE_GROUP."',
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

        DB::db_query("load_group", "SELECT * FROM resource WHERE id='$resource_id';");
        if(DB::db_check_result("load_group") > 0){
            $data = DB::db_get_array("load_group");
            $this->load_array($data);
        } else {
            error("No group in database with that id\n");
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
        $this->resource_type = get_type_as_string(TYPE_GROUP);        
        $this->loaded = TRUE;

    }

}

?>
