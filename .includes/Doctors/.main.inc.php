<?php

//Doctors::draw_main function body
//this is responsible for drawing the main of the page which will change a lot

Doctors::access_control();

if(!$_SESSION["permission_granted"]){
    
    error("Permission Denied!");
    //include($GLOBALS["draw_includes_path"]."/.DoctorsMain.html");  
    return;
    
}
    
switch($_SESSION["object_type"]){

 case TYPE_DOCUMENT:
     Document::main();            
     break;
    
 case TYPE_REFERENCE:
     Reference::main();            
     break;
 case TYPE_RESOURCE:
     Resource::main();
     break;
 case TYPE_CATEGORY:
     Category::main();
     break;
 case TYPE_ARTICLE:
     Article::main();
     break;
 case TYPE_INPROCEEDINGS:
     InProceedings::main();            
     break;
 case TYPE_INCOLLECTION:
     InCollection::main();            
     break;
 case TYPE_INCOLLECTION:
     InBook::main();            
     break;
 case TYPE_INBOOK:
     InBook::main();            
     break;
 case TYPE_BOOKLET:
     Booklet::main();            
     break;
 case TYPE_THESIS:
     Thesis::main();            
     break;
 case TYPE_TECHREPORT:
     TechReport::main();            
     break;
 case TYPE_MANUAL:
     Manual::main();            
     break;
 case TYPE_UNPUBLISHED:
     Unpublished::main();            
     break;
 case TYPE_MISC:
     Misc::main();            
     break;

 case TYPE_LINK:
     Link::main();            
     break;
 case TYPE_JOURNAL:
     Journal::main();
     break;
 case TYPE_CONFERENCE:
     Conference::main();
     break;
 case TYPE_COLLECTION:
     Collection::main();
     break;
 case TYPE_BOOK:
     Book::main();
     break;
 case TYPE_SCHOOL:
     School::main();
     break;
 case TYPE_COMPANY:
     Company::main();
     break;
 case TYPE_USER:
     User::main();
     break;
 case TYPE_GROUP:
     Group::main();
     break;
 case TYPE_DATA:
     Data::main();
     break;
     
 default: 
     include($GLOBALS["draw_includes_path"]."/.DoctorsMain.html");   
     
}

unset($_SESSION["permission_granted"]);


?>
