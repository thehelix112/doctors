<?php
/*
File: .Collection.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the Collection class. This class is responsible for collection resources.
	
*/

class Collection extends Resource {

    /* Data */

    var $OPTyear;
    var $OPTvolume;
    var $OPTnumber;
    var $OPTmonth;
    var $OPTpublisher;
    var $OPTeditor;
    var $OPTseries;
    var $OPTtype;
    var $OPTaddress;
    var $OPTedition;
  


    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Collection::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Collection::edit();
        } else if($action == ACTION_VIEW){
            Collection::view();
        } else if($action == ACTION_SAVE){
            $ares = new Collection;
            $ares->load_post(); 
            $ares->save();
            Resource::saved();
            Collection::view();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Collection::remove();
                unset($_SESSION["confirm"]);
            } else {
                Collection::view_remove();
            }

        }
                
    }

    function view_remove(){
        $ares = new Collection;
        $result = $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.CollectionDelete.html");

    }

    function remove(){

        //do reference wide delete
        Resource::remove($_SESSION["object_id"]);

        DB::db_query("delete_collection", "DELETE FROM resource WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_collection") > 0){
            include($GLOBALS["draw_includes_path"]."/Resource/.CollectionDeleted.html");
        }
    }

    function search(){

        //run the search string on the collection journals
        DB::db_query("search_collection", "SELECT * FROM resource WHERE resource_type='Collection'");
        if(DB::db_check_result("search_collection") > 0){
            $data = DB::db_get_array("search_collection");
            $this->load_array($data);
        }
         

    }



    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $ares = new Collection;
        $ares->load_db($_SESSION["object_id"]);
        $this = $ares; 

        include($GLOBALS["draw_includes_path"]."/Resource/.ResourceViewTop.html");

        //for all the optional values (key, volume...)
        $mains = array();
        if(valid($this->OPTyear)) $mains["Year:"] = $this->OPTyear;
        if(valid($this->OPTmonth)) $mains["Month:"] = get_month_as_string($this->OPTmonth);
        if(valid($this->OPTvolume)) $mains["Volume:"] = $this->OPTvolume;
        if(valid($this->OPTpublisher)) $mains["Publisher:"] = $this->OPTpublisher;
        if(valid($this->OPTeditor)) $mains["Editor:"] = $this->OPTeditor;
        if(valid($this->OPTseries)) $mains["Series:"] = $this->OPTseries;
        if(valid($this->OPTtype)) $mains["Type:"] = $this->OPTtype;
        if(valid($this->OPTaddress)) $mains["Address:"] = $this->OPTaddress;
        if(valid($this->OPTedition)) $mains["Edition:"] = $this->OPTedition;
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
                $ares = new Collection;
                if(isset($_SESSION["object_id"])){
                    $result = $ares->load_db($_SESSION["object_id"]);
                }
                //$this->OPTmonth = $ares->OPTmonth;                
                $this = $ares;                

                //draw the constant bits at the top 
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditTop.html");

                //draw the Collection specifics
                include($GLOBALS["draw_includes_path"]."/Resource/.CollectionEdit.html");

                //draw the constant bits at the bottom
                include($GLOBALS["draw_includes_path"]."/Resource/.ResourceEditBottom.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Resource/.CollectionOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){

            
            DB::db_query("save_resource", "INSERT INTO resource 
                        (name, description, urlmain,
                        watch, watchurl, watchkeys,
                        resource_type, year, volume, 
                        number, month,
                        publisher, editor,
                        series, type,
                        address, edition) VALUES 
                        ('".$this->name."', '".$this->description."', '".$this->urlmain."',
                        '".$this->watch."', '".$this->watchurl."', '".$this->watchkeys."',
                        '".$this->resource_type."', '".$this->OPTyear."', '".$this->OPTvolume."',
                        '".$this->OPTnumber."', '".$this->OPTmonth."',
                        '".$this->OPTpublisher."', '".$this->OPTeditor."',
                        '".$this->OPTseries."', '".$this->OPTtype."',
                        '".$this->OPTaddress."', '".$this->OPTedition."');
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
                        year = '".$this->OPTyear."', month = '".$this->OPTmonth."',
                        volume = '".$this->OPTvolume."', number = '".$this->OPTnumber."',
                        publisher = '".$this->OPTpublisher."', editor = '".$this->OPTeditor."',
                        series = '".$this->OPTseries."', type = '".$this->OPTtype."',
                        address = '".$this->OPTaddress."', edition = '".$this->OPTedition."'
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
        $this->OPTyear = $array["year"];
        $this->OPTvolume = $array["volume"];
        $this->OPTnumber = $array["number"];
        $this->OPTmonth = $array["month"];
        $this->OPTpublisher = $array["publisher"];
        $this->OPTeditor = $array["editor"];
        $this->OPTseries = $array["series"];
        $this->OPTtype = $array["type"];
        $this->OPTaddress = $array["address"];
        $this->OPTedition = $array["edition"];
        $this->resource_type = get_type_as_string(TYPE_COLLECTION);        
        $this->loaded = TRUE;

    }

}

?>
