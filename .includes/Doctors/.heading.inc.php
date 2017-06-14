<?php

//Doctors::draw_heading function body
//this is responsible for drawing the heading of the page which will not change overmuch

/*echo "
    <p class=\"heading\">
        Doctors
    </p>";*/

if(!DB::db_query("userQuery", "SELECT * FROM \"user\" WHERE \"username\" = '".$_SESSION["username"]."';")){
    error("Getting user from database failed.");
}
$fullname = DB::db_get_field("userQuery", "fullname");


echo "
<table width=\"99%\" background=\"images/title-background.png\" cellpadding=\"0\" cellspacing=\"0\">
<tr><td height=\"3\"><img src=\"images/clear.gif\"></td></tr>
<tr><td><a href=\"".get_action_as_url(-1, TYPE_NULL, ACTION_MAIN)."\"><img src=\"images/title.png\" border=\"0\"></a></td>
    <td align=\"right\" valign=\"bottom\"><p class=\"heading_name\">";
echo $fullname;
echo "&nbsp;&nbsp;</td></tr>
<tr><td height=\"3\" colspan=\"2\"><img src=\"images/clear.gif\"></td></tr>
<tr><td height=\"3\" colspan=\"2\" bgcolor=\"white\"><img src=\"images/clear.gif\"></td></tr>
<tr><td height=\"2\" colspan=\"2\"><img src=\"images/clear.gif\"></td></tr>
<tr><td height=\"1\" colspan=\"2\" bgcolor=\"white\"><img src=\"images/clear.gif\"></td></tr>
<tr><td height=\"1\" colspan=\"2\"><img src=\"images/clear.gif\"></td></tr>
</table>
<br/>
";


?>
