<?php 

//start the session if its not already
session_start();

include_once(".includes/.Doctors.inc.php");
include_once(".includes/.Database.inc.php");
include_once(".includes/.admin.inc.php");
include_once(".includes/.common.inc.php");

//draw all boring inital html stuffs
Doctors::draw_top("Doctors");

//gather the information about what to do from POST or GET methods
gather_run_info();

//first do authentication
Doctors::authenticate();

if($_SESSION["authenticated"]){

    //lets get going
	Doctors::main();

}

//draw all boring closing html stuffs
Doctors::draw_bottom();

?>

