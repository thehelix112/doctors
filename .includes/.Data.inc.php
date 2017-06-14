<?php

/*
File: .Data.inc.php
Author: David Andrews
Date Started: 16/02/2004
Synopsis:

    This file defines the Data class which is responsible
    for importing and exporting the referencing data.
	
*/

class Data {

    function main(){

        switch($_SESSION["action"]){

            case ACTION_IMPORT:
                include($GLOBALS["draw_includes_path"]."/Data/.DataImport.html");
                break;
                
            case ACTION_EXPORT:
                Data::export();
                break;

            case ACTION_SAVE:
                Data::import();
                break;

            default:
                include($GLOBALS["draw_includes_path"]."/Data/.Data.html");

        }

    }

    function import(){

        //import as much information as possible from a .bib file 
        $newfile = $GLOBALS['base_path'].$GLOBALS['tmp_upload_path']."/".$_FILES['import_path']['name'];

        if(move_uploaded_file($_FILES['import_path']['tmp_name'], $newfile)){

            //the file was uploaded successfully.
            //open the file and read its contents
            $fd = fopen($newfile, "r");
            $import_data = fread($fd, filesize($newfile));
            fclose($fd);

            //now need to load in the information from the text file.

            //trim the begin comments.
            $import_data = substr($import_data, strpos($import_data, "\n@"));
            $import_data = str_replace("'", "\'", $import_data);

            $references = explode("@", $import_data);

            //print_r($references);

            $counter = 0;

            //go through each entry
            foreach($references as $reference){

                $array = array();

                $lines = explode("\n", $reference);
                //print_r($lines);

                //get the type and the id from the first line
                //$type = substr($lines[0], 0, strpos($lines[0], "{"));
                //$reference_id = substr($lines[0], strpos($lines[0], "{")+1, -1);

                preg_match("/(.*){(.*),/", $lines[0], $matches);
                $type = $matches[1];
                $reference_id = $matches[2];
        
                $array["reference_id"] = $reference_id;
                $array["resource_id"] = "-1";

                //create the appropriate object
                switch($type){
                
                    case get_type_as_string(TYPE_ARTICLE):
                        $array["reference_type"] = get_type_as_string(TYPE_ARTICLE);
                        $ref = new Article;
                        break;

                    case get_type_as_string(TYPE_INPROCEEDINGS):
                        $array["reference_type"] = get_type_as_string(TYPE_INPROCEEDINGS);
                        $ref = new InProceedings;
                        break;

                    case get_type_as_string(TYPE_INCOLLECTION):
                        $array["reference_type"] = get_type_as_string(TYPE_INCOLLECTION);
                        $ref = new InCollection;
                        break;

                    case get_type_as_string(TYPE_INBOOK):
                        $array["reference_type"] = get_type_as_string(TYPE_INBOOK);
                        $ref = new InBook;
                        break;

                    case get_type_as_string(TYPE_PROCEEDINGS):
                        $array["reference_type"] = get_type_as_string(TYPE_PROCEEDINGS);
                        $ref = new Proceedings;
                        break;

                    case get_type_as_string(TYPE_BOOK):
                        $array["reference_type"] = get_type_as_string(TYPE_BOOK);
                        $ref = new Book;
                        break;

                    case get_type_as_string(TYPE_BOOKLET):
                        $array["reference_type"] = get_type_as_string(TYPE_BOOKLET);
                        $ref = new Booklet;
                        break;

                    case "Phd".get_type_as_string(TYPE_THESIS):
                        $array["reference_type"] = get_type_as_string(TYPE_THESIS);
                        $ref = new Thesis;
                        $array["thesis_type"] = "Phd";                        
                        break;

                    case "Masters".get_type_as_string(TYPE_THESIS):
                        $array["reference_type"] = get_type_as_string(TYPE_THESIS);
                        $ref = new Thesis;
                        $array["thesis_type"] = "Masters";                        
                        break;


                    case get_type_as_string(TYPE_TECHREPORT):
                        $array["reference_type"] = get_type_as_string(TYPE_TECHREPORT);
                        $ref = new TechReport;
                        break;

                    case get_type_as_string(TYPE_MANUAL):
                        $array["reference_type"] = get_type_as_string(TYPE_MANUAL);
                        $ref = new Manual;
                        break;

                    case get_type_as_string(TYPE_UNPUBLISHED):
                        $array["reference_type"] = get_type_as_string(TYPE_UNPUBLISHED);
                        $ref = new UnPublished;
                        break;

                    case get_type_as_string(TYPE_MISC):
                        $array["reference_type"] = get_type_as_string(TYPE_MISC);
                        $ref = new Misc;
                        break;

                    default:
                        $ref = NULL;

                }

                array_shift($lines);

                //create an array from the text
                foreach($lines as $line){

                    //extract the key
                    //$key = trim(substr($line,0, strpos($line, "=")));

                    //extract the value
                    //$value = substr($line, strpos($line,"{")+1, -2);
                    preg_match("/(.*)=\s*{(.*)}/", $line, $matches);

                    $key = trim($matches[1]);
                    $value = $matches[2];
    
                    //import the value (remove {}'s)
                    $value = import_val($value);

                    if(valid($key) && valid($value)){
                   
                        //IF its a resource then copy name to title
                        if(is_subclass_of($ref, "Resource")){
                            if($key == "title") $key = "name";                            
                        }
                        

                        if($key == "organization") $key = "organisation";
                        if($key == "key") $key = "keywords";
                        if($key == "month") $value = get_month_as_abbreviation($value);

                        $array[$key] = $value;

                    }


                }

                //print_r($array);

                //load it into the object
                if($ref != NULL){

                    $ref->load_array($array);
                    $ref->save();

                    $counter++;

                }

            }
            echo $counter." references added.\n";

        }

    }

    function export(){

        //export a working .bib file
        $expstr = "% begin bibliography <pre>\n";

        //get the users reference
        $reference_links = Link::get_links(ANY, $_SESSION["user_id"], LINK_REFERENCE_OF_USER);

        for($i = 0; $i < count($reference_links); $i++){

            DB::db_query("get", "SELECT * FROM reference WHERE id='".$reference_links[$i]["from_id"]."';");
            if(DB::db_check_result("get") > 0){

                $num = DB::db_num_rows("get");
                if($num != FALSE && $num > 0){
                    //for each reference write the appropriate .bib entry.
                    //  based on reference_type, load_db from the appropriate class
                    //  then call export() on that instance.
                    for($j = 0; $j < $num; $j++){
                        
                        $array = DB::db_get_array("get");
                        
                        switch($array['reference_type']){

                        case get_type_as_string(TYPE_ARTICLE):
                            $ref = new Article;
                        break;
                        
                        case get_type_as_string(TYPE_INPROCEEDINGS):
                        $ref = new InProceedings;
                        break;

                        case get_type_as_string(TYPE_INCOLLECTION):
                            $ref = new InCollection;
                            break;

                        case get_type_as_string(TYPE_INBOOK):
                            $ref = new InBook;
                            break;

                        case get_type_as_string(TYPE_BOOKLET):
                            $ref = new Booklet;
                            break;

                        case get_type_as_string(TYPE_THESIS):
                            $ref = new Thesis;
                            break;

                        case get_type_as_string(TYPE_TECHREPORT):
                            $ref = new TechReport;
                            break;

                        case get_type_as_string(TYPE_MANUAL):
                            $ref = new Manual;
                            break;

                        case get_type_as_string(TYPE_UNPUBLISHED):
                            $ref = new UnPublished;
                            break;

                        case get_type_as_string(TYPE_MISC):
                            $ref = new Misc;
                            break;

                        default:
                            $ref = NULL;

                        }

                        if($ref != NULL){
                            $ref->load_array($array);
                            $expstr .= $ref->export()."\n\n";
                        }

                    }

                }            
                
            }
            
            
        }
        
        //get the users reference
        $resource_links = Link::get_links(ANY, $_SESSION["user_id"], LINK_RESOURCE_OF_USER);

        for($i = 0; $i < count($resource_links); $i++){

            DB::db_query("get", "SELECT * FROM resource WHERE id='".$resource_links[$i]["from_id"]."';");
            if(DB::db_check_result("get") > 0){

                //we must now go through the resources and add the conferences as @Proceedings
                //and add the books as @Book
                //select all resources from the database for this user.
                $num = DB::db_num_rows("get");
                if($num != FALSE && $num > 0){

                    for($j = 0; $j < $num; $j++){

                        $array = DB::db_get_array("get");

                        switch($array['resource_type']){


                        case get_type_as_string(TYPE_BOOK):
                            $res = new Book;
                            break;
                    
                        case get_type_as_string(TYPE_CONFERENCE):
                            $res = new Conference;
                            break;

                        default:
                            $res = NULL;
                            break;
                
                    
                        }

                        if($res != NULL){
                            $res->load_array($array);
                            $expstr .= $res->export()."\n\n";
                        }
                
                    }
            
            
                }
                
            }
            
        }
        

                

        $expstr .= "% end bibliography </pre>";

        //write the expstr to a file then open a new window for the client to this file.        
        $fd = fopen($GLOBALS["base_path"].$GLOBALS["tmp_upload_path"]."export_".$_SESSION["username"].".bib", "w");
        fwrite($fd, $expstr, strlen($expstr));
        fclose($fd);

        //open a new window to this just written file
        echo "
<script language=\"javascript\">
<!--

window.open(\"".$GLOBALS["base_url"].$GLOBALS["tmp_upload_path"]."export_".$_SESSION["username"].".bib\");

//-->
</script>

References exported.<br/><br/>";

        echo "
If a new window did not open automatically click <b><a target=\"_new\" href=\"".$GLOBALS["base_url"].$GLOBALS["tmp_upload_path"]."export_".$_SESSION["username"].".bib\">here</a></b> to view your references.\n";
        

        //echo $expstr;

    }

}

?>
