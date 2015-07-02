<?php

#filename=tablename.[php or xxx.php]
if (!$tablename)
	$tablename=preg_replace(":^.*/([^/\.]*)\.[^/]*$:","$1",$_SERVER["SCRIPT_NAME"]);

include_once ("$cfdir_tab/".$tablename.'_inc.php');
$tt=array(
	'pagename'=>f_getpagename(),
	'http_host'=>$_SERVER["HTTP_HOST"],
	'script_name'=>$_SERVER["SCRIPT_NAME"],
	'tablename'=>$tablename,
	'ttfilename'=>"$cfdir_root/gentpl/inc/".$tablename.'_inc.php',
	'cfrep_inc'=> "$cfdir_root/gentpl/inc/",
	'cfrep_img'=> "$cfdir_root/gentpl/inc/",
	'ttarrayname'=>$tablename,
	);
/* debut code spe inc *//* fin code spe */
$tablename=$tt["tablename"];
$tb=$$tablename;
$tb=gentpl_tbajoutdef($tb);
$tbids=array_keys($tb);
foreach($tb as $tbk=>$tbv) if ($tbv["type"]=="id") $tbid=$tbk;
if (!$tbid) $tbid=$tbids[0];
$tt["tbid"]=$tbid;
$tt["popup"]=$_REQUEST["popup"];
$tt["message"]=$_REQUEST["message"];
//prepare les menus
foreach($tb as $tbk=>$tbm) //menus dyn
	if ($tbm["menu"]) {
		if (substr($tbm["menu"],0,1)=='$')
			$tbm["menu"]=$tb[$tbk]["menu"]=eval("return(".$tb[$tbk]["menu"].");");
		$tb[$tbk]["tmp_menutab"]=f_sql2menutab($tbm["menu"]);
	}
$action=$_REQUEST["action"];
//prepare la liste de champs
$ttfields=array();
foreach($tb as $name=>$tbv)
{
	if ($tbv["menu"]){
		$tbv["if_menu"]=array("id"=>$name,"menukv"=>array());
		$tmenukv=$tbv["tmp_menutab"];
		foreach($tmenukv as $tmenukvk=>$tmenukvv)
			$tbv["if_menu"]["menukv"][]=array(
				"key"=>$tmenukvk,
				"value"=>
					preg_replace("/^[\-]*-/",$tbv["menu_tabul"],$tmenukvv),
				"valueq"=>
					preg_replace("/^[\-]*-/",$tbv["menu_tabul"],preg_replace("/'/","\'",$tmenukvv)),
				);
	}
	if ($tbv["type"]=="longtext")
		$tbv["if_longtext"]=array("id"=>$name);
	if ($tbv["type"]=="file")
		$tbv["if_file"]=array("id"=>$name);
	if ($tbv["flag_href"])
		$tbv["if_href"]=array("id"=>$name);
	if ($tbv["flag_noedit"] or $tbv["flag_href"])
		$tbv["if_noedit"]=array("id"=>$name);
	$tbv["id"]=$name;
	$ttfields[]=$tbv;//liste des headers
}
/* traitement ajax  multimod */
if ($_REQUEST["action"]=="action_multimod")
{
	$sql="update $tablename set \n";
	$sqlup=0;
	foreach($tb as $tk=>$tv) {
		if ((!is_null($_REQUEST[$tk]))
		    and ($_REQUEST[$tk]!='')){
			$sqlup++;
			$tr=(is_array($_REQUEST[$tk]))?implode(",",$_REQUEST[$tk]):$_REQUEST[$tk];
			if     (($tv["typebdd"]=="int")) $sql.="\n $tk=".f_qqsql($tr).",";
			elseif (($tv["typebdd"]=="date")) $sql.="\n $tk=".f_date2sql($tr).",";
			elseif (($tv["typebdd"]=="text")) $sql.="\n $tk=".f_qqsqlq($tr).",";
		}
	}
	$sql=substr($sql,0,-1)."\n where $tbid=".f_qqsqlr("id")." limit 1";
	if ($sqlup) f_sql2tt0($sql);
	$multimod_msg=$_REQUEST["id"]." OK<br/>\n";
	print(f_str2utf8($multimod_msg));
	exit;
}
/* traitement ajax  multidel */
if ($_REQUEST["action"]=="action_multidel")
{
	$sql="delete from $tablename where $tbid=".f_qqsqlr("id")." limit 1";
	f_sql2tt0($sql);
	$multimod_msg=$_REQUEST["id"]." Suppr.<br/>\n";
	print(f_str2utf8($multimod_msg));
	exit;
}
/* traitement ajax  upload */
if ($_REQUEST["action"]=="editupload_form")
{
	$editupload_msg='
	<form action="'.$tablename.'.php" method="POST" ENCTYPE="multipart/form-data"
		name="editupload_form" id="editupload_form" >
	<input type="hidden" name="action" value="editupload_upload" id="editupload_action">
	<input type="hidden" name="editupload_field" value="'.$_REQUEST["editupload_field"].'" id="editupload_field">
	<input type="hidden" name="editupload_id" value="'.$_REQUEST["editupload_id"].'" id="editupload_id">
	<input type="file" name="editupload_file" id="editupload_file" value="">
	<input class="but_submit" type=submit value=Envoyer id="editupload_submit">
	</form>';
	print(f_str2utf8($editupload_msg));
	exit;
}
if ($_REQUEST["action"]=="editupload_upload")
{
	$editupload_id=$_REQUEST["editupload_id"];
	$editupload_field=$_REQUEST["editupload_field"];
	$editupload_file=$_REQUEST["editupload_file"];
	$msg=gentpl_upload($editupload_field,"editupload_file",$editupload_id);
	print(f_str2utf8($msg));
	exit;
}
/* traitement ajax  de tablekit */
if ((substr($_REQUEST["id"],0,8)=="list_tr_") and (substr($_REQUEST["field"],0,8)=="list_th_"))
{
	$ajax_id=substr(($_REQUEST["id"]),8);
	$ajax_field=substr(($_REQUEST["field"]),8);
	$ajax_value=trim(($_REQUEST["value"]));
	$ajax_value=f_str2iso($ajax_value);//??
	$tk=$ajax_field;
	$tv=$tb[$tk];
	if  (($tv["typebdd"]=="int")) $ajax_value=preg_replace("/[^0-9]*/","",	$ajax_value);
	$postfix=($tv["postfix"])?$tv["postfix"]:"";
	$ajax_value=str_replace($postfix,"",$ajax_value);
	$sql="update $tablename set \n";
	if     (($tv["typebdd"]=="int")) {
		$ajax_value=f_qqsql($ajax_value);
		$sql.="\n $tk=".$ajax_value;
		}
	if     (($tv["typebdd"]=="date")) {
		$ajax_value=f_date2sql($ajax_value);
		$sql.="\n $tk=".$ajax_value;
		$ajax_value=f_sql2date($ajax_value);
		}
	if     (($tv["typebdd"]=="text")) {
		$sql.="\n $tk=".f_qqsqlq($ajax_value);
		}
		$sql.="\n where $tbid=".f_qqsqlq($ajax_id)." limit 1";
	f_sql2tt0($sql);
	if ($tv["menu"]) {
		$ajax_menutab=$tv["tmp_menutab"];
		$ajax_value=$ajax_menutab[$ajax_value];
		}
	print(f_str2utf8($ajax_value.$postfix));
	exit;
}
/* debut code spe head *//* fin code spe */
if ($action=="add") //---------------------------------ADD----------------------------------------
{
	$sql="insert into $tablename ()values()";
	f_sql2tt0($sql);
	$_REQUEST[$tbid]=$$tbid=mysql_insert_id();
	$action="mod";
}
if ($action=="del") //---------------------------------DEL----------------------------------------
{
	$sql="delete from $tablename where $tbid=".f_qqsqlr($tbid);
	f_sql2tt0($sql);
	$tt["message"].=" l'element $tbid de $tablename a été supprimé.";
}
if ($action=="mod")  //---------------------------------MOD----------------------------------------
{
	$sql="update $tablename set \n";
	foreach($tb as $tk=>$tv) {
		if (!is_null($_REQUEST[$tk])){
			$tr=(is_array($_REQUEST[$tk]))?implode(",",$_REQUEST[$tk]):$_REQUEST[$tk];
			if     (($tv["typebdd"]=="int")) $sql.="\n $tk=".f_qqsql($tr).",";
			elseif (($tv["typebdd"]=="date")) $sql.="\n $tk=".f_date2sql($tr).",";
			elseif (($tv["typebdd"]=="text")) $sql.="\n $tk=".f_qqsqlq($tr).",";
		}
	}
	$sql=substr($sql,0,-1)."\n where $tbid=".f_qqsqlr($tbid);
	f_sql2tt0($sql);
	if ($_FILES){
		$msg=$tt["msg"];
		foreach ($_FILES as $name=>$file)
		{
			$kname=preg_replace("/^[a-z]*_/","",$name);
			$msg.=gentpl_upload($kname,$name,$_REQUEST[$tbid]);
		}//for
		$tt["msg"]=$msg;
	}
}
//---------------------------------EDIT----------------------------------------
if (($action=="edit") or ($action=="mod") or ($action=="add"))  
{
	$sql="select * from $tablename where 1 \n";
	foreach($tb as $name=>$tbv)
		if (((!$_REQUEST[$tbid]) or ($name==$tbid)) and ($_REQUEST[$name]))
			$sql.=gentpl_typebdd_wqq($name,$_REQUEST[$name],$tbv["typebdd"],false);
	$tt["form"]=f_sql2tt0($sql);
	foreach(array_keys($tb) as $tbk)
		if ($tb[$tbk]["menu"])
				$tt["form"]["menu_".$tbk] = f_sql2menu($tbk ,$tt["form"][$tbk] ,$tb[$tbk]["tmp_menutab"],$tb[$tbk]["menu_javascript"],$tb[$tbk]["menu_tabul"]);
	/* debut code spe headf *//* fin code spe */
	// reformattage eventuel
	if($tt["form"])
	  foreach($tt["form"] as $ttfield=>$ttv){
		if ($tb[$ttfield]["type"]=="date")
			$tt["form"][$ttfield."_aff"]=f_sql2date($ttv);
		if ($tb[$ttfield]["type"]=="photo")
			$tt["form"][$ttfield."_img"]=f_formatnamejpg($ttv);
		if ($tb[$ttfield]["type"]=="html")
			$tt["form"][$ttfield."_fck"]=htmlentities($ttv);
		if ($tb[$ttfield]["resize"])
		{
			$tcomtph=array();
			foreach(split("[ ,;]",$tb[$ttfield]["resize"]) as $vcomkv)
			{
				list($vcomk,$vcomv)=split("[:=]",$vcomkv);
				$extvk=substr($vcomk,0,2);
				$filvk=substr(preg_replace("|/([^/]*)$|","/".$extvk."_$1","/".$tt["form"][$ttfield]),1);
				$filvk=f_formatnamejpg($filvk);
				$tcomtph[]=array(
						"ext" => $extvk,
						$ttfield."_".$extvk => $filvk,
						$ttfield."_ext" => $filvk,
						$ttfield => $tt["form"][$ttfield],
					);
			}
			$tt["form"]["edit_".$ttfield."_sizes"]=$tcomtph;
		}
		/* debut code spe loopf *//* fin code spe */
		}
	/* debut code spe endf *//* fin code spe */
}
$tt["new"]["pagename"]=f_getpagename();
//---------------------------------DEFAUTS NEW----------------------------------------
//reset search if defined in url
foreach($tb as $name=>$tbv)
	if ((!is_null($_REQUEST["search_".$name])) or (!is_null($_REQUEST["search_".$name."_null"])))
		$_SESSION["search_".$tablename."_filter"]=null;
if ($action=="search") $_SESSION["search_".$tablename."_filter"]=null;
$search_filter=$_SESSION["search_".$tablename."_filter"];
if (!$search_filter) $search_filter=array();
//init search by fields
foreach($tb as $name1=>$tbv)
	foreach(array($name1,$tbv["fk_id"],$tbv["fk_label"]) as $name)
		if (!is_null($name))
			if ((!is_null($_REQUEST["search_".$name])) or (!is_null($_REQUEST["search_".$name."_null"])))
			$search_filter[$name]=
				((!is_null($_REQUEST["search_".$name."_not"]))?"!":"").
				((!is_null($_REQUEST["search_".$name."_null"]))?"null":
				 $_REQUEST["search_$name"]);
if (!is_null($_REQUEST["search_fulltext"]))
	$search_filter["fulltext"]=$_REQUEST["search_fulltext"];
/* debut code spe search *//* fin code spe */
$_SESSION["search_".$tablename."_filter"]=$search_filter;
//init creation
$new_filter=$search_filter;
foreach($tb as $name=>$tbv)
	if (!is_null($_REQUEST["new_$name"]))
		$new_filter[$name]=$_REQUEST["new_$name"];
//init tri
$menu_orderby_tab=array();
foreach($tb as $name=>$tbv)
	if ($tbv["typeaff"]!="hidden")
		$menu_orderby_tab[$name]=$tbv["label"];
//init menus
foreach($tb as $name=>$tbv)
	if ($tbv["menu"]){
		$tt["new"]["menu_$name"]=f_sql2menu("$name",$new_filter[$name],$tbv["tmp_menutab"],$tbv["menu_javascript"],$tbv["menu_tabul"]);
		$tt["search"]["menu_$name"]=f_sql2menu("search_$name",$search_filter[$name],$tbv["tmp_menutab"],$tbv["menu_javascript"],$tbv["menu_tabul"]);
	}
	else
	{
		$search_val=$search_filter[$name];
		$tt["new"]["$name"]=($new_filter[$name]=="null")?"":$new_filter[$name];
		if (substr($search_val,0,1)=="!"){
			$tt["search"]["search_".$name."_not_checked"]="checked";
			$search_val=substr($search_val,1);
		}
		if ($search_val=="null"){
			$tt["search"]["search_".$name."_null_checked"]="checked";
			$search_val="";
		}
		$tt["search"]["$name"]=$search_val;
	}
$tt["search"]["fulltext"]=$search_filter["fulltext"];
$tt["search"]["menu_orderby"]=f_sql2menu("orderby",$tb[$tbid]["orderby"],$menu_orderby_tab);
//---------------------------------LISTE----------------------------------------
// -- liste des existants
$sqlselect="select * ";
$sqljoin="";
foreach($tb as $name=>$tbv)
	if ($tbv["fk_id"])
	{
		$sqljoin.="\n left join ".$tbv["fk_table"].
			" on ".$tablename.".".$name."=".$tbv["fk_table"].".".$tbv["fk_id"];
		$sqlselect.=",".$tbv["fk_table"].".".$tbv["fk_id"];
		$sqlselect.=",".$tbv["fk_table"].".".$tbv["fk_label"];
	}
$sqlselect.=" from $tablename ";
$sqlcount="select count(*) as nb from $tablename ";
$sqlselect.=$sqljoin;
$sqlcount.=$sqljoin;
$whereclause=$tb[$tbid]["whereclause"];
$sqlwhere=" where 1=1 $whereclause ";
foreach($tb as $name1=>$tbv)
	foreach(array($name1,$tbv["fk_id"],$tbv["fk_label"]) as $name)
		if (!is_null($name))
			if ($search_filter[$name]!="")
			{
				$filter=$search_filter[$name];
				$filterexact=($tbv["menu"])?1:0;
				if (substr($filter,0,1)=="!") {
					$filternot=(substr($filter,0,1)=="!")?true:false;
					$filter=substr($filter,1);
				}
				if ((($tbv["typebdd"]=="int")or($tbv["typebdd"]=="date"))
					and (preg_match("/^[<>]=?/",$filter))) {
					$filterexact=preg_replace("/^([<>]=*).*$/","$1",$filter);
					$filterexact=($filterexact==">")?3:(
							($filterexact==">=")?4:(
							($filterexact=="<=")?5:(
							($filterexact=="<")?6:1)));
					$filter=preg_replace("/^[<>]=?/","",$filter);
				}
				if ($tbv["search"]){
					foreach(explode(";",$tbv["search"]) as $search_sqlspe)
						if (preg_match("/".$filter.":/",$search_sqlspe)){
							$filter=preg_replace("/".$filter.":/","",$search_sqlspe);
							$filterexact=10;
							break;
						}
				}
				/*gentpl_typebdd_wqq($v_name,$v_filter,$v_typebdd="text",$v_filternot=false,$v_exact=1,$v_andor="AND")*/
				$sqlwhere.=gentpl_typebdd_wqq($name,$filter,$tbv["typebdd"],$filternot,$filterexact);
			}
$filter_fulltext=$search_filter["fulltext"];
if ($filter_fulltext){
	$sqlwhere.=" and (1=0 ";
	foreach($tb as $name=>$tbv)
		if (($tbv["typebdd"]=="text") or ($tbv["typebdd"]=="date"))
			$sqlwhere.=gentpl_typebdd_wqq($name,$filter_fulltext,$tbv["typebdd"],$filternot,($tbv["menu"])?1:2,"OR");
	$sqlwhere.=" ) ";
}
$sqlorderby=" order by ";
if ($_REQUEST["orderby"]) $sqlorderby.=$_REQUEST["orderby"];
else if ($tb[$tbid]["orderby"]) $sqlorderby.=$tb[$tbid]["orderby"];
		else $sqlorderby.=$tbid;
$sqlorderby.=($_REQUEST["orderby_desc"])?" desc ":" ";
if ($_REQUEST["action2"]=="export"){
	f_export_tt2xls_send(f_sql2tt($sqlselect.$sqlwhere.$sqlorderby),$tablename);
	exit;
}
/* debut code spe headp *//* fin code spe */
$ttlist=array();
//pagination
//in=$pagination_nbpp = nb d'elts par page // $page = url actuelle // $pagination_nb = nb total
//out $ttpagin avec infos pour $sqllimit=" limit $pagination_deb,$pagination_pag ";
$def_orderby=$tb[$tbid]["whereclause"];//non utilisé
$def_orderby=$tb[$tbid]["orderby"];//non utilisé
if (!$_SESSION[$tbid."_pagination_pag"]) $_SESSION[$tbid."_pagination_pag"]=$tb[$tbid]["pagination_pag"];
if ($_REQUEST["pagination_pag"]) $_SESSION[$tbid."_pagination_pag"]=$_REQUEST["pagination_pag"];
$pagination_nbpp=$_SESSION[$tbid."_pagination_pag"];
$ttnb=f_sql2tt0($sqlcount.$sqlwhere);
$ttlist["pagination"]=f_pagination($pagination_nbpp,$ttnb["nb"],f_getpagename(),20);
$sqllimit=" limit ".$ttlist["pagination"]["pagination_deb"].",".$ttlist["pagination"]["pagination_pag"];
$ttlist["fields"]=array();
foreach($ttfields as $name=>$tbv)
	if ($tbv["list"])
		$ttlist["fields"][]=$tbv;
//DEBUG print($sqlselect.$sqlwhere.$sqlorderby.$sqllimit);
$ttlist["list"]=f_sql2tt($sqlselect.$sqlwhere.$sqlorderby.$sqllimit);
for ($i=0;$i<count($ttlist["list"]);$i++)
{
	$ttv=$ttlist["list"][$i];
	$ttv["parite"]=($i%2);//inutile
	$ttv["http_host"]=$_SERVER["HTTP_HOST"];
	$ttv["script_name"]=$_SERVER["SCRIPT_NAME"];
	$ttv["pagename"]=f_getpagename();
	foreach($tb as $ttfield => $tbf)
		if ($tbf["list"]==1)
		{
		// reformattage pour affichage
			$ttvaff=$ttv[$ttfield];
			if ($tbf["typebdd"]=="date")
				$ttvaff=f_sql2date($ttvaff);
			if ($tbf["typeaff"]=="photo"){
				$ttvaff=f_formatnamejpg($ttvaff);
				if (preg_match("/br/",$tb[$ttfield]["resize"])){
					if (preg_match(":/:",$ttvaff))
						$ttv[$ttfield."_br"]=preg_replace(":^(.*)/([^/]*)$:","$1/br_$2",$ttvaff);
					else
						$ttv[$ttfield."_br"]="br_".$ttvaff;
					}
			}
			if ($tbf["menu"])
				if ($tbf["tmp_menutab"][$ttvaff])
					$ttvaff=str_repeat($tbf["menu_tabul"],strlen(
						preg_replace("/^(\-*)([^\-].*)$/","$1",$tbf["tmp_menutab"][$ttvaff]))).
						preg_replace("/^(\-*)([^\-].*)$/","$2",$tbf["tmp_menutab"][$ttvaff]);
			if ($tbf["affix"])
				$ttvaff=filtpl_compile($tbf["affix"],array_merge($tbf,$ttv));
			if (($tbf["typeaff"]=="longtext") and ($tbf["colwidth"]) and ($tbf["flag_noedit"]))
				if (strlen($ttvaff)>$tbf["colwidth"]-5) $ttvaff=substr($ttvaff,0,$tbf["colwidth"]-5)." ... ";
			$ttv[$ttfield."_aff"]=$ttvaff;
			if ($tbf["fk_label"]) $ttv[$tbf["fk_label"]."_aff"]=$ttvaff;
		}
	$ttlist["list"][$i]=$ttv;
	/* debut code spe loop *//* fin code spe */
}
$tt["divlist"]=$ttlist;
$tt["multimod"]=$ttlist;
foreach(array_keys($tb) as $tbk)
	if ($tb[$tbk]["menu"])
			$tt["multimod"]["menu_".$tbk] = f_sql2menu($tbk ,$tt["multimod"][$tbk] ,$tb[$tbk]["tmp_menutab"],$tb[$tbk]["menu_javascript"],$tb[$tbk]["menu_tabul"]);
//affichage ou pas de chaque bloc
//edit
if (($_REQUEST["action"]=="edit")
	or ($_REQUEST["action"]=="add")
	or ($_REQUEST["action"]=="mod"))
{
	if ($_REQUEST[$tbid])
		$tt["divlist"]=$tt["new"]=$tt["search"]=null;
	else
		$tt["divlist"]=$tt["form"]=$tt["search"]=null;
	$tt["if_".(($_REQUEST["popup"])?"":"not_")."popup"]=array("a"=>"a");
}
//close popup
if (($_REQUEST["action"]=="del") and ($_REQUEST["popup"]==1))
		$tt["form"]=$tt["divlist"]=$tt["new"]=$tt["search"]=null;
//ajout de trucs dans form new list etc
foreach($tt as $ttsubsk=>$ttsubsv)
	if (is_array($ttsubsv)){
		$tt[$ttsubsk]["pagename"]=f_getpagename();
		$tt[$ttsubsk]["popup"]=$tt["popup"];
		$tt[$ttsubsk]["if_".(($_REQUEST["popup"])?"":"not_")."popup"]=
			$tt["if_".(($_REQUEST["popup"])?"":"not_")."popup"];
	}
/* debut code spe endp *//* fin code spe */
/* debut code spe end *//* fin code spe */
//print_r($tb);die;
$filehtm=gentpl_action_gentpl_htm($tb);
//print_r($tb);print_r($tt);print($filehtm);die;
print gentpl_template($tt,array(),$filehtm);
?>
