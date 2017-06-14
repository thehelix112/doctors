<?php

//database include file contains functions to make our use of the database easier

class DB {

    //connect to the database
    function db_connect(){

        if(!isset($GLOBALS["database_connection"])){
            $GLOBALS["database_connection"] = pg_connect("dbname=".$GLOBALS["database_name"]." 
                                user=".$GLOBALS["database_username"]."
                                password=".$GLOBALS["database_password"]);
        }

    }

    function db_query($result_id, $sql){

        if(!isset($GLOBALS["database_connection"])){
            DB::db_connect();
        }

        $result = pg_query($GLOBALS["database_connection"], $sql);

        if($result == FALSE){
            error("`<i>$sql</i>' failed.");
            return FALSE;
        } else {
            $GLOBALS["database_result_".$result_id] = $result;
            return TRUE;
        }

    }



    function db_get_array($result_id, $row = NULL){

        if(!DB::db_check_result($result_id)){
            return FALSE;
        }

        return pg_fetch_array($GLOBALS["database_result_".$result_id], $row);

    }

    function db_last_id($result_id, $table){

        if(!DB::db_check_result($result_id)){
            return FALSE;
        }
        $last_oid = pg_last_oid($GLOBALS["database_result_".$result_id]);
        DB::db_query("blah", "SELECT id FROM \"".$table."\" WHERE oid='".$last_oid."';");
        $data = DB::db_get_array("blah");

        return $data["id"];

    }

    function db_affected_rows($result_id){

        if(!DB::db_check_result($result_id)){
            return FALSE;
        }

        return pg_affected_rows($GLOBALS["database_result_".$result_id]);

    }

    function db_num_rows($result_id){

        if(!DB::db_check_result($result_id)){
            return FALSE;
        }

        return pg_num_rows($GLOBALS["database_result_".$result_id]);

    }

    function db_get_field($result_id, $field){

        $array = DB::db_get_array($result_id, 0);

        if($array == FALSE){
            error("Result is not a row.");
            return FALSE;
        }        

        if(!isset($array[$field])){
            error("Invalid field.");
            return FALSE;
        }

        return $array[$field];

    }

    function db_check_result($result_id){

        if(!isset($GLOBALS["database_result_".$result_id])){
            error("Invalid database result.");
            return FALSE;
        } else {
            return TRUE;
        }

    }

    function db_clear_result($result_id){

        if(isset($GLOBALS["database_result_".$result_id])){
            unset($GLOBALS["database_result_".$result_id]);	
        }

    }

}

?>
