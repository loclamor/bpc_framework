<?php
/**
 * affiche le contenu d'une variable � des fins de debogage
 * @param var $debug
 */
function debug($debug,$force = false){
	if(FORCE_DEBUG or $force){
		echo '<pre>';
		//print_r($debug);
		var_dump($debug);
		echo '</pre>';
	}
}

function concat($str1,$str2) {
	return $str1.$str2;
}

/**
 * importe un fichier
 * exemples :
 * import("package.ClassName");
 * import("another.package.*");
 * @param strigf $path
 */
function import($path=""){
	if($path == ""){ //no parameter returns the file import info tree;
		$report = $_SESSION['imports'];
		foreach($report as &$item) $item = array_flip($item);
		return $report;
	}

	$current = str_replace("\\","/",getcwd())."/";
	$path = $current.str_replace(".","/",$path);
	if(substr($path,-1) != "*") $path .= ".php";

	$imports = &$_SESSION['imports'];
	if(!is_array($imports)) $imports = array();

	$control = &$imports[$_SERVER['SCRIPT_FILENAME']];
	if(!is_array($control)) $control = array();

	foreach(glob($path) as $file){
		$file = str_replace($current,"",$file);
		if(is_dir($file)) import($file.".*");
		if(substr($file,-4) != ".php") continue;
		//if($control[$file]) continue;
		$control[$file] = count($control);
		require_once($file);
		//echo $file . '<br/>';
	}
}

function redirect($url) {
	if($url !== false)
		header('Location: '.$url);
}

//remplace le bbcode par le code html correspondant
function code($texte)
{
	//Smileys
	$texte = str_replace('/(', '<img src="../images/smiley/381.gif" alt="/(" title="/(" class="smiley" />', $texte);
	$texte = str_replace('#snif#', '<img src="../images/smiley/3523.gif" alt="snif" title="snif" class="smiley" />', $texte);
	$texte = str_replace('#love#', '<img src="../images/smiley/3525.gif" alt="love" title="love" class="smiley" />', $texte);
	$texte = str_replace('#noel#', '<img src="../images/smiley/3526.gif" alt="noel" title="noel" class="smiley" />', $texte);
	$texte = str_replace('8)', '<img src="../images/smiley/3657.png" alt="8)" title="8)" class="smiley" />', $texte);
	$texte = str_replace('+o(', '<img src="../images/smiley/3692.gif" alt="+o(" title="+o(" class="smiley" />', $texte);
	$texte = str_replace(':p', '<img src="../images/smiley/3781.gif" alt=":p" title=":p" class="smiley" />', $texte);
	$texte = str_replace(':-p', '<img src="../images/smiley/3781.gif" alt=":p" title=":p" class="smiley" />', $texte);
	$texte = str_replace('#ga#', '<img src="../images/smiley/3891.png" alt="ga" title="ga" class="smiley" />', $texte);
	$texte = str_replace('#ill#', '<img src="../images/smiley/4440.png" alt="ill" title="ill" class="smiley" />', $texte);
	$texte = str_replace('#win#', '<img src="../images/smiley/4555.png" alt="win" title="win" class="smiley" />', $texte);
	$texte = str_replace('#toro#', '<img src="../images/smiley/5034.gif" alt="toro" title="toro" class="smiley" />', $texte);
	$texte = str_replace('o_O', '<img src="../images/smiley/blink.gif" alt="o_O" title="o_O" class="smiley" />', $texte);
	$texte = str_replace('oO', '<img src="../images/smiley/blink.gif" alt="o_O" title="o_O" class="smiley" />', $texte);
	$texte = str_replace(';)', '<img src="../images/smiley/clin.png" alt=";)" title=";)" class="smiley" />', $texte);
	$texte = str_replace(';-)', '<img src="../images/smiley/clin.png" alt=";)" title=";)" class="smiley" />', $texte);
	$texte = str_replace('#zz#', '<img src="../images/smiley/dort3pl.gif" alt="zz" title="zz" class="smiley" />', $texte);
	$texte = str_replace(':D', '<img src="../images/smiley/heureux.png" alt=":D" title=":D" class="smiley" />', $texte);
	$texte = str_replace(':-D', '<img src="../images/smiley/heureux.png" alt=":D" title=":D" class="smiley" />', $texte);
	$texte = str_replace(':d', '<img src="../images/smiley/heureux.png" alt=":D" title=":D" class="smiley" />', $texte);
	$texte = str_replace(':-d', '<img src="../images/smiley/heureux.png" alt=":D" title=":D" class="smiley" />', $texte);
	$texte = str_replace('^^', '<img src="../images/smiley/hihi.png" alt="^^" title="^^" class="smiley" />', $texte);
	$texte = str_replace(':o', '<img src="../images/smiley/huh.png" alt=":o" title=":o" class="smiley" />', $texte);
	$texte = str_replace('#fouet#', '<img src="../images/smiley/lala_final.gif" alt="fouet" title="fouet" class="smiley" />', $texte);
	$texte = str_replace(':P', '<img src="../images/smiley/langue.png" alt=":p" title=":p" class="smiley" />', $texte);
	$texte = str_replace(':-P', '<img src="../images/smiley/langue.png" alt=":p" title=":p" class="smiley" />', $texte);
	$texte = str_replace('X(', '<img src="../images/smiley/pinch.png" alt="X)" title="X)" class="smiley" />', $texte);
	$texte = str_replace('#prison#', '<img src="../images/smiley/prison2.gif" alt="prison" title="prison" class="smiley" />', $texte);
	$texte = str_replace('#haha#', '<img src="../images/smiley/rire.gif" alt="haha" title="haha" class="smiley" />', $texte);
	$texte = str_replace('#siffle#', '<img src="../images/smiley/siffle.png" alt="siffle" title="siffle^" class="smiley" />', $texte);
	$texte = str_replace(':-�', '<img src="../images/smiley/siffle.png" alt="siffle" title="siffle^" class="smiley" />', $texte);
	$texte = str_replace(':)', '<img src="../images/smiley/smile.png" alt=":)" title=":)" title=":)" class="smiley" />', $texte);
	$texte = str_replace(':-)', '<img src="../images/smiley/smile.png" alt=":)" title=":)" title=":)" class="smiley" />', $texte);
	$texte = str_replace(':(', '<img src="../images/smiley/triste.png" alt=":(" title=":(" class="smiley" />', $texte);
	$texte = str_replace(':-(', '<img src="../images/smiley/triste.png" alt=":(" title=":(" class="smiley" />', $texte);
	$texte = str_replace('O_o', '<img src="../images/smiley/unsure.gif" alt="O_o" title="O_o" class="smiley" />', $texte);
	$texte = str_replace('Oo', '<img src="../images/smiley/unsure.gif" alt="O_o" title="O_o" class="smiley" />', $texte);
	$texte = str_replace('#waw#', '<img src="../images/smiley/waw.png" alt="waw" title="waw" class="smiley" />', $texte);
	$texte = str_replace('OO', '<img src="../images/smiley/waw.png" alt="waw" title="waw" class="smiley" />', $texte);
	$texte = str_replace('XD', '<img src="../images/smiley/XD2.gif" alt="XD" title="XD" class="smiley" />', $texte);
	$texte = str_replace('xd', '<img src="../images/smiley/XD2.gif" alt="XD" title="XD" class="smiley" />', $texte);

	//Mise en forme du texte
	//gras : b || g
	$texte = preg_replace('`\[b\](.+)\[/b\]`isU', '<strong>$1</strong>', $texte); 
	$texte = preg_replace('`\[g\](.+)\[/g\]`isU', '<strong>$1</strong>', $texte); 
	//italique : i
	$texte = preg_replace('`\[i\](.+)\[/i\]`isU', '<em>$1</em>', $texte);
	//soulign� : u
	$texte = preg_replace('`\[u\](.+)\[/u\]`isU', '<u>$1</u>', $texte);
	//barr� : s
	$texte = preg_replace('`\[s\](.+)\[/s\]`isU', '<strike>$1</strike>', $texte);
	//grandes lettres : gl
	$texte = preg_replace('`\[gl\](.+)\[/gl\]`isU', '<big>$1</big>', $texte);
	//petites lettres : pl
	$texte = preg_replace('`\[pl\](.+)\[/pl\]`isU', '<small>$1</small>', $texte);
	//taille : size=x
	$texte = preg_replace('`\[size=(.+)\]`isU', '<span style="font-size: $1px;">', $texte);
	$texte = str_replace('[/size]', '</span>', $texte);
	//t�l�texte : tt
	$texte = preg_replace('`\[tt\](.+)\[/tt\]`isU', '<tt>$1</tt>', $texte);
	// citation
	$texte = preg_replace('`\[citation=(.+)\](.+)`isU', '<fieldset class="citation"><legend>Citation : $1</legend><tt>$2', $texte);
	$texte = str_replace('[/citation]', '</tt></fieldset>', $texte);
	//centr� : ct || center
	$texte = preg_replace('`\[ct\](.+)\[/ct\]`isU', '<center>$1</center>', $texte);
	$texte = preg_replace('`\[center\](.+)\[/center\]`isU', '<center>$1</center>', $texte);
	//couleur  : color=x
	$texte = preg_replace('`\[color=(.+)\]`isU', '<span style="color: $1;">', $texte);
	$texte = str_replace('[/color]', '</span>', $texte);
	//lien : url || url=x
	//$texte = preg_replace('#http://[a-z0-9._/-]+#i', '<a href="$0">$0</a>', $texte);
	$texte = preg_replace('`\[url\](.+)\[/url\]`isU', '<a href="$1" target="_blank">$1</a>', $texte);
	$texte = preg_replace('`\[url=(.+)\]`isU', '<a href="$1" target="_blank">', $texte);
	//$texte = preg_replace('`\[/url\]`isU', '</a>', $texte);
	$texte = str_replace('[/url]', '</a>', $texte);
	//image
	$texte = preg_replace('`\[img\](.+)\[/img\]`isU', '<img src="$1" />', $texte);
	//tableaux
		//nouveau tableau
		$texte = preg_replace('`\[t\](.+)\[/t\]`isU', '<table>$1</table>', $texte);
		//ligne tableau
		$texte = preg_replace('`\[l\](.+)\[/l\]`isU', '<tr>$1</tr>', $texte);
		//cellules titre tableau
		$texte = preg_replace('`\[h\](.+)\[/h\]`isU', '<th>$1</th>', $texte);
		//cellules tableau
		$texte = preg_replace('`\[d\](.+)\[/d\]`isU', '<td>$1</td>', $texte);


	return $texte;
}

function noSpecialChar($chaine){
 
    //  les accents
    $chaine=trim($chaine);
    $chaine= strtr($chaine,"�����������������������������������������������������","aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
 
    //  les carac�tres sp�ciaux (aures que lettres et chiffres en fait)
    $chaine = preg_replace('/([^.a-z0-9]+)/i', '_', $chaine);
    $chaine = strtolower($chaine);
 
    return $chaine;
}

/**
 * 
 * formate une chaine avec les premieres lettres de chaque mot en majuscule
 * @param $chaine String, la chaine � modifier
 * @param $delim Char, [optionel] le caract�re de d�limitation des mots
 * @return String, la chaine formatee
 */
function firstchartoupper($chaine,$delim = ' '){
	$words = explode($delim, $chaine);
	$not2upper = array('l', 'en', 'le', 'la', 'les', 'de', 'des', 'del', 'of', 'dei', 'delle', 'di');
	if(count($words)>1 || $delim == ' ')
	{
		$results = array();
		foreach($words as $w){
			$results[] = firstchartoupper(firstchartoupper(strtolower($w),'-'),'\'');
		}
		return implode($delim, $results);
		
		
	}
	else {
		$w = ($words[0]);
		if(strlen($w) > 2 && !in_array($w, $not2upper)) {
			$char = strtoupper(substr($w, 0, 1));
			if($char == '�' || $char == '&eacute;'){
				$char = '&Eacute;';
			}
			return $char.(substr($w, 1));
		}
		else {
			$not2lower = array('i', 'I', 'ii', 'II', 'iii', 'III', 'iv', 'IV', 'v', 'V', 'vi', 'VI',
								'vii', 'VII', 'viii', 'VIII', 'ix', 'IX', 'x', 'X');
			if(!in_array($w, $not2lower)){
				return $w;
			}
			else {
				return strtoupper($w);
			}
		}
	}
}

function firstchartolower($chaine){
	$char = strtolower(substr($chaine, 0, 1));
	return $char.(substr($chaine, 1));
}

/**
 * 
 * To bytes converter ...
 * @param $str String
 */
function toBytes($str){
    $val = trim($str);
    $last = strtolower($str[strlen($str)-1]);
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;        
    }
    return $val;
}
/**
 * Cr�e une nouvelle image redimentionn�e � partir de $urlImage
 * @param String $urlImage adresse locale de l'image d'origine
 * @param integer $resizeSize taille de redimentionnement
 * @param char $resizeType [optional] 'H' ou 'L' d�finit le type de redimention, c'est a dire si $resizeSize est une hauteur ('H') ou une largeur ('L')
 * @param String $newUrl [optional] l'adresse + nom de l'image en sortie
 * @return String url de la nouvelle image, par defaut de la forme {basename($urlImage)}.min{resizeType}{resizeSize}.{extention} ou false en cas d'erreur 
 */
function redimJPEG($urlImage,$resizeSize = 500, $resizeType = 'H', $newUrl = 'default'){
	if($imageOrigine = imagecreatefromjpeg($urlImage) or die('erreur')){
		$tailleOrigine = getimagesize($urlImage, $info);
		if($resizeType == 'L'){
			//la valeur donnee pour le redim. est la largeur
			$largeur = $resizeSize;
			$hauteur = ( ($tailleOrigine[1] * (($largeur)/$tailleOrigine[0])) );
		}
		elseif ($resizeType == 'H') {
			//la hauteur
			$hauteur = $resizeSize;
			$largeur = ( ($tailleOrigine[0] * (($hauteur)/$tailleOrigine[1])) );
		}
		else {
			return false;
		}
		
		$nouvelleImage = imagecreatetruecolor($largeur , $hauteur) or die ("Erreur");
		imagecopyresampled($nouvelleImage , $imageOrigine  , 0,0, 0,0, $largeur, $hauteur, $tailleOrigine[0],$tailleOrigine[1]);
		imagedestroy($imageOrigine);
		
		
		if($newUrl == 'default') {
			//$newUrl = $temp[0] . '_' . intval($largeur). 'x' . intval($hauteur) . '.' . $temp[1];
			$path_parts = pathinfo($urlImage);
			$newUrl = $path_parts['dirname'].'/'.$path_parts['filename'].'.min'.$resizeType.$resizeSize.'.'.$path_parts['extension'];
		}
		imagejpeg($nouvelleImage , $newUrl, 100);
	
		return $newUrl;
	}
	else {
		return false;
	}
}

function getImageDate($filename) {
	$datePV = false;
	getimagesize($filename, $info);
	$pattern = '([0-9]{4}\:[0-9]{2}\:[0-9]{2}|[0-9]{4}\/[0-9]{2}\/[0-9]{2}|[0-9]{4}\-[0-9]{2}\-[0-9]{2})';
	if(is_array($info) and isset($info['APP1'])) {
        preg_match($pattern, $info['APP1'],$match);
        if(is_array($match) and isset($match[0])){
        	$datePV = str_replace('/', '-', $match[0]);
	        $datePV = str_replace(':', '-', $datePV);
        }
	}
	return $datePV;
}

function html2rgb($color)
{
	if ($color[0] == '#')
	$color = substr($color, 1);

	if (strlen($color) == 6) {
		list($r, $g, $b) = array($color[0].$color[1],
		$color[2].$color[3],
		$color[4].$color[5]);
	}
	elseif (strlen($color) == 3) {
		list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1],   $color[2].$color[2]);
	}
	else {
		return false;
	}
	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array($r, $g, $b);
} 

function rgb2html($r, $g=-1, $b=-1)
{
	if (is_array($r) && sizeof($r) == 3) {
		list($r, $g, $b) = $r;
	}
	
	$r = intval($r);
	$g = intval($g);
	$b = intval($b);
	
	$r = dechex($r<0?0:($r>255?255:$r));
	$g = dechex($g<0?0:($g>255?255:$g));
	$b = dechex($b<0?0:($b>255?255:$b));
	
	$color = (strlen($r) < 2?'0':'').$r;
	$color .= (strlen($g) < 2?'0':'').$g;
	$color .= (strlen($b) < 2?'0':'').$b;
	
	return '#'.$color;
}

function assombrir($couleur,$qt){
	$type = 'rgb';
	if(!is_array($couleur)){
		$couleur = html2rgb($couleur);
		$type = 'html';
	}
	list($r, $g, $b) = array(
	($couleur[0]-$qt<0)?0:$couleur[0]-$qt,
	($couleur[1]-$qt<0)?0:$couleur[1]-$qt,
	($couleur[2]-$qt<0)?0:$couleur[2]-$qt);
	
	if($type == 'html') {
		return rgb2html($r, $g, $b);
	}
	else {
		return array($r, $g, $b);
	}
	
}

function eclaircir($couleur,$qt) {
	return assombrir($couleur,-1*$qt);
}

function remote_file_exists ( $url ) {
	ini_set('allow_url_fopen', '1');
	ini_set ('user_agent', $_SERVER['HTTP_USER_AGENT']); 
	if (@fclose(@fopen($url, 'r'))) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Retourne le bbcode de la photo pass�e en param�tre
 * @param Bdmap_Photo $photo la photo
 * @param boolean $encapsuleUrl [optional, default true] si � true, le bbcode de la photo est entour� du bbcode du lien de la photo
 */
function getBBCodeFromOnePhoto(Bdmap_Photo $photo, $encapsuleUrl = true, $resize=false){
	$bbcode = "";
	if($encapsuleUrl){
		$bbcode = "[url=".HOST_OF_SITE."/photo/".$photo->getUniqid().".jpg]\n";
	}
	
	if($resize) {
		$bbcode .= "    [img]".HOST_OF_SITE."/photo/".$photo->getUniqid().".minL800.jpg[/img]\n";
	}
	else {
		$bbcode .= "    [img]".HOST_OF_SITE."/photo/".$photo->getUniqid().".jpg[/img]\n";
	}
	
	if($encapsuleUrl){
		$bbcode .= "[/url]\n";
	}
	return $bbcode;
}

/**
 * retourne le BBCode pour un album donn�, c'est a dire le bbcode de toutes les photos entour� du lien de la visionneuse d'album
 * @param Bdmap_Album $album l'album
 */
function getBBCodeFromOneAlbum(Bdmap_Album $album, $photoEncapsuleUrl = false, $resize=false){
	$bbcode = "[url=".HOST_OF_SITE."/view/".$album->getUniqid().".html]\n";
	
	$photos = Gestionnaire::getGestionnaire('photo')->getOf(array('id_album'=>$album->getId()));
	foreach ($photos as $photo) {
		if($photo instanceof Bdmap_Photo){
			$bbcode .= getBBCodeFromOnePhoto($photo, $photoEncapsuleUrl, $resize);
		}
	}
	
	$bbcode .= "[/url]\n";
	return $bbcode;
}

function __autoload($className){
	$class = str_replace('_', '/', $className);
	$classArray = explode("/", $class);
	$newClassArray = array();
	$page = false;
	if(strtolower($classArray[0]) == "page" and count($classArray) > 1){
		$page = true;
	}
	foreach ($classArray as $val){
		$newClassArray[] = firstchartolower($val);
		
	}
	//pour les fichiers de classes autre que Page qui n'ont pas leur premi�re lettre en minuscule
	if(!$page){
		$newClassArray[count($newClassArray)-1] = $classArray[count($classArray)-1];
	}
	$classPath = implode("/", $newClassArray);
	if( file_exists( 'model/'.$classPath.'.php' ) ) {
		require_once 'model/'.$classPath.'.php';
	}
	elseif( file_exists( ''.$classPath.'.php' ) ) {
		require_once ''.$classPath.'.php';
	}
	elseif ( file_exists( BPCF.'/core/'.$classPath.'.php' ) ) {
		require_once BPCF.'/core/'.$classPath.'.php';
	}
	elseif ( file_exists( BPCF.'/'.$classPath.'.php' ) ) {
		require_once BPCF.'/'.$classPath.'.php';
	}
//	echo $className . " imported<br/>";
}

function emu_getallheaders() {
        foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
               $headers[$name] = $value;
           } else if ($name == "CONTENT_TYPE") {
               $headers["Content-Type"] = $value;
           } else if ($name == "CONTENT_LENGTH") {
               $headers["Content-Length"] = $value;
           }
       }
       return $headers;
    } 
    
    
function getClassNameFromPath($pathFile) {
	//bpcfv1/plugin/upload/mondophoto.php
	//Plugin_Upload_Mondophoto
	$path = str_replace(".php", "", $pathFile);
	$path = str_replace("./", "", $path);
	$path = str_replace(BPCF."/", "", $path);
	$temp = explode("/", $path);
	$className = array();
	foreach ($temp as $folder){
		$className[] = strtoupper(substr($folder, 0, 1)).(substr($folder, 1));
	}
	return implode("_", $className);
}