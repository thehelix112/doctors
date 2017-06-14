<?php

//authenticate::authenticate
//check the username and password supplied

	//if someone hasn't yet logged in take them to the login page
	if(!isset($_SESSION["authenticated"]) && (!isset($_POST["username"]) || !isset($_POST["password"]))){
		Doctors::login();
		return FALSE;
	}

	//use the session variables to attempt to login to doctors
	if($_SESSION["action"] == ACTION_LOGIN){
       
       //necessary because TYPE_USER == object_type when its not set. 
       //TODO: Fix this.
       $_SESSION["object_type"] = -1;       
       
       $username = strtolower($_POST["username"]);	
       $password = $_POST["password"];	
       
       //see if the username is in the database
       
       if(!DB::db_query("userQuery", "SELECT * FROM \"user\" WHERE \"username\" = '".$username."';")){
           error("Incorrect login.");
           Doctors::login();
           return FALSE;
       }
       
       if(DB::db_num_rows("userQuery") < 1){
           error("Incorrect login.");
           Doctors::login();
           return FALSE;
       }        

       $dbpassword = DB::db_get_field("userQuery", "password");
       $dbadministrator = DB::db_get_field("userQuery", "administrator");
       
       if($dbadministrator == "true"){
           $_SESSION["administrator"] = TRUE;
       } else {
           $_SESSION["administrator"] = FALSE;
       }


       if(crypt($password, $dbpassword) != $dbpassword){
           error("Incorrect login.");
           Doctors::login();
           return FALSE;
       }
       
       $_SESSION["username"] = $username;       
       $_SESSION["user_id"] = DB::db_get_field("userQuery", "id");       
       $_SESSION["authenticated"] = TRUE;

       return TRUE;
       
	}

?>
