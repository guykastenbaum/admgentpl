<?php
$cfdir_gentpl="gen";
$cfdir_root=".";
while(!is_dir("$cfdir_root/$cfdir_gentpl")) $cfdir_root=$cfdir_root."/..";
$cfdir_gen="$cfdir_root/$cfdir_gentpl/";
$cfdir_inc="$cfdir_root/$cfdir_gentpl/";
$cfdir_src="$cfdir_root/$cfdir_gentpl/";
$cfdir_tab="$cfdir_root/$cfdir_gentpl/tab/";
$cfdir_ins="$cfdir_root/";
$cfdir_fck="/inc/lib_js/FCKeditor/";

$db=mysqli_connect($db_host,$db_user,$db_pass);
mysqli_set_charset($db,"utf8")||die('utf8');
mysqli_select_db($db,$db_name)||die('dbsel');
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);
?>
