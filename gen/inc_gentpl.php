<?php


//petite verrue avant d'avoir l'edition des tableaux : change x:y;z:a;.. par array(x=>y, etc)
function gentpl_f_sql2menu($v_menu_id,$v_menu_sel,$v_tab_or_sql,$v_javascript="")
{
return(f_sql2menu($v_menu_id,$v_menu_sel,$v_tab_or_sql,$v_javascript));
}
function mkbrmrhr($v_dir,$v_name,$v_com)
{
return(f_mkbrmrhr($v_dir,$v_name,$v_com));
}
function gentpl_typebdd_wqq($v_name,$v_filter,$v_typebdd="text",$v_filternot=false,$v_exact=1,$v_andor="AND")
{
	$wqqeqtab=array("=","=","=",">",">=","<=","<");/* exact=like 0 = XX% , 1=XX , 2=%XX% , 3=GT 4=GE 5=LE 6=LT */
	$filternot=($v_filternot)?"not":"";
	if (strtolower($v_filter)=="null")
		return(" $v_andor $v_name IS $filternot NULL");
	// si exact=10 on suppose que c'est du sql ...........
	if ($v_exact==10)
		return(" $v_andor $v_filter");
	if ($v_typebdd=="text"){
		if ($v_exact==1)
			return(" $v_andor $filternot $v_name =".f_qqsqlq($v_filter)." \n");
		else
			return(" $v_andor $v_name $filternot like ".f_qqsqlq(
				(($v_exact==2)?"%":"").
				$v_filter.
				(($v_exact==0 or $v_exact==2)?"%":""))." \n");
	}
	if ($v_typebdd=="int")
		return(" $v_andor $filternot $v_name".$wqqeqtab[$v_exact].f_qqsql($v_filter)." \n");
	if (($v_typebdd=="date")){
		//if (!preg_match("/now()/",$v_filter));
		$v_filter=f_date2sql($v_filter);
		if ($v_exact)
			return(" $v_andor $filternot $v_name".$wqqeqtab[$v_exact].($v_filter)." \n");
		else
			return(" $v_andor ($filternot ( $v_name >=".($v_filter)." \n".
				" AND $v_name < DATE_ADD(".($v_filter).",INTERVAL 1 DAY))) \n");
	}
	if ($v_typebdd=="null") return("");//not a column
	return(" $v_andor $filternot $v_name=".f_qqsqlq($v_filter)." \n");//par defaut
}

//renvoie la page avec le bon template
function gentpl_template($v_tt,$v_ttt=array(),$v_filhtm)
{
global $cfdir_root,$cfdir_inc;
$tthd=array( //trucs par defaut
	'pagename'=>f_getpagename(),
	'http_host'=>$_SERVER["HTTP_HOST"],
	'script_name'=>$_SERVER["SCRIPT_NAME"],
	);
$v_ajax=$_REQUEST["ajax"];
$v_popup=$_REQUEST["popup"];
if (($v_ajax==1) or ($v_popup==1))
	$template_file="template_ajax.htm";
else
	$template_file="template_admin.htm";
$ttt=array_merge($_REQUEST,$_SERVER,$tthd,$v_ttt);
if (file_exists("$cfdir_root/template_inc.php")) include_once("$cfdir_root/template_inc.php");
$ttt["template_header"]=filtpl_compile(f_getpagenamehtm("template_header"),$v_tt);
$pagename=f_getpagename();
if (($v_ajax==1) and (file_exists(preg_replace("/\.php$/","_ajax.htm",$pagename))))
	$pagename=preg_replace("/\.php$/","_ajax.htm",$pagename);
if (file_exists(preg_replace("/\.php$/","_htm.htm",$pagename)))
	$pagename=preg_replace("/\.php$/","_htm.htm",$pagename);
$filhtm=($v_filhtm)?$v_filhtm:f_getpagenamehtm($pagename);
$ttt["html"]=filtpl_compile($filhtm,$v_tt);
//foreach(array(".","..",$cfdir_root,$cfdir_inc) as $dtfi)
//if (file_exists("$dtfi/$template_file")) $dtf=$dtfi;
$html=filtpl_compile(f_getpagenamehtm($template_file),$ttt);
if ($v_ajax==1)
	$html=f_str2utf8($html);
return $html;
}
?>
