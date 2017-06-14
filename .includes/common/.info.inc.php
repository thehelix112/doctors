<?php

function gather_run_info(){

    /* check action/object specific authentication */
    get_action_info();
    get_type_info();
    if(isset($_POST["object_id"])) $_SESSION["object_id"] = $_POST["object_id"];
    if(isset($_GET["object_id"])) $_SESSION["object_id"] = $_GET["object_id"];
    if(isset($_POST["confirm"])) $_SESSION["confirm"] = $_POST["confirm"];
    if(isset($_GET["confirm"])) $_SESSION["confirm"] = $_GET["confirm"];

}

function get_type_info(){

    //go through all the different possible ACTION_'s and see which one is set. grrr

    if(isset($_POST["object_type"])) $_SESSION["object_type"] = 
        get_type_as_constant($_POST["object_type"]);
    if(isset($_GET["object_type"])) $_SESSION["object_type"] = 
        get_type_as_constant($_GET["object_type"]);


}

function get_type_as_string($type){

    if($type == TYPE_USER) return "User"; 
    if($type == TYPE_GROUP) return "Group";
    if($type == TYPE_REFERENCE) return "Reference";
    if($type == TYPE_DOCUMENT) return "Document";
    if($type == TYPE_RESOURCE) return "Resource";
    if($type == TYPE_CATEGORY) return "Category";
    if($type == TYPE_NULL) return "Null";
    if($type == TYPE_ARTICLE) return "Article";
    if($type == TYPE_INPROCEEDINGS) return "InProceedings";
    if($type == TYPE_INCOLLECTION) return "InCollection";
    if($type == TYPE_INBOOK) return "InBook";
    //if($type == TYPE_PROCEEDINGS) return "Proceedings";
    if($type == TYPE_BOOKLET) return "Booklet";
    if($type == TYPE_THESIS) return "Thesis";
    //if($type == TYPE_MASTERSTHESIS) return "MastersThesis";
    if($type == TYPE_TECHREPORT) return "TechReport";
    if($type == TYPE_MANUAL) return "Manual";
    if($type == TYPE_UNPUBLISHED) return "Unpublished";
    if($type == TYPE_MISC) return "Misc";
    if($type == TYPE_JOURNAL) return "Journal";
    if($type == TYPE_CONFERENCE) return "Conference";
    if($type == TYPE_COLLECTION) return "Collection";
    if($type == TYPE_BOOK) return "Book";
    if($type == TYPE_SCHOOL) return "School";
    if($type == TYPE_COMPANY) return "Company";
    if($type == TYPE_ADMINISTRATION) return "Administration";
    if($type == TYPE_LINK) return "Link";
    if($type == TYPE_DATA) return "Data";

}

function get_type_as_constant($type){

    if($type == "User") return TYPE_USER; 
    if($type == "Group") return TYPE_GROUP; 
    if($type == "Reference") return TYPE_REFERENCE; 
    if($type == "Document") return TYPE_DOCUMENT; 
    if($type == "Resource") return TYPE_RESOURCE; 
    if($type == "Category") return TYPE_CATEGORY; 
    if($type == "Null") return TYPE_NULL; 
    if($type == "Article") return TYPE_ARTICLE; 
    if($type == "InProceedings") return TYPE_INPROCEEDINGS; 
    if($type == "InCollection") return TYPE_INCOLLECTION; 
    if($type == "InBook") return TYPE_INBOOK; 
    //if($type == "Proceedings") return TYPE_PROCEEDINGS;
    if($type == "Booklet") return TYPE_BOOKLET; 
    if($type == "Thesis") return TYPE_THESIS;
    if($type == "TechReport") return TYPE_TECHREPORT; 
    if($type == "Manual") return TYPE_MANUAL; 
    if($type == "Unpublished") return TYPE_UNPUBLISHED; 
    if($type == "Misc") return TYPE_MISC; 
    if($type == "Journal") return TYPE_JOURNAL;
    if($type == "Conference") return TYPE_CONFERENCE;
    if($type == "Collection") return TYPE_COLLECTION;
    if($type == "Book") return TYPE_BOOK;
    if($type == "School") return TYPE_SCHOOL;
    if($type == "Company") return TYPE_COMPANY;
    if($type == "Administration") return TYPE_ADMINISTRATION;
    if($type == "Link") return TYPE_LINK;
    if($type == "Data") return TYPE_DATA;

}

function get_action_info(){

    //go through all the different possible ACTION_'s and see which one is set. grrr

    if(isset($_POST["action"])) $_SESSION["action"] = get_action_as_constant($_POST["action"]);
    if(isset($_GET["action"])) $_SESSION["action"] = get_action_as_constant($_GET["action"]);


}

function get_action_as_constant($action){

    if($action == "Login") return ACTION_LOGIN; 
    if($action == "Add") return ACTION_ADD; 
    if($action == "Edit") return ACTION_EDIT; 
    if($action == "Edit Password") return ACTION_EDIT_PASSWORD; 
    if($action == "View") return ACTION_VIEW; 
    if($action == "Save") return ACTION_SAVE; 
    if($action == "Save Password") return ACTION_SAVE_PASSWORD; 
    if($action == "Delete") return ACTION_DELETE; 
    if($action == "Main") return ACTION_MAIN; 
    if($action == "Search") return ACTION_SEARCH; 
    if($action == "Logout") return ACTION_LOGOUT; 
    if($action == "Browse") return ACTION_BROWSE; 
    if($action == "Import") return ACTION_IMPORT; 
    if($action == "Export") return ACTION_EXPORT; 

}

function get_action_as_string($action){

    if($action == ACTION_LOGIN) return "Login"; 
    if($action == ACTION_ADD) return "Add"; 
    if($action == ACTION_EDIT) return "Edit"; 
    if($action == ACTION_VIEW) return "View"; 
    if($action == ACTION_SAVE) return "Save"; 
    if($action == ACTION_DELETE) return "Delete"; 
    if($action == ACTION_MAIN) return "Main"; 
    if($action == ACTION_SEARCH) return "Search"; 
    if($action == ACTION_LOGOUT) return "Logout"; 
    if($action == ACTION_BROWSE) return "Browse"; 
    if($action == ACTION_EDIT_PASSWORD) return "Edit Password"; 
    if($action == ACTION_SAVE_PASSWORD) return "Save Password"; 
    if($action == ACTION_IMPORT) return "Import"; 
    if($action == ACTION_EXPORT) return "Export"; 

}

function get_state_as_string($state){

    if($state == STATE_NOTATED) return "Notated";
    if($state == STATE_LINKED) return "Linked";
    if($state == STATE_CATEGORISED) return "Categorised";
    if($state == STATE_HASDOCUMENT) return "HasDocument";

}

function get_state_as_constant($state){

    if($state == "Notated") return STATE_NOTATED;
    if($state == "Linked") return STATE_LINKED;
    if($state == "Categorised") return STATE_CATEGORISED;
    if($state == "Has Document") return STATE_HASDOCUMENT;

}

function get_link_as_string($link){

    //these strings should be of the format: 
    //<From Type><space><single word describing relationship><space><To Type>

    if($link == LINK_REFERENCE_OF_USER) return "Reference owner User"; 
    if($link == LINK_REFERENCE_OF_GROUP) return "Reference owner Group"; 
    if($link == LINK_REFERENCE_IN_CATEGORY) return "Reference in Category"; 
    if($link == LINK_REFERENCE_CITES_REFERENCE) return "Reference cites Reference"; 
    if($link == LINK_REFERENCE_FROM_RESORCE) return "Reference from Resource";    
    if($link == LINK_RESOURCE_IN_CATEGORY) return "Resource in Category";
    if($link == LINK_RESOURCE_OF_USER) return "Resource owner User";    
    if($link == LINK_RESOURCE_OF_GROUP) return "Resource owner Group";    
    if($link == LINK_CATEGORY_OF_USER) return "Category owner User";    
    if($link == LINK_CATEGORY_OF_GROUP) return "Category owner Group";    

}

function get_link_as_constant($state){

    if($state == "REFERENCE_OF_USER") return LINK_REFERENCE_OF_USER;
    if($state == "REFERENCE_OF_GROUP") return LINK_REFERENCE_OF_GROUP;
    if($state == "REFERENCE_IN_CATEGORY") return LINK_REFERENCE_IN_CATEGORY;
    if($state == "REFERENCE_CITED_BY_REFERENCE") return LINK_REFERENCE_CITES_REFERENCE;
    if($state == "REFERENCE_FROM_RESOURCE") return LINK_REFERENCE_FROM_RESOURCE;
    if($state == "RESOURCE_IN_CATEGORY") return LINK_RESOURCE_IN_CATEGORY;
    if($state == "RESOURCE_OF_USER") return LINK_RESOURCE_OF_USER;    
    if($state == "RESOURCE_OF_GROUP") return LINK_RESOURCE_OF_GROUP;    
    if($state == "CATEGORY_OF_USER") return LINK_CATEGORY_OF_USER;    
    if($state == "CATEGORY_OF_GROUP") return LINK_CATEGORY_OF_GROUP;
}

function get_month_as_string($month){

    if($month == "jan") return "January";
    if($month == "feb") return "February";
    if($month == "mar") return "March";
    if($month == "apr") return "April";
    if($month == "may") return "May";
    if($month == "jun") return "June";
    if($month == "jul") return "July";
    if($month == "aug") return "August";
    if($month == "sep") return "September";
    if($month == "oct") return "October";
    if($month == "nov") return "November";
    if($month == "dec") return "December";

}

function get_month_as_abbreviation($month){

    if($month == "January") return "jan";
    if($month == "February") return "feb";
    if($month == "March") return "mar";
    if($month == "April") return "apr";
    if($month == "May") return "may";
    if($month == "June") return "jun";
    if($month == "July") return "jul";
    if($month == "August") return "aug";
    if($month == "September") return "sep";
    if($month == "October") return "oct";
    if($month == "November") return "nov";
    if($month == "December") return "dec";

}



?>
