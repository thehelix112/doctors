<?php

//Doctors::draw_overall function body

//this file is responsible for dictating how the different parts of the view are layed out
//these different parts are the menu, heading, crumbs, and main

	//draw the heading at the top
	Doctors::draw_heading();

	echo "
	<table class=\"main\">
		<tr><td class=\"main_crumbs\" colspan=2>";
	Crumbs::draw();
	echo "</td></tr>
		<tr><td valign=\"top\" class=\"main_menu\">";
	Menu::draw();
	echo "</td>
      <td class=\"menu_spacer\">&nbsp;</td>
      <td valign=\"top\" class=\"main_main\">";
	Doctors::draw_main();
	echo "</td></tr>
	</table>";


?>
