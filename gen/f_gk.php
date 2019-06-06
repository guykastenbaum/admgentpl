<?php
//fonctions utilitaires generiques gk
/* in: "yyyy-mm-dd [hh:mm:ss]" or "dd/mm/yyyy [hh:mm:ss]" */
//print_r(sscanf($v_date,"%d/%d/%d %d:%d:%d"));print "$v_date \n";
function f_date2timestamp($v_date){
	$v_date=preg_replace("|[^0-9:\-\/ ]|","",trim($v_date));
	if (!preg_match("|:|",$v_date)) $v_date.=' 00';
	$v_date=preg_replace("|\s+|"," ",$v_date);
	if (preg_match("| \d+$|",$v_date)) $v_date.=':00';
	if (preg_match("| \d+:\d+$|",$v_date)) $v_date.=':00';
	if (preg_match("|^\d+/\d+[^/]|",$v_date)) 
		preg_replace("|^(\d+/\d+)([^/].*$)|","$1/".strftime("%Y")."$2",$v_date);

	if (preg_match("|^\d+-\d+-\d+ \d+:\d+:\d+$|",$v_date))
		list($year,$month, $day, $hour, $min, $sec) = sscanf($v_date,"%u-%u-%u %u:%u:%u");
	if (preg_match("|^\d+/\d+/\d+ \d+:\d+:\d+$|",$v_date))
		list($day,$month,$year,$hour,$min,$sec) = sscanf($v_date,"%u/%u/%u %u:%u:%u");
	if (!$day) return null;
	if ($year<1000) $year+=($year<50)?2000:1900;
	return(mktime ( $hour,$min,$sec,$month,$day,$year));
}
function f_timestamp2sql($v_timestamp){
	if (!$v_timestamp) return("null");
	return(strftime("'%Y-%m-%d %H:%M:%S'",$v_timestamp));
}
function f_date2sql($v_date){
	return(f_timestamp2sql(f_date2timestamp($v_date)));
}
function f_time2sql($v_time){
	global $db;
	if (!$v_time) return("null");
	list($hh,$mm,$ss)=explode(":",mysqli_real_escape_string($db,$v_time));
	if (!is_numeric($hh)) return("null");
	if (!is_numeric($mm)) $mm=0;
	if (!is_numeric($ss)) $ss=0;
	return "'".$hh.":".$mm.":".$ss."'";
}
function f_sql2date($v_datetime){
	return(preg_replace("/ .*$/","",f_sql2datetime($v_datetime)));
}
function f_timestamp2datetime($v_timestamp=-2){
	if (!$v_timestamp) return(null);
	if ($v_timestamp==-2) $v_timestamp=time();
	return(strftime("%d/%m/%y %H:%M:%S",$v_timestamp));
// annee sur 2 chiffres %y, ou 4 chiffres %Y -- on choisi 2
}
function f_sql2datetime($v_datetime){
	return(f_timestamp2datetime(f_date2timestamp($v_datetime)));
}

function f_debug_time($v_msg=""){
	$microtimenow=microtime(true);
	if (!$GLOBALS["f_debug_time"]) $GLOBALS["f_debug_time"]=$microtimenow;
	$microtimelap=$microtimenow - $GLOBALS["f_debug_time"];
	$microtimetrc="";
	foreach(debug_backtrace() as $microtimettrc)
	{
		if (!preg_match("/f_gk.php/",$microtimettrc["file"]))
			$microtimetrc.=
				" ".$microtimettrc["function"].
				":".basename($microtimettrc["file"]).
				":".$microtimettrc["line"];
	}
	if (!is_string($v_msg)) $v_msg=print_r($v_msg,1);
	return(sprintf("%s %2.5f %s : %s \n",
		strftime("%Y-%m-%d-%H:%M:%S"),$microtimelap,$microtimetrc,$v_msg));
}
function f_debug_print_log($v_file,$v_msg){
	if (substr($v_file,-4)!=".log") return;
	$fwlog = fopen($v_file, "a");
	if (!$fwlog) return;
	fwrite($fwlog,f_debug_time($v_msg));
	fclose($fwlog);
}
function f_debug_print($v_msg=""){
	$dbm5=md5($_SERVER["DOCUMENT_ROOT"].strftime("%D"));
	if (!$_SESSION[$dbm5."debug"]) return;
	if (!$_SESSION[$dbm5."debug_file"])
	{
		print "<pre>".f_debug_time($v_msg)."</pre>";
		return;
	}
	$v_file=$_SESSION[$dbm5."debug_file"];
	f_debug_print_log($v_file,$v_msg);
}
function f_isrecursive(){
    foreach(debug_backtrace() as $dbif=>$dbgf)
	foreach(debug_backtrace() as $dbig=>$dbgg)
	    if ($dbig!=$dbif)
		if ($dbgg["function"]==$dbgf["function"])
		    return(true);
    return(false);
}

/* ajoute v_nbjouv jours ouvrés à la date v_timestamp (compte +1 si >v_heurelimite)  , renvoie date (h=0) */
function f_datejourouvres($v_nbjouv,$v_timestamp=null,$v_heurelimite=0){
	$nbjreel=$v_nbjouv;
	$j=($v_timestamp)?$v_timestamp:time();//jour de depart
	$jn=getdate($j);
	if ($jn["hours"]>=$v_heurelimite) $nbjreel++;// +1 si commande apres midi
	$jk=0;
	while($nbjreel>=0){
		$j=mktime(0,0,0,$jn["mon"],$jn["mday"]+$jk,$jn["year"]);
		$jd=getdate($j);
		/* sauf samedi dimanche et jours feries 2012 */
		if (!(
			   ($jd["wday"]==6)  or  ($jd["wday"]==0)   //samedi dimanche
			or (($jd["mon"]==1)  and ($jd["mday"]==1))  //1er janvier
			or (($jd["mon"]==4)  and ($jd["mday"]==9))  //lundi de paques
			or (($jd["mon"]==5)  and ($jd["mday"]==1))  //1er mai
			or (($jd["mon"]==5)  and ($jd["mday"]==8))  //8 mai
			or (($jd["mon"]==5)  and ($jd["mday"]==17)) //jeudi de l'ascension
			or (($jd["mon"]==7)  and ($jd["mday"]==14)) //14 juillet
			or (($jd["mon"]==8)  and ($jd["mday"]==15)) //15 aout
			or (($jd["mon"]==11) and ($jd["mday"]==11)) //11 novembre
			or (($jd["mon"]==12) and ($jd["mday"]==25)) //25 decembre
			))
			$nbjreel--;
		$jk++;
	}
	return($j);
}


function f_qqsq($v_str){global $db;return(mysqli_escape_string($db,($v_str)));} //removed stripslashes
function f_qqsql($v_str){return(((!is_null($v_str)) and (is_numeric($v_str)))?f_qqsq($v_str):"NULL");}
function f_qqsqlq($v_str){return((!is_null($v_str))?"'".f_qqsq($v_str)."'":"NULL");}
function f_qqsqlr($v_str){return(f_qqsql($_REQUEST[$v_str]));}
function f_qqsqlqr($v_str){return(f_qqsqlq($_REQUEST[$v_str]));}
function f_date2sqlr($v_date){return(f_date2sql($_REQUEST[$v_date]));}
function f_time2sqlr($v_time){return(f_time2sql($_REQUEST[$v_time]));}
function f_menu_dyn($v_menu_id,$v_menu_sel,$v_menu_tab,$v_javascript="")
{
	$mendynmulti=(preg_match("/multiple/i",$v_javascript));
	$menu_dyn='<select name="'.$v_menu_id.(($mendynmulti)?"[]":"").'" id="'.$v_menu_id.'" '.$v_javascript.'>'."\n";
	foreach(array_keys($v_menu_tab) as $v_menu_i)
	{
		//teste xy==xy pour ambiguite "" et 0
		$mendynsel=($mendynmulti)?
			preg_match("/[,;\|:]".$v_menu_i."[,;\|:]/",",".$v_menu_sel.","):
			("x".$v_menu_i=="x".$v_menu_sel);
		$menu_dyn.='<option value="'.$v_menu_i.'" '.(($mendynsel)?' selected ':'').'>'.
			$v_menu_tab[$v_menu_i].'</option>'."\n";
	}
	$menu_dyn.='</select>'."\n";
return($menu_dyn);
}
//menu avec parents à partir de sql, attention select id,libelle,parent
function f_ttr2menutab_compare($a, $b) {return strcmp($a["pathname"], $b["pathname"]);}
function f_ttr2menutab($v_menu_tab,$v_orphan=null){
	if (!$v_menu_tab) return($tt2menutab);
	$path_sep="/";
	$menu_tab_in=$v_menu_tab;
	$menu_tab_keys=array_keys($v_menu_tab[0]);
	$menu_tab_id=$menu_tab_keys[0];
	$menu_tab_val=$menu_tab_keys[1];
	$menu_tab_parent=$menu_tab_keys[2];
	$menu_tab_rang=(count($menu_tab_keys>3))?$menu_tab_keys[3]:$menu_tab_id;
	//creation d'un index "id"=>$item
	$menu_tab_idx=Array();
	foreach($menu_tab_in as $menu_path_i => $menu_path_item)
		$menu_tab_idx[$menu_path_item[$menu_tab_id]]=$menu_path_item;
	// calcul du path
	foreach($menu_tab_in as $menu_path_i => $menu_path_item)
	{
		$path=$menu_path_item["path"];
		$pathnice="";
		if ($path=="")
		{
			$menu_path_par=$menu_path_item[$menu_tab_parent];
			$menu_path_order=substr("00000".$menu_path_item[$menu_tab_rang],-5).$menu_path_item[$menu_tab_id];
			$ilimit=100;
			while ("x".$menu_path_par!="x") // boucle juskalaracine
			{
				$path=$menu_path_order.$path_sep.$path;
				$pathnice=$menu_path_par.$path_sep.$pathnice;
				$menu_item_par=$menu_tab_idx[$menu_path_par];
				if (!$menu_item_par) {
					$menu_path_par=null;
					$menu_path_order="orphans";//.$path_sep.$path;
				} else {
					$menu_path_order=substr("00000".$menu_item_par[$menu_tab_rang],-5).$menu_item_par[$menu_tab_id];
					$menu_path_par=$menu_item_par[$menu_tab_parent];
				}
				if (!$ilimit--) die("erreur recursion $path");//un peu hard
			}
			$pathnice=substr($menu_path_par.$path_sep.$pathnice,0,-1);
			$path=substr($menu_path_order.$path_sep.$path,0,-1);
			$menu_tab_in[$menu_path_i]["path"]=$path;//ajout du champ path
			$menu_tab_in[$menu_path_i]["pathname"]=$path;//.$menu_path_item[$menu_tab_id];//ajout du champ path
			$menu_tab_in[$menu_path_i]["pathnice"]=$pathnice;//.$menu_path_item[$menu_tab_id];//ajout du champ path
		}
	}
	usort($menu_tab_in, "f_ttr2menutab_compare");//tri par path
	//prefixe par des "-"
	foreach($menu_tab_in as $menu_tab_i=>$menu_tab_item)
		$menu_tab_in[$menu_tab_i]["pathprefix"]=
			preg_replace(":".$path_sep.":","-",
				preg_replace(":[^".$path_sep."]*:","",$menu_tab_item["path"]));

	if ($v_orphan) // si on garde les orphelins
	{
		$menu_tab_orphan=-1;
		foreach($menu_tab_in as $menu_tab_i=>$menu_tab_item)
			if (($menu_tab_orphan==-1) and (preg_match("/^orphans/",$menu_tab_item["path"])))
				$menu_tab_orphan=$menu_tab_i;
		if ($menu_tab_orphan>=0)
			array_splice($menu_tab_in, $menu_tab_orphan,0,array(array(
				"path"=>"orphanage","pathprefix"=>"",$menu_tab_id=>$v_orphan,$menu_tab_val=>$v_orphan)));
	}
	else
	{
		foreach($menu_tab_in as $menu_tab_i=>$menu_tab_item)
			if (preg_match("/^orphans/",$menu_tab_item["path"]))
				unset($menu_tab_in[$menu_tab_i]);
	}

	//print_r($menu_tab_in);die();
	//calcul de menutab classique
	$tt2menutab=Array();
	foreach($menu_tab_in as $menu_tab_item)
		$tt2menutab[$menu_tab_item[$menu_tab_id]]=$menu_tab_item["pathprefix"].$menu_tab_item[$menu_tab_val];
	return($tt2menutab);
}
//menu à partir de sql, attention select id,libelle
function f_tt2menutab($v_menu_tab){
	$tt2menutab=Array();
	if (!$v_menu_tab) return($tt2menutab);
	$menu_tab_keys=array_keys($v_menu_tab[0]);
	$menu_tab_id=$menu_tab_keys[0];
	$menu_tab_val=$menu_tab_keys[1];
	foreach($v_menu_tab as $menu_tab_item)
		$tt2menutab[$menu_tab_item[$menu_tab_id]]=$menu_tab_item[$menu_tab_val];
	return($tt2menutab);
}
/*
OLDf_sql2menu($v_menu_id,$v_menu_sel,$v_tab_or_sql,$v_javascript=""){
	if (is_array($v_tab_or_sql))
		return(f_menu_dyn($v_menu_id,$v_menu_sel,$v_tab_or_sql,$v_javascript));
	return(f_menu_dyn($v_menu_id,$v_menu_sel,f_tt2menutab(f_sql2tt($v_tab_or_sql)),$v_javascript));
}
*/
//ajout menu par string: change x:y;z:a;.. par array(x=>y, etc) +u;v;w possib
function f_sql2menu_keyval($v_tab_or_sql)
{
	if (preg_match("/^[a-zA-Z0-9 \_\-\.']*[;:=]/",$v_tab_or_sql))
	{
		$v_tab=array();
		foreach(explode(";",$v_tab_or_sql) as $sql2menu_kvm2a0)
			$v_tab[]=array("key"=>preg_replace("/[:=].*$/","",$sql2menu_kvm2a0),
					"value"=>preg_replace("/^[^:=]*[:=]/","",$sql2menu_kvm2a0));
	}
	return($v_tab);
}
//menu (string keyval, select ou tableau) => menu
function f_sql2menutab($v_tab_or_sql,$v_orphan=null)
{
	if (is_array($v_tab_or_sql))
		$v_tab=$v_tab_or_sql;
	else
	{
		if (preg_match("/^[a-zA-Z0-9 \_\-\.']*[;:=]/",$v_tab_or_sql))
			$v_tab=f_sql2menu_keyval($v_tab_or_sql);
		else
			$v_tab=f_sql2tt($v_tab_or_sql);
		$v_tab0=($v_tab[0])?$v_tab[0]:array("key"=>"","val"=>"");
		foreach(array_keys($v_tab0) as $v_tabk) $v_tab0[$v_tabk]="";
		if (!array_key_exists("",$v_tab)) array_unshift($v_tab,$v_tab0);
		if (count($v_tab0)<=2)  // id nom
			$v_tab=f_tt2menutab($v_tab);
		if ((count($v_tab0)==3) or (count($v_tab0)==4)) //id nom parent
			$v_tab=f_ttr2menutab($v_tab,$v_orphan);
	}
	return($v_tab);
}
function f_sql2menu($v_menu_id,$v_menu_sel,$v_tab_or_sql,$v_javascript="",$v_prefix=null,$v_orphan=null)
{
	$v_tab=f_sql2menutab($v_tab_or_sql,$v_orphan);
	if ($v_prefix)
		foreach($v_tab as $tabk=>$tabv)
			$v_tab[$tabk]=str_repeat($v_prefix,strlen(
				preg_replace("/^(\-*)([^\-].*)$/","$1",$tabv))).
				preg_replace("/^(\-*)([^\-].*)$/","$2",$tabv);
	return(f_menu_dyn($v_menu_id,$v_menu_sel,$v_tab,$v_javascript));
}
//---------------

function f_format_texte_long($v_f_format_texte){
	$v_f_format_texte=stripslashes($v_f_format_texte);
	if (substr($v_f_format_texte,0,1)!="<")
	{
		$v_f_format_texte=html_entity_decode($v_f_format_texte);
		//ici traiter une mise en page ....
		$v_fft_txt="";
		$v_fft_txtul=0;
		foreach (explode("\n",$v_f_format_texte) as $v_fft_txtline)
		{
			if (substr($v_fft_txtline,0,2)=="- "){
				$v_fft_txtline="<li>".substr($v_fft_txtline,2)."</li>\n";
				if ($v_fft_txtul==0)
				$v_fft_txtline="<ul>".$v_fft_txtline;
				$v_fft_txtul=1;
				$v_fft_txt.=$v_fft_txtline."\n";
			}else{
				if ($v_fft_txtul==1){
					$v_fft_txtline="</ul>".$v_fft_txtline;
					$v_fft_txt=0;
				}
				$v_fft_txt.=$v_fft_txtline."<br>\n";
			}//"- "
		}
		$v_fft_txt=preg_replace("/<br>\n$/","",$v_fft_txt);
		$v_f_format_texte=$v_fft_txt;
	}
	return $v_f_format_texte;
}
function f_format_texte_court($v_f_format_texte){
	$v_f_format_texte=stripslashes($v_f_format_texte);
	return $v_f_format_texte;
}
function f_rskeys($v_rs)
{
	$rskeys=array();
	foreach(array_keys($v_rs) as $rsk)
			if (!is_int($rsk))
				$rskeys[$rsk]=$v_rs[$rsk];
	return($rskeys);
}
function f_sql2tt($v_sql)
{
	global $db;
	$f_sql2tt= array();
	f_debug_print($v_sql);
	($lpag = mysqli_query($db,$v_sql) ) or die (mysqli_error($db));
	if (!is_bool($lpag))
		while ($rs=mysqli_fetch_array($lpag))
			array_push($f_sql2tt,f_rskeys($rs));
	f_debug_print($f_sql2tt);
	return($f_sql2tt);
}
function tt_utf8_decode(&$v_tt,$v_champ)
{
	for($i=0;$i<count($v_tt);$i++) 
		$v_tt[$i][$v_champ]=utf8_decode($v_tt[$i][$v_champ]);
}
function f_sql2tt0($v_sql){
	if ((preg_match("/^select/i",$v_sql)) 
		and (!preg_match("/; *$/",$v_sql))
		and(!preg_match("/limit 1/i",$v_sql))) 
			$v_sql.=" limit 1";
	$f_sql2tt0=f_sql2tt($v_sql);
	if(!$f_sql2tt0) return(null);
	if(!$f_sql2tt0[0]) return(null);
	return($f_sql2tt0[0]);
}
function f_str2iso($v_str){
	return ($v_str != utf8_encode(utf8_decode($v_str)))?
		$v_str:utf8_decode($v_str);
}
function f_str2utf8($v_str){
	return ($v_str != utf8_encode(utf8_decode($v_str)))?
		utf8_encode($v_str):$v_str;
}
//renvoie le nom de la page html/php
function f_getpagename(){
	$server_script_name=$_SERVER["PHP_SELF"];
	if (!$server_script_name) $server_script_name=$_SERVER["SCRIPT_NAME"];
	if (!$server_script_name) $server_script_name=$_SERVER["SCRIPT_FILENAME"];
	$server_script_name=f_str2iso(preg_replace(":^.*/:","",$server_script_name));
return($server_script_name);
}
//renvoie le contenu html
function f_getfilenamehtm($v_str=null){
	$f_gpn_htm=($v_str)?$v_str:f_getpagename();
	$f_gpn_htm=preg_replace("/\.[^\.\/]*$/","",$f_gpn_htm);
	$f_gpn_fil=$f_gpn_htm.".htm";
	if (file_exists($f_gpn_fil)) return($f_gpn_fil);
	$f_gpn_fil=$f_gpn_htm.".html";
	if (file_exists($f_gpn_fil)) return($f_gpn_fil);
	$f_gpn_fil=$_SERVER["DOCUMENT_ROOT"]."/".$f_gpn_htm.".htm";
	if (file_exists($f_gpn_fil)) return($f_gpn_fil);
	$f_gpn_fil=$_SERVER["DOCUMENT_ROOT"]."/".$f_gpn_htm.".html";
	if (file_exists($f_gpn_fil)) return($f_gpn_fil);
	return(null);
}
function f_getpagenamehtm($v_str=null){
	$f_gpn_htm=f_getfilenamehtm($v_str);
	if (!$f_gpn_htm) return(null);
	return(implode("",file("$f_gpn_htm")));
}
function f_desaccentuation($v_str)
{
	//et si j'utilisait htmlentities pour les accents !?: référence devient reference
	$str=f_str2utf8($v_str);
	$str=f_str2iso(html_entity_decode($str,ENT_NOQUOTES,"UTF-8"));
	$str=preg_replace("/[\xa0-\xbf\xd7\xf7]/"," ",$str);
	$str=htmlentities(f_str2utf8($str),ENT_NOQUOTES,"UTF-8");
	$str=str_replace("&nbsp;"," ",$str);
	$str=str_replace("&lt;","<",$str);
	$str=str_replace("&gt;",">",$str);
	$str=str_replace("&amp;","&",$str);
	$str=str_replace("&quot;",'"',$str);
	$str=str_replace("&apos;","'",$str);
	return(preg_replace("/\&(.)[^\;]*\;/","$1",$str));
}
function f_lib_js_htm()
{
	$f_lib_js_htm="";
	foreach(array("ah_js.js","prototype.js","scriptaculous.js","behaviour.js","builder.js","effects.js","controls.js","dragdrop.js","slider.js") as $jsfile)
		$f_lib_js_htm.='<script src="/inc/lib_js/'.$jsfile.'" type="text/javascript"></script>'."\n";
	return($f_lib_js_htm);
}

//pagination
//in=$pagination_nbpp = nb d'elts par page // $page = url actuelle // $pagination_nb = nb total  // max de liens le reste est en ...
//out $ttpagin avec infos pour $sqllimit=" limit $pagination_deb,$pagination_pag ";
/* pagination , exemple : 
$pagination_nbpp=9;$ttnb=f_sql2tt0($sqlcount.$sqlwhere);
$tt["pagination"]=f_pagination($pagination_nbpp,$ttnb["nb"],$page);
$sqllimit=" limit ".$tt["pagination"]["pagination_deb"].",".$tt["pagination"]["pagination_pag"];
<div id="nbr_pages">
<!-- BEGIN pagination -->
<!-- BEGIN pagination_prec --><a href="{pagination_url}">{pagination}</a><!-- END pagination_prec -->
<!-- BEGIN pagination_act --><span>{pagination}</span><!-- END pagination_act -->
<!-- BEGIN pagination_suiv --><a href="{pagination_url}">{pagination}</a><!-- END pagination_suiv -->
<!-- END pagination -->
</div>
#nbr_pages{clear: both;font: normal 11px "Trebuchet MS", Verdana, sans-serif;margin: 10px 0 0 5px;}
#nbr_pages a{font: normal 11px "Trebuchet MS", Verdana, sans-serif;display: inline;
	padding: 0 3px 0 3px;background: #666;color: #FFF;text-decoration: none;margin: 0 5px 0 0;}
#nbr_pages a:hover{font: normal 11px "Trebuchet MS", Verdana, sans-serif;color: #FFF;background: #F00;text-decoration: none;}
#nbr_pages span{background: #EEE;color: #000;margin: 0;padding: 0 3px 0 3px;text-decoration: none;}
req(pagination)=numero de page courante ;; pg_nb=nombre d'items ;; pg_nbpp ou req(pg_pag) si exist=nb d'itzm pas page
*/
function f_pagination($pagination_nbpp,$pagination_nb,$v_page,$v_maxpages=10)
{
	$ttpagination=array();
	$pagination_url=$v_page."?";
	foreach(array_keys($_REQUEST) as $req)
		if ((! preg_match("/(SESSID|pagination)/",$req)) and (is_string($_REQUEST[$req])))
			$pagination_url.=$req."=".urlencode($_REQUEST[$req])."&";
	$ttpagination["pagination_url"]=$pagination_url;
	$ttpagination["pagination_nb"]=$pagination_nb;
	$ttpagination["pagination_pag"]=$pagination_pag=
		($_REQUEST["pagination_pag"]>0)?$_REQUEST["pagination_pag"]:(($pagination_nbpp)?$pagination_nbpp:20);
	$ttpagination["pagination"]=$pagination=
		($_REQUEST["pagination"]>0)?$_REQUEST["pagination"]:1;
	$ttpagination["pagination_nb_act_premier"]=
		(1+($pagination-1)*$pagination_pag > $pagination_nb)?$pagination_nb:1+($pagination-1)*$pagination_pag;
	$ttpagination["pagination_nb_act_dernier"]=
		($pagination*$pagination_pag>$pagination_nb)?$pagination_nb:$pagination*$pagination_pag;
	$ttpagination["pagination_prec"]=array();
	$ttpagination["pagination_suiv"]=array();
	$pagination_nbpag=floor(($pagination_nb+$pagination_pag-1)/$pagination_pag);
	//$pagination_maxpag=($v_maxpages<$pagination_nbpag)?$v_maxpages:$pagination_nbpag;
	$pagination_maxpag=$v_maxpages;
	if ($pagination_nbpag>1)
		for ($i_pag=1;$i_pag<=$pagination_nbpag;$i_pag++)
		{
			$pagination_urlpag=$pagination_url."&pagination=".$i_pag;
	        if ($i_pag < $pagination) //and ($i_pag > $pagination-$pagination_maxpag))
	                array_push($ttpagination["pagination_prec"],array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag));
	        if (($i_pag == $pagination))
	                $ttpagination["pagination_act"]=array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag);
	        if ($i_pag > $pagination) //and ($i_pag < $pagination+$pagination_maxpag))
	                array_push($ttpagination["pagination_suiv"],array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag));
	        if (($i_pag == $pagination - 1))
	                $ttpagination["pagination_pageprec"]=array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag);
	        if (($i_pag == $ttpagination["pagination"] + 1))
	                $ttpagination["pagination_pagesuiv"]=array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag);
	        if (($i_pag == 1))
	                $ttpagination["pagination_pagepremiere"]=array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag);
	        if (($i_pag == $pagination_nbpag))
	                $ttpagination["pagination_pagederniere"]=array("pagination_url"=>$pagination_urlpag,"pagination"=>$i_pag);
		}
	$pagedeb=$pagination_pag*($ttpagination["pagination"]-1);
	if ($pagedeb<0) $pagedeb=0;
	if (count($ttpagination["pagination_suiv"])>$pagination_maxpag)
	{
		array_splice($ttpagination["pagination_suiv"],$pagination_maxpag,count($ttpagination["pagination_suiv"])-$pagination_maxpag-2);
		$ttpagination["pagination_suiv"][$pagination_maxpag]["pagination"]="...";
	}
	if (count($ttpagination["pagination_prec"])>$pagination_maxpag)
	{
		array_splice($ttpagination["pagination_prec"],1,count($ttpagination["pagination_prec"])-$pagination_maxpag-1);
		$ttpagination["pagination_prec"][1]["pagination"]="...";
	}
	if ($pagedeb>$pagination_nb) $pagedeb=$pagination_nb;
	$ttpagination["pagination_deb"]=$pagedeb;
	return($ttpagination);
}

//importe un fichier tabulé en un tableau associatif (clés=1ere ligne des champs)
// sans clés en 1ere ligne si nohead=1
function f_import_xls2tt($v_file,$v_nohead=0)
{
	if (file_exists($v_file))
		$prodfilel=file($v_file);//fichier de tableau de lignes
	else
		$prodfilel=(is_array($v_file))?$v_file:explode("\n",$v_file);//tableau de lignes
	$prodhdrl=trim(array_shift($prodfilel));
	$sep="\t";//detection \t ; et "
	if (preg_match("/;/",$prodhdrl)) $sep=";";//bof
	if (preg_match("/\t/",$prodhdrl)) $sep="\t";
	$prodhdrl=preg_replace("/".$sep."$/","",$prodhdrl);//trim
	$prodhdr=explode($sep,$prodhdrl);//1ere ligne=nom des champs
	if ($v_nohead)
	{
		$prodhdr0=array();
		for ($prodhdri=0;$prodhdri<count($prodhdr);$prodhdri++) 
			$prodhdr0[$prodhdri]=$prodhdri;
		array_push($prodfilel,$prodhdrl);
		$prodhdr=$prodhdr0;
	}
	foreach($prodhdr as $prodhdri=>$prodhdrv)
		$prodhdr[$prodhdri]=preg_replace('/^"(.*)"$/',"$1",$prodhdrv);
	$prodtab=array();
	foreach($prodfilel as $prod_l)
	{
		if (trim($prod_l)!="")
		{
			$ilig++;
			$prod_l=preg_replace("/".$sep."$/","",trim($prod_l));//trim
			$prod_a=explode($sep,$prod_l);
			$prod_h=array();
			foreach($prodhdr as $prodhdri) $prod_h[$prodhdri]="";//init vide
			for($i=0;$i<count($prod_a);$i++)
			{
				$val=$prod_a[$i]; //prod_h(col)=>item
				$val=str_replace("\\n",chr(10),$val);//remplace \\n par \n
				$val=str_replace("\\t",chr(9),$val);//remplace \\t par \t
				$val=preg_replace('/^"(.*)"$/',"$1",$val);//vire "truc"
				$prod_h[$prodhdr[$i]]=$val;
			}
			$prodtab[]=$prod_h;//empile
		}
	}
	return($prodtab);
}

function f_export_tt2xls_send($v_export,$v_exportname="export",$v_sep="\t")
{
	header("Content-type: application/xls");
	header(strftime("Content-Disposition: attachment; filename=".$v_exportname."_%y%m%d%H%M%S.xls"));
	print f_export_tt2xls($v_export,$v_sep);
	exit;
}
function f_export_tt2xls($v_export,$v_sep="\t")
{
	$conf_replace_tab_bakt=0;
	$conf_replace_tab_space=1;
	$conf_replace_cr_bakn=1;
	$conf_replace_cr_space=0;
	$conf_replace_vr_space=0;
	$conf_replace_pv_space=0;
	$conf_exportname="export_".$table;
	$keyst=array();
	for($i=0;$i<min(50,count($v_export));$i++) foreach(array_keys($v_export[$i]) as $k) $keyst[$k]=1;
	$keys=array_keys($keyst);
	$export=(implode($v_sep,$keys))."\n";
	for($i=0;$i<count($v_export);$i++){
		for($j=0;$j<count($keys);$j++){
			$val=$v_export[$i][$keys[$j]];
			if ($conf_replace_tab_bakt) $val=preg_replace("/\t/m","\\t",$val);
			if ($conf_replace_tab_space) $val=preg_replace("/\t/m"," ",$val);
			$val=preg_replace("/\r\n/s","\n",$val);
			$val=preg_replace("/\n\r/s","\n",$val);
			$val=preg_replace("/\r/s","\n",$val);
			if ($conf_replace_cr_bakn) $val=preg_replace("/\n/s","\\n",$val);
			if ($conf_replace_cr_space) $val=preg_replace("/\n/s"," ",$val);
			if ($conf_replace_vr_space) $val=preg_replace("/,/s"," ",$val);
			if ($conf_replace_pv_space) $val=preg_replace("/;/s"," ",$val);
			if ($j!=0) $export.=$v_sep;
			$export.=$val;
		}
		$export.="\n";
	}
return($export);
}

/* unserialize with fix string length http://www.biggnuts.com/fix-corrupt-serialized-data/ */
function f_unserialize_fix($v_data)
{
	$splits = preg_split("/s:([0-9]*):/", $v_data);
	preg_match_all("/s:([0-9]*):/", $v_data, $lengths);
	$lengths = $lengths[1];
	for($i = 0; $i < sizeof($splits)-1; $i++){
		$text = $splits[$i+1];
		$pos = strpos($text, '";');
		$text = substr($text, 1, $pos-1);
		$text_len = strlen($text);
		if($lengths[$i] != $text_len)
			$lengths[$i] = $text_len;
		//echo "{$lengths[$i]} -> $text_len\n";
	}
	$return = $splits[0];
	for($i = 0; $i < sizeof($splits)-1; $i++){
		$return .= "s:{$lengths[$i]}:";
		$return .= $splits[$i+1];
	}
	return $return;
}
function f_unserialize($v_data){
	$unserialized_data=unserialize($v_data);
	if ($unserialized_data) return($unserialized_data);
	$v_data=f_unserialize_fix($v_data);
	$unserialized_data=unserialize($v_data);
	return($unserialized_data);
}
//nom d'image en iso ou utf8
function f_formatnamejpg($v_namejpg,$v_fscode="ISO")
{
	$v_namejpg=preg_replace("/\.(jpeg|jpg)$/i",".jpg",$v_namejpg);
	$v_namejpg=preg_replace("/\.png$/i",".png",$v_namejpg);
	$v_namejpg=preg_replace("/\.gif$/i",".gif",$v_namejpg);
	if (!preg_match("/\.(gif|jpg|png)$/",$v_namejpg)) $v_namejpg=$v_namejpg.".jpg";
	if ($v_fscode=="ISO")
		$v_namejpg=f_str2iso($v_namejpg);
	else
		$v_namejpg=f_str2utf8($v_namejpg);
	return(preg_replace("/%2F/i","/",rawurlencode($v_namejpg)));
}
// resize des photos crée des br_xxx.jpg (br mr hr etc au choix) 
// syntaxe com = brw=150,mrw=280,hrw=500 br=XX W ou H ou les deux = nb px maxi
function f_mkbrmrhr($v_dir,$v_name,$v_com,$v_etirer=true)
{
	$tcomt=$tcom2t=array();
	foreach(preg_split("/[ ,;]/",$v_com) as $vcomkv)
	{
		list($vcomk,$vcomv)=preg_split("/[:=]/",$vcomkv);
		$tcomt[$vcomk]=$vcomv;
		$tcomt2[substr($vcomk,0,2)]=1;
	}
	$fname = $v_dir."/".$v_name;
	$fvdir=preg_replace(":/[^/]*$:","",$fname);
	$fvname=preg_replace(":\.jpg:i","",preg_replace(":^.*/:","",$fname)).".jpg";
	$img=imagecreatefromjpeg($fname);
	list($largeur,$hauteur)=getimagesize($fname);
	foreach(array_keys($tcomt2) as $pfxi)
	{
		if ($tcomt[$pfxi."h"] and $tcomt[$pfxi."w"])
			$cotefix=($largeur/$tcomt[$pfxi."w"]>$hauteur/$tcomt[$pfxi."h"])?"w":"h";//plus gd cote
		else
			$cotefix=($tcomt[$pfxi."h"])?"h":"w";
		if ($cotefix=="w")
		{
			$largeur2=$tcomt[$pfxi."w"];
			$hauteur2=round(($largeur2/$largeur)*$hauteur);
			$stretch=($hauteur2>$hauteur)?1:0;
		}
		if ($cotefix=="h")
		{
			$hauteur2=$tcomt[$pfxi."h"];
			$largeur2=round(($hauteur2/$hauteur)*$largeur);
			$stretch=($largeur2>$largeur)?1:0;
		}
		if (($stretch) and (!$v_etirer)) // on ne fait pas de streching
		{
			$hauteur2=$hauteur;
			$largeur2=$largeur;
		}
		$img3=imagecreatetruecolor($largeur2,$hauteur2);
		//imagecopyresized mais en mieux
		imagecopyresampled($img3,$img,0,0,0,0,$largeur2,$hauteur2,$largeur,$hauteur);
		imagejpeg($img3,$fvdir."/".$pfxi."_".$fvname);//qualite 70%
	}
}
/* renvoie . ou ./.. ou ./../.. etc jusquau root level */
function f_getrootrelpath()
{
	$droot=strtolower(str_replace("\\","/",realpath($_SERVER["DOCUMENT_ROOT"])));
	$droot=preg_replace(":/$:","",$droot);
	for ($rootrelpath=".",$i=0;$i<20;$i++,$rootrelpath.="/.."){
		$rroot=strtolower(str_replace("\\","/",realpath($rootrelpath)));
		if ($rroot==$droot) return($rootrelpath);
	}
	return(".");//oups raté
}
//execute une fonction recursivement sur un arbre (max 20 niveaux)
//faudra peut etre un truc avec 2 parametres pour la clé
function f_array_recurse($x_tt,$v_fx,$v_levelmax=20)
{
	if ($v_levelmax<=0) die("f_array_recurse too many recursion");
	if (!is_array($x_tt)) return($v_fx($x_tt));
	foreach($x_tt as $x_ttk=>$x_ttv)
		$x_tt[$x_ttk]=f_array_recurse($x_ttv,$v_fx,$v_levelmax-1);
	return($x_tt);
}

function f_redim_hw($imgh,$imgw,$maxh,$maxw)
{
	if ((!$imgh) or (!$imgw) or (!$maxh) or (!$maxw)) return(array($imgh,$imgw));
	if (($imgh/$imgw)>($maxh/$maxw))
		return(array(ceil($maxh),ceil($imgw*($maxh/$imgh))));
	else
		return(array(ceil($imgh*($maxw/$imgw)),ceil($maxw)));
}
function f_redim_attr($imgh,$imgw,$v_maxh,$v_maxw){
	list($imgh,$imgw)=f_redim_hw($imgh,$imgw,$v_maxh,$v_maxw);
	return(' height="'.$imgh.'" width="'.$imgw.'" ');
}

//multi/mono wget : in=url or ["url"=>url] , out=content or array[+content]
function f_wget_curl($v_url,$v_data=null,$v_curlopts=null)
{
	$turls=(is_array($v_url))?$v_url:array(array("url"=>$v_url));
	$t_curlopts=array();
	if (is_array($v_curlopts)) $t_curlopts=$v_curlopts;
	if (is_numeric($v_curlopts)) $t_curlopts[CURLOPT_TIMEOUT]=$v_curlopts;
	
	$mh = curl_multi_init();
	foreach($turls as $iurl=>$turl)
	{
		$url=$turl["url"];
		f_debug_print($url);
		$ch = curl_init($url);

		$t_curloptsdef=array(
			CURLOPT_HTTPHEADER => array('X-Real-IP: '.(($_SERVER["HTTP_X_REAL_IP"])?$_SERVER["HTTP_X_REAL_IP"]:$_SERVER["REMOTE_ADDR"])),
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_REFERER => "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"],
			CURLOPT_HEADER => 0,
			);
		//ajoute les defs au debut
		$tt_curlopts=array();
		foreach($t_curloptsdef as $kcurlopt=>$vcurlopt)
			if (!array_key_exists($kcurlopt,$t_curlopts))
				$tt_curlopts[$kcurlopt]=$vcurlopt;
		foreach($t_curlopts as $kcurlopt=>$vcurlopt)
			$tt_curlopts[$kcurlopt]=$vcurlopt;
		//ajoute les posts a la fin
		if (($v_data) and (is_array($v_data))){
			$tt_curlopts[CURLOPT_POST]=1;
			$tt_curlopts[CURLOPT_POSTFIELDS]=$v_data;
		}

		curl_setopt_array($ch, $tt_curlopts);
		$turls[$iurl]["ch"]=$ch;

		curl_multi_add_handle($mh,$ch);
	}
	$active = null;
	do {
		$status = curl_multi_exec($mh, $active);
		//$info = curl_multi_info_read($mh);if (false !== $info) {var_dump($info);}
	} while ($status === CURLM_CALL_MULTI_PERFORM || $active);

	foreach ($turls as $i => $turl) {
		$turls[$i]["content"] = curl_multi_getcontent($turl["ch"]);
		$turls[$i]["code"]=curl_getinfo ( $turl["ch"],CURLINFO_HTTP_CODE);
		$turls[$i]["errno"]=curl_errno($turl["ch"]);
		curl_close($turl["ch"]);
		curl_multi_remove_handle($mh, $turl["ch"]);
	}
	curl_multi_close($mh);
	return((is_array($v_url))?$turls:$turls[0]["content"]);
}

//nettoyage de nom d'url ou domaine
function f_urldomclean($v_url) {
	$v_url=preg_replace("|^http[s]*://|","",$v_url);
	$v_url=preg_replace("|^([^/]*)/.*$|","$1",$v_url);
	$v_url=strtolower($v_url);
	$v_url=preg_replace("|[^a-z0-9\-\.]|","",$v_url);
	$v_url=preg_replace("|^www\.|","",$v_url);
	if (strlen($v_url)<2) return(null);
	if (!strpos("x".substr($v_url,-5),".")) $v_url.=".com";
	return($v_url);
}
function f_urlclean($v_url) {
	$v_url=f_urldomclean($v_url);
	if (!$v_url) return(null);
	$urlnx=preg_replace("/\.[a-z]{1,3}$/","",$v_url);//enleve .com,.uk
	$urlnx=preg_replace("/\.[a-z]{1,2}$/","",$urlnx);//enleve .co[.uk]
	if (preg_match("|\.|",$urlnx)) return($v_url);//already prefixed
	return("www.".$v_url);
}
//A simple but useful packaging for continuing processing after telling 
//the the browser that output is finished.
//http://fr2.php.net/manual/en/features.connection-handling.php
function f_discontinue($sURL=null,$v_time_limit=null) 
{
	if ($sURL) header( "Location: ".$sURL );
	if (is_numeric($v_time_limit)) set_time_limit ($v_time_limit);
	ob_end_clean(); //arr1s code 
	header("Connection: close"); 
	ignore_user_abort(); 
	ob_start(); 
	header("Content-Length: 0"); 
	ob_end_flush(); 
	flush(); // end arr1s code 
	session_write_close(); // as pointed out by Anonymous 
} 
function f_monthsdates($v_date=null) //now(ms,ts),act(ms,ts),pre(ms,ts),sui(ms,ts)
{
	//mois courant
	$datents=time();
	$daten=strftime("1/%m/%Y",$datents);

	$dateats=($v_date)?f_date2timestamp($v_date):time();
	$datea=strftime("1/%m/%Y",$dateats);

	//mois prec
	$datepm=strftime("%m",$dateats);
	$datepy=strftime("%Y",$dateats);
	
	$datem=1+($datepm-1+12-1)%12;
	$datey=($datem==12)?($datepy-1):$datepy;
	$datep="1/".$datem."/".$datey;
	$datepts=f_date2timestamp($datep);

	$datem=1+($datepm-1+12+1)%12;
	$datey=($datem==1)?($datepy+1):$datepy;
	$dates="1/".$datem."/".$datey;
	$datests=f_date2timestamp($dates);
	
	$daten=preg_replace(":(^|/)0:","$1",$daten);
	$datea=preg_replace(":(^|/)0:","$1",$datea);
	$datep=preg_replace(":(^|/)0:","$1",$datep);
	$dates=preg_replace(":(^|/)0:","$1",$dates);
	return(array(
		"nowms"=>$daten,"nowts"=>$datents,
		"actms"=>$datea,"actts"=>$dateats,
		"prems"=>$datep,"prets"=>$datepts,
		"suims"=>$dates,"suits"=>$datests,
	));
}
?>
