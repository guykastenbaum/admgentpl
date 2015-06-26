<?php
//print_r($ttt);
$ttt["tables_admin"]=array();
foreach(glob("gen/tab/*_inc.php") as $ftabmin){
	$ttabmin=preg_replace(":(^.*/)([^/]*)_inc.php:","$2",$ftabmin);
	if (file_exists("$ttabmin.php")) $ttt["tables_admin"][]=array("table"=>$ttabmin);
}
?>
