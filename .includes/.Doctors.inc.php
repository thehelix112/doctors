<?php
/*
File: .Doctors.inc.php
Author: David Andrews
Date Started: 27/09/2003
Synopsis:

This file defines the Doctors class. This class is the entry point
into the entire system.


*/

//includes for all the objects needed

include_once(".includes/.Menu.inc.php");
include_once(".includes/.Link.inc.php");
include_once(".includes/.Crumbs.inc.php");
include_once(".includes/.Category.inc.php");
include_once(".includes/.Document.inc.php");
include_once(".includes/.Reference.inc.php");
//=========================================
include_once(".includes/.Article.inc.php");
include_once(".includes/.InProceedings.inc.php");
include_once(".includes/.InCollection.inc.php");
include_once(".includes/.InBook.inc.php");
include_once(".includes/.Booklet.inc.php");
include_once(".includes/.Thesis.inc.php");
include_once(".includes/.TechReport.inc.php");
include_once(".includes/.Manual.inc.php");
include_once(".includes/.Unpublished.inc.php");
include_once(".includes/.Misc.inc.php");


include_once(".includes/.Resource.inc.php");
//=========================================
include_once(".includes/.Journal.inc.php");
include_once(".includes/.Conference.inc.php");
include_once(".includes/.Collection.inc.php");
include_once(".includes/.Book.inc.php");
include_once(".includes/.School.inc.php");
include_once(".includes/.Company.inc.php");

include_once(".includes/.User.inc.php");
include_once(".includes/.Group.inc.php");
include_once(".includes/.Data.inc.php");

class Doctors {

    /* functions:
        authenticate
        login */

    function authenticate(){
        include_once(".includes/Doctors/.authenticate.inc.php");
    }

    function access_control(){
        include_once(".includes/Doctors/.access_control.inc.php");
    }    

    function login(){
        include_once(".includes/Doctors/.login.inc.php");
    }

    function logout(){
        include_once(".includes/Doctors/.logout.inc.php");
    }

    function draw_top(){
        include_once(".includes/Doctors/.top.inc.php");
    }

    function draw_bottom(){
        include_once(".includes/Doctors/.bottom.inc.php");
    }

    function draw_overall(){
        include_once(".includes/Doctors/.overall.inc.php");
    }

    function draw_heading(){
        include_once(".includes/Doctors/.heading.inc.php");
    }

    function draw_main(){
        include(".includes/Doctors/.main.inc.php");
    }


    function main(){

        switch($_SESSION["action"]){

            case ACTION_LOGOUT:
                Doctors::logout();  //log the user out
                Doctors::login();   //let them log back in
                break;

            default:                
                Doctors::draw_overall();
                break;
        }

    }

}

?>
