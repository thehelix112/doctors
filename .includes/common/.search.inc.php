<?
/*
File: .search.inc.php
Author: David Andrews
Date Started: 28/09/2003
Synopsis:

This file defines methods used during searching.

*/

function get_needles($search_needles){

    //strip out '\'s and add ensure the first character isn't a '"'
    $search_needles = " ".stripslashes($search_needles);

    $keywords = array();

    if(is_integer(strpos($search_needles, '"'))){

        $keywords_phrases = array();
        $phrases = array();
        $is_keyword = TRUE;
        $startpos = 0;

        while(is_integer($nextpos = strpos($search_needles, '"', $startpos))){

            //if we actually have a string to work with
            if(($nextpos-$startpos) > 1){

                //get the string from start to next.
                $needle = substr($search_needles, $startpos, $nextpos-$startpos);
                if($is_keyword){
                    //its just a keyword
                    $keywords_phrases[] = $needle;
                } else {
                    //its a phrase
                    $phrases[] = $needle;
                }
        
            }

            $startpos = ($nextpos+1);
            $is_keyword = (!$is_keyword);

        }

        //no more quotes. add anything that remains as keyword_phrase
        if($startpos < strlen($search_needles)){

            $keywords_phrases[] = substr($search_needles, $startpos);

        }


    } else {

        //if no phrases then just copy and press on
        $keywords_phrases[] = $search_needles;

    }

    //break the keywords up according to whitespaces
    $keyword_string = "";
    if(count($keywords_phrases) > 0){
        foreach($keywords_phrases as $value){
            $keyword_string .= " ".$value;
        }
        $keywords = preg_split("/[\s,]+/", trim($keyword_string));
    }

    //add in the whole phrases
    if(count($phrases) > 0){
        foreach($phrases as $value){
            $keywords[] = $value;
        }
    }

    return $keywords;

}


?>
