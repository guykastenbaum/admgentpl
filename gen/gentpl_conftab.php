<?php
/* gentpl fixed config array */
$tbtypes=array(
	"id" => array("size" => 10, "colwidth" => 40, "list" => 0,"typebdd"=>"int","flag_search"=>0,"openpopup"=>2),
	"text" => array(
		"size" => 30, "colwidth" => 300, "list" => 1,"menu"=>null,"typebdd"=>"text",
		"postfix"=>"","affix"=>"","flag_href"=>0,"href_extra"=>"",
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"fk_table"=>null,"fk_id"=>null,"fk_label"=>null,
		"fk_href"=>"{fk_table}.php?action=edit&{fk_id}=[!{fk_id}!]",
		"flag_search"=>0,"editcode"=>"","flag_noedit"=>0,
		"menu_javascript"=>null,"menu_tabul"=>null,
		),
	"longtext" => array(
		"typebdd"=>"text","rows" => 3,"cols" => 30,"editcode"=>"","flag_noedit"=>0,
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"size"=>16000, "colwidth" => 300, "list" => 0,"menu"=>null,"flag_search"=>0),
	"html" => array(
		"typebdd"=>"text","height" => 400,"width" => 600,"flag_noedit"=>1,
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"size"=>16000, "colwidth" => 300, "list"=>0, "flag_search"=>0),
	"photo" => array(
		"typebdd"=>"text","list" => 0,"dir"=>'',"name_format"=>'',"resize"=>"brw=150,mrw=280,hrw=500",
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"affix"=>"","editcode"=>"","flag_noedit"=>0,),
	"file" => array(
		"typebdd"=>"file","list" => 0,"dir"=>'',"name_format"=>'',
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"affix"=>"","editcode"=>"","flag_noedit"=>0,),
	"int" => array(
		"typebdd"=>"int","size" => 10, "colwidth" => 80, "list" => 1,"menu"=>null,
		"postfix"=>"","affix"=>"","flag_href"=>0,"type_href"=>0,"flag_search"=>0,
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","editcode"=>"","flag_noedit"=>0,
		"fk_table"=>null,"fk_id"=>null,"fk_label"=>null,
		"fk_href"=>"{fk_table}.php?action=edit&{fk_id}=[!{fk_id}!]",
		),
	"date" => array(
		"typebdd"=>"date","size" => 10, "colwidth" => 150, "list" => 1,"flag_search"=>0,"flag_noedit"=>1,
				"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		),
	"datetime" => array(
		"typebdd"=>"date","size" => 10, "colwidth" => 150, "list" => 1,"flag_search"=>0,"flag_noedit"=>1,
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		),
	"hidden" => array(
		"typebdd"=>"null","size" => 0, "list" => 0,"postfix"=>"",
		"flag_href"=>0,"href_extra"=>"","editcode"=>"","flag_noedit"=>1,
		"href"=>"{tablename}.php?action=edit&{tbid}=[!{tbid}!]","type_href"=>1,
		"search"=>"",
		),
	);
$tbtypes_help=array(
	"id" => "",
	"size" => "taille en base de donnnee",
	"colwidth" => "largeur de la colonne (TD) à afficher",
	"list" => "ce champ est il dans l'affichage liste",
	"text" => "",
	"menu" => "choix sous forme de menu fixe 'key:val;key:val' ou sql 'select key,val from table'",
	"postfix" => "libellé à afficher après la valeur",
	"affix" => "contenu à afficher en remplacement de la valeur, <br>Ex: &lt;img src='/img/flag-actif-{ champ }.gif'&gt;",
	"flag_href" => "affiche t on le lien href sur la liste",
	"href_extra" => "extra dans le tag href (onclick= ou target=)",
	"href" => "lien href (en general vers l'edition de fiche)",
	"type_href" => "type de lien : href, popup, ajax",
	"fk_table" => "nom de la table distante",
	"fk_id" => "nom du champ id de la table distante",
	"fk_label" => "nom du champ à afficher de la table distante",
	"fk_href" => "href à afficher, variables entre [ ! ! ]",
	"longtext" => "type text/memo",
	"rows" => "nombre de rangs à afficher",
	"cols" => "nombre de colonness à afficher",
	"html" => "type texte-long à afficher avec fckeditor",
	"photo" => "type varchar contenant l'adresse de la photo",
	"dir" => "répertoire préfixant le chemin de la photo",
	"name_format" => "format du nom de la photo, texte ou sql 
		<br> Ex texte: prd_rub/prd_nom-prd_id
		<br> Ex sql: select concat(rub,'/',article_nom,'/',photo_id) from photo where photo_id={tbid}",
	"resize" => "resizing automatique de la photo brw=150 ".
		"signifie que photo_br.jpg aura une largeur de 150px, ".
		"syntaxe FF[hw]=nnn FF:préfixe,hw:hauteur ou largeur, ".
		"on peut avoir brh=120,brw=100 la photo sera resizée pour tenir dans ce rectangle",
	"int" => "",
	"date" => "",
	"datetime" => "",
	"hidden" => "champ non affiché",
	"menu_javascript"=>"javascript exécuté sur un menu",
	"menu_tabul"=>"tabulation utilisée dans les menus hierarchiques",
	"search"=>"requete sql 'key:(sql);key:(sql)'",
	);
$type_edit=array(
	"type" => array("id" => "id","text" => "text","longtext" => "longtext","html"=>"html","photo"=>"photo","int" => "int","date" => "date","file"=>"file","hidden" => "hidden"),
	"typeaff" => array("text" => "text","longtext" => "longtext","html"=>"html","photo"=>"photo","int" => "int","date" => "date","file"=>"file","hidden" => "hidden"),
	"typebdd" => array("text" => "text","int" => "int","date" => "date","file"=>"file","null" => "null"),
	"type_href" => array(0 => "aucun",1 => "lien href",2=>"popup",3=>"pwc ajax"),
	"openpopup" => array(0 => "lien href",1 => "lien href",2=>"popup window",3=>"pwc ajax"),
	"label" => "text",
	"default" => "text",
	"flag" => "flag",
	"list" => "flag",
	"longtext" => "html",
	"html"=>"html",
	"photo"=>"photo",
	"pagination_pag" => "int",
	"orderby" => "text",
	"href" => "text",
	"fk_href" => "text",
	"len" => "int",
	"size" => "int",
	"editcode"=>"html",
	"affix"=>"html",
	"menu_javascript"=>"text",
	"menu_tabul"=>"text",
	);
//--------------------EDITION DE CODE SPECIFIQUE--------------------------------------------------------------------------
$t_codespec=array("inc","head","headp","loop","endp","end","headf","loopf","endf","search");
//--------------------MORCEAUX D'HTML--------------------------------------------------------------------------
$t_morchtml=array("1_begin", "2_list", "3_form", "4_search", "5_new", "6_multi", "7_end");

?>