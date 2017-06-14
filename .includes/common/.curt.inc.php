<?

//common functions to be accessible everywhere
function error($error_msg){

    print("<p class=\"error\">Error: $error_msg\n</p>");

}

function valid($variable){

    return !($variable == "");

}

function get_action_as_url($object_id, $object_type, $action, $confirm="FALSE", $additional=NULL){

    $str = "index.php?".
            "object_id=".$object_id.
            "&object_type=".get_type_as_string($object_type).
            "&action=".get_action_as_string($action).
            "&confirm=".$confirm;
    if($additional != NULL && is_array($additional)){
        foreach($additional as $key => $value){
            $str .= "&".$key."=".$value;
        }
    }
    return $str;

}

function get_action_as_link($link, $object_id, $object_type, $action, $confirm="FALSE", $additional=NULL, $target=""){

    return "<a target=\"".$target."\" href=\"".get_action_as_url($object_id, $object_type, $action, $confirm, $additional)."\">".$link."</a>";

}

function get_action_as_form($object_id, $object_type, $action, $confirm="FALSE", $additional=NULL){

    $str = "<form action=\"index.php\" method=\"POST\">
                <input type=\"hidden\" name=\"object_id\" value=\"".$object_id."\">
                <input type=\"hidden\" name=\"object_type\" value=\"".get_type_as_string($object_type)."\">
                <input type=\"hidden\" name=\"confirm\" value=\"".$confirm."\">";
    if($additional != NULL && is_array($additional)){
        foreach($additional as $key => $value){
            $str .= "
                <input type=\"hidden\" name=\"".$key."\" value=\"".$value."\">";
        }
    }
    $str .= "
                <input type=\"submit\" name=\"action\" value=\"".get_action_as_string($action)."\">
            </form>";
    return $str;

}

//print out an array as a javascript array;
function get_array_as_javascript_array($array, $name){
    $str = "$name = new Array();\n";
    $countertop = 0;
    foreach($array as $keytop => $valuetop){
        if(is_array($valuetop)){
            //we've got another level to process
            $str .= $name."[".$countertop."] = new Array();\n";
            foreach($valuetop as $keyinner => $valueinner){
                if(!is_int($keyinner)){
                    $keyinner = "'".$keyinner."'";
                }
                $str .= $name."[".$countertop."][".$keyinner."] = \"".trim($valueinner)."\";\n";
            }
        } else {
            if(!is_int($keytop)){
                $keytop = "'".$keytop."'";
            }
            $str .= $name."[".$keytop."] = \"".$valuetop."\";\n";
        }
        $countertop++;
    }
    return $str;
}

//wrap all capitals with curly braces.
function export_val($value){

    return preg_replace("/([A-Z])/", "{\$1}", $value);

}

//remove wrappings of capitals
function import_val($value){

    return preg_replace("/{([A-Z])}/", "\$1", $value);

}

function export_name($name){
    
    $ret = "";    

    $array = explode(" ", $name);
    foreach($array as $portion){                
        if(strlen($portion) > 1)
            $ret .= strtolower($portion);
    }

    return $ret;
}

//redirect to whatever is given.
function redirect($object_id, $object_type, $action){

    echo "
<script language=\"javascript\">
<!--

document.location = \"".get_action_as_url($object_id, $object_type, $action)."\";

//-->
</script>";    
    
}


?>
