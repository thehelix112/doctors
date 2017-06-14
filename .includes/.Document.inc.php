<?php
/*
File: .Document.inc.php
Author: David Andrews
Date Started: 04/10/2003
Synopsis:

    This file defines the Document class. This class is responsible for associating files with references.
	
*/

class Document {

    /* Data */

    var $name;
    var $description;
    var $filename;
    var $reference_id;

    /* Functionality */

    function main(){

        $action = $_SESSION["action"];

        if($action == ACTION_EDIT){
            Document::edit();
        } else if($action == ACTION_ADD){
            unset($_SESSION["object_id"]);
            Document::edit();
        } else if($action == ACTION_VIEW){
            Document::view();
        } else if($action == ACTION_SAVE){
            $adoc = new Document;
            $adoc->load_post(); 
            $adoc->save();
        } else if($action == ACTION_DELETE){
            if($_SESSION["confirm"] == "TRUE"){
                Document::remove();
                unset($_SESSION["confirm"]);
            } else {
                Document::view_remove();
            }

        }
                
    }

    function view_remove(){
        $adoc = new Document;
        $result = $adoc->load_db($_SESSION["object_id"]);
        $this = $adoc; 

        include($GLOBALS["draw_includes_path"]."/Document/.DocumentDelete.html");

    }

    function remove(){

        DB::db_query("delete_company", "DELETE FROM document WHERE id='".$_SESSION["object_id"]."'");
        if(DB::db_check_result("delete_company") > 0){
            include($GLOBALS["draw_includes_path"]."/Document/.DocumentDeleted.html");
        }

        //delete the links
        $data = Link::get_links($_SESSION["object_id"], ANY, LINK_DOCUMENT_OF_REFERENCE);
        Link::delete_links($data);

    }

    function search(){

//         //run the search string on the company companys
//         DB::db_query("search_company", "SELECT * FROM document WHERE document_type='Document'");
//         if(DB::db_check_result("search_company") > 0){
//             $data = DB::db_get_array("search_company");
//             $this->load_array($data);
//         }
         

    }

    function view(){

        //get the object from the database based on the _SESSION["object_id"] 

        $adoc = new Document;
        $adoc->load_db($_SESSION["object_id"]);
        //$this = $adoc; 

        include($GLOBALS["draw_includes_path"]."/Document/.DocumentView.html");

    }

    function edit(){
        
                $adoc = new Document;
                if(isset($_SESSION["object_id"])){
                    $result = $adoc->load_db($_SESSION["object_id"]);
                }

                Document::gather_document_info();                
                $adoc->reference_id = $_SESSION["reference_id"];

                //if the document's name is not set then set it to a default
                if($adoc->name == ""){
                    $aref = new Reference;
                    $aref->load_db($adoc->reference_id);
                    $adoc->name = $aref->title."'s Document";
                }

                //draw the Document specifics
                include($GLOBALS["draw_includes_path"]."/Document/.DocumentEdit.html");

    }


    /* begin instance functions */
    function get_search_haystack(){

        return "";        

    }

    function search_overview($bgcolour){

        include($GLOBALS["draw_includes_path"]."/Document/.DocumentOverview.html");

    }

    function save(){

        //write everything from the object to the database

        if(!isset($this->id)){

            //save to the upload directory
            $newfile = $GLOBALS['base_path'].$GLOBALS['tmp_upload_path']."/".$_FILES['document']['name'];
            
            if(move_uploaded_file($_FILES['document']['tmp_name'], $newfile)){
                
                //the file was uploaded successfully.

                DB::db_query("save_document", "INSERT INTO document 
                        (name, description, filename) VALUES 
                        ('".$this->name."', '".$this->description."', '".$_FILES['document']['name']."');
                    ");
                
            
            } else {
            
                //upload Failed
                error("File upload failed!");                
                
            }

            //lookup user id.
            DB::db_query("document_id_lookup", "SELECT id FROM document WHERE name = '".$this->name."'
                                                  AND filename = '".$_FILES['document']['name']."'
                                                  AND description = '".$this->description."';");
            if(DB::db_check_result("document_id_lookup") > 0){
                $doc_id = DB::db_get_field("document_id_lookup", "id");                
                if($doc_id == "") error("Bad Document id.");                
            }
            
            Document::gather_document_info();            

            //add Link appropriately
            $link = new Link;
            $link->from_id = $doc_id;            
            $link->to_id = $_SESSION["reference_id"];
            $link->type = LINK_DOCUMENT_OF_REFERENCE;
            $link->save();

            //redirect back to reference
            $ref = new reference;
            $ref->load_db($_SESSION["reference_id"]);
            redirect($_SESSION["reference_id"], get_type_as_constant($ref->reference_type), ACTION_VIEW);

        } else {

            DB::db_query("save_document", "UPDATE document SET
                        name = '".$this->name."',
                        description = '".$this->description."'
                        WHERE id = '".$this->id."';
                    ");
        }

    }

    function load_post(){

        $data = $_POST;
        $this->load_array($data);

    }

    function load_db($document_id){

        DB::db_query("load_company", "SELECT * FROM document WHERE id='$document_id';");
        if(DB::db_check_result("load_company") > 0){
            $data = DB::db_get_array("load_company");
            $this->load_array($data);
            return TRUE;
        } else {
            error("No company in database with that id\n");
            return FALSE;
        }

    }

    function load_array($array){

        $this->id = $array["id"];
        $this->name = $array["name"];
        $this->description = $array["description"];
        $this->filename = $array["filename"];
        $this->loaded = TRUE;

    }

    function gather_document_info(){

        if(isset($_POST["reference_id"])) $_SESSION["reference_id"] = $_POST["reference_id"];
        if(isset($_GET["reference_id"])) $_SESSION["reference_id"] = $_GET["reference_id"];

    }

}

?>
