<?php

//Doctors::access_control
//check if the user is allowed to do what they are requesting.
//sets FALSE if they're not allowed, TRUE if they are.

//For whatever a user wants to do (besides ACTION_ADD), we need to check that the appropriate links
//record exists to indicate that they have ownership of the object.

if(!isset($_SESSION["object_id"]) || $_SESSION["object_id"] == "-1"){

    //if the user is adding things its fine
    $_SESSION["permission_granted"] = TRUE;
    
} else {

    //if the user is doing anything else we need to see
    //if they own that object

    if($_SESSION["object_type"] == TYPE_DOCUMENT){
        $object_type = TYPE_DOCUMENT;
    } else if(Reference::is_reference_object_type($_SESSION["object_type"])){
        $object_type = TYPE_REFERENCE;
    } else if(Resource::is_resource_object_type($_SESSION["object_type"])){
        $object_type = TYPE_RESOURCE;
    } else {
        $object_type = $_SESSION["object_type"];
    }

    $object_id = $_SESSION["object_id"];
    
    switch($object_type){
        
    case TYPE_REFERENCE:
        $link_type = LINK_REFERENCE_OF_USER;
        break;
        
    case TYPE_RESOURCE:    
        $link_type = LINK_RESOURCE_OF_USER;
        break;

    case TYPE_CATEGORY:
        $link_type = LINK_CATEGORY_OF_USER;
        break;
        
    case TYPE_DOCUMENT:
        $link_type = LINK_DOCUMENT_OF_REFERENCE;
        //more stuff to do here
        //go see if the user has access to this reference

        $links = Link::get_links($_SESSION["object_id"], ANY, $link_type);        
        if(count($links) != 1){
            error("Invalid Document.");
        } else {
            $object_id = $links[0]["to_id"];
            $link_type = LINK_REFERENCE_OF_USER;
        }
        break;

    case TYPE_USER:
        //users aren't allowed to alter anyone else
        if($object_id == $_SESSION["user_id"] || $_SESSION["administrator"] == TRUE){
            $_SESSION["permission_granted"] = TRUE;
            return;
        } else {
            $_SESSION["permission_granted"] = FALSE;
            return;            
        }        
        
    default:
        $_SESSION["permission_granted"] = TRUE;
        return;
        break;
        
    }

    //echo "object_id: ".$_SESSION["object_id"]."<br>";
    //echo "user_id: ".$_SESSION["user_id"]."<br>";
    //echo "link_type: ".$link_type."<br>";

    //look for the link record which has: from_id = $_SESSION["object_id"] and to_id = $_SESSION["user_id"]
    $links = Link::get_links($object_id, $_SESSION["user_id"], $link_type);
    
    if(count($links) == 0){            
        $_SESSION["permission_granted"] = FALSE;
    } else {
        $_SESSION["permission_granted"] = TRUE;
    }
    
    
    
}

?>
