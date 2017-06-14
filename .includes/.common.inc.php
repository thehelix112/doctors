<?php

//global variables to be accessible everywhere
$GLOBALS["base_path"] = "/var/www/localhost/htdocs/doctors/";
$GLOBALS["tmp_upload_path"] = "uploads/";
$GLOBALS["base_url"] = "/doctors/";
$GLOBALS["draw_includes_path"] = $GLOBALS["base_path"]."/.includes/draw";
$GLOBALS["stylesheet_path"] = $GLOBALS["base_path"]."/.includes/draw/style.css";
$GLOBALS["stylesheet_url"] = $GLOBALS["base_url"]."/.includes/draw/style.css";
$GLOBALS["database_name"] = "doctors";
$GLOBALS["database_username"] = "dave";
$GLOBALS["database_password"] = "fu(king";
$GLOBALS["max_name_length"] = "40";

//define tasks for the various objects
define("ACTION_ADD", 0);
define("ACTION_EDIT", 1);
define("ACTION_VIEW", 2);
define("ACTION_SAVE", 3);
define("ACTION_DELETE", 4);
define("ACTION_LOGIN", 5);
define("ACTION_MAIN", 6);
define("ACTION_SEARCH", 7);
define("ACTION_LOGOUT", 8);
define("ACTION_BROWSE", 9);
define("ACTION_EDIT_PASSWORD", 10);
define("ACTION_SAVE_PASSWORD", 11);
define("ACTION_IMPORT", 12);
define("ACTION_EXPORT", 13);

//define the types of objects
define("TYPE_USER", 0);
define("TYPE_GROUP", 1);
define("TYPE_REFERENCE", 2);
define("TYPE_DOCUMENT", 3);
define("TYPE_RESOURCE", 4);
define("TYPE_CATEGORY", 5);
define("TYPE_NULL", 6);
define("TYPE_ARTICLE", 7);
define("TYPE_INPROCEEDINGS", 8);
define("TYPE_INCOLLECTION", 9);
define("TYPE_INBOOK", 10);
//define("TYPE_PROCEEDINGS", 11);
//define("TYPE_BOOK", 12);
define("TYPE_BOOKLET", 13);
define("TYPE_THESIS", 14);
define("TYPE_TECHREPORT", 16);
define("TYPE_MANUAL", 17);
define("TYPE_UNPUBLISHED", 18);
define("TYPE_MISC", 19);
define("TYPE_JOURNAL", 20);
define("TYPE_CONFERENCE", 21);
define("TYPE_BOOK", 22);
define("TYPE_SCHOOL", 23);
define("TYPE_COMPANY", 24);
define("TYPE_ADMINISTRATION", 25);
define("TYPE_LINK", 26);
define("TYPE_DATA", 27);
define("TYPE_COLLECTION", 28);

//define the reference states
define("STATE_NOTATED", 0);
define("STATE_LINKED", 1);
define("STATE_CATEGORISED", 2);
define("STATE_HASDOCUMENT", 3);

//define links
define("LINK_REFERENCE_OF_USER", 0); //*
define("LINK_REFERENCE_OF_GROUP", 1);
define("LINK_REFERENCE_IN_CATEGORY", 2); //*
define("LINK_REFERENCE_CITES_REFERENCE", 4); //*
define("LINK_REFERENCE_FROM_RESOURCE", 5); //*
define("LINK_RESOURCE_IN_CATEGORY", 6); //*
define("LINK_CATEGORY_SUB_OF_CATEGORY", 7); //*
define("LINK_DOCUMENT_OF_REFERENCE", 8);
define("LINK_RESOURCE_OF_USER", 9); //*
define("LINK_RESOURCE_OF_GROUP", 10);
define("LINK_CATEGORY_OF_USER", 11); //*
define("LINK_CATEGORY_OF_GROUP", 12);


//define link properties
define("LINK_TO", 0);
define("LINK_FROM", 1);
define("LINK_SELF", 2);

//define any
define("ANY", -1);

//get the gather_info functions
include_once(".includes/common/.info.inc.php");

//the search functions
include_once(".includes/common/.search.inc.php");

//curteousy functions
include_once(".includes/common/.curt.inc.php");


//max number of levels in the menu
define("MENU_MAX_LEVELS", 8);

?>
