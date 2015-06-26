<?php
$cfdir_root=".";
$cfdir_gentpl="gen";
while(!is_dir("$cfdir_root/$cfdir_gentpl")) $cfdir_root=$cfdir_root."/..";
include_once("$cfdir_root/$cfdir_gentpl/gentpl_includes.php") ;
include_once("$cfdir_root/$cfdir_gentpl/gengentpl_tpl.php") ;
?>