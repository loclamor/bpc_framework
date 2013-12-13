<?php

class Site {
	
	private static $instance = null;
	
	private $title = array();
	private $filariane = array();
	
	/**
	 * @var DOMDocument
	 */
	private $DOMHead = null;
	/**
	 * @var DOMDocument
	 */
	private $DOMMenu = null;
	/**
	 * @var DOMDocument
	 */
	private $DOMContent = null;
	/**
	 * @var DOMDocument
	 */
	private $DOMFoot = null;
	
	private $errors = array();
	private $confirm = array();
	private $infos = array();
	
	/**
	 * @var Logger
	 */
	private $log;
	
	private $microtimeStart = 0;
	private $microtimeEnd = 0;
	
	/**
	 * @var Bdmap_Utilisateur
	 */
	public $user = null;
	private $user_connected = false;
	
	public $page = null;
	public $couleur;
	
	public $controller;
	public $action;
	
	public function getController() {
		return $this->controller;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public static function getInstance( $controller = null, $action = null ) {
		if(is_null(self::$instance)) {
		self::$instance = new Site( $controller, $action );
		}
		return self::$instance;
	}
	
	/**
	 * Constructeur, gère l'appel des controllers, etc
	 */
	private function __construct( $controller = null, $action = null )
	{
		
		if( !is_null( $controller ) ) {
			$this->controller = $controller;
			if( !is_null( $action ) ) {
				$this->action = $action;
			}
			else if( isset ( $_GET['action'] ) and !empty ( $_GET['action'] ) ) {
				$this->action = $_GET['action'];
			}
			else {
				$this->action = DEFAULT_ACTION;
			}
		}
		else if( isset ( $_GET['controller'] ) and !empty ( $_GET['controller'] ) ) {
			$this->controller = $_GET['controller'];
			if( isset ( $_GET['action'] ) and !empty ( $_GET['action'] ) ) {
				$this->action = $_GET['action'];
			}
			else {
				$this->action = DEFAULT_ACTION;
			}
		}
		else {
			$this->controller = DEFAULT_CONTROLLER;
			$this->action = DEFAULT_ACTION;
		}
		
		$controllerClass = "Controller_".firstchartoupper($this->controller);
		$ObjController = new $controllerClass();
		$ret = $ObjController->getAction( $this->action );
		$this->addContent( $ret, strtolower($this->controller) . "-" . strtolower($this->action), 'div', array("class" => "container") );
		//chargement des plugins
		if($dossier = opendir(PLUGINS_FOLDER)){
			while(false !== ($fichier = readdir($dossier))){
				if($fichier != '.' && $fichier != '..'){
					if($ss_dossier = opendir(PLUGINS_FOLDER . '/'.$fichier)){
						$rep = $fichier;
						//c'est un r�pertoire de site
						while(false !== ($fichier = readdir($ss_dossier))){
							if($fichier != '.' && $fichier != '..' && is_file(PLUGINS_FOLDER . '/' . $rep . '/' .$fichier)){
								$class = getClassNameFromPath(PLUGINS_FOLDER . '/' . $rep . '/' .$fichier);
								$class = new $class;
								$class->setDomContent($this->DOMContent);
								$class->exec();
							}
						}
					}
					else {
						if(is_file(PLUGINS_FOLDER . '/' . $fichier)){
							//c'est un fichier
							$class = getClassNameFromPath(PLUGINS_FOLDER . '/' .$fichier);
							$class = new $class;
							$class->setDomContent($this->DOMContent);
							$class->exec();
						}
					}
				}
			}
		}

	}
	
	//public abstract function construct();
	
	/**
	 * retourne le contenu de l'element title pour affichage
	 * @param String $impl [optional] la chaine de liaison
	 */
	public function getTitle($impl = ' - ') {
		return implode($impl,$this->title);
	}
	
	/**
	 * retourne le contenu de l'element head pour affichage
	 * 
	 */
	public function getHead() {
		if(is_null($this->DOMHead)){   	
        	$this->DOMHead = new DOMDocument;
		}
		return htmlspecialchars_decode($this->DOMHead->saveHTML());
	}
	
	/**
	 * retourne le contenu de l'element menu pour affichage
	 * 
	 */
	public function getMenu() {
		if(is_null($this->DOMMenu)){   	
        	$this->DOMMenu = new DOMDocument;
		}
		return htmlspecialchars_decode($this->DOMMenu->saveHTML());
	}
	
	/**
	 * retourne le contenu de l'element fil d'ariane pour affichage
	 * @param String $impl [optional] la chaine de liaison
	 */
	public function getFilAriane($impl = ' > ') {
		return implode($impl,$this->filariane);
	}
	
	/**
	 * retourne le contenu de l'element content pour affichage
	 * 
	 */
	public function getContent() {
		if(is_null($this->DOMContent)){   	
        	$this->DOMContent = new DOMDocument;
		}
		return htmlspecialchars_decode($this->DOMContent->saveHTML());
	}
	
	/**
	 * retourne le contenu de l'element foot pour affichage
	 * 
	 */
	public function getFoot() {
		if(is_null($this->DOMFoot)){   	
        	$this->DOMFoot = new DOMDocument;
		}
		return htmlspecialchars_decode($this->DOMFoot->saveHTML());
	}
	
	/**
	 * retourne le contenu de l'element errors pour affichage
	 * @param String $impl [optional] la chaine de liaison
	 */
	public function getMessageErrors($impl = '<br/>') {
		return implode($impl,$this->errors);
	}
	
	/**
	 * retourne le contenu de l'element confirm pour affichage
	 * @param String $impl [optional] la chaine de liaison
	 */
	public function getMessageConfirm($impl = '<br/>') {
		return implode($impl,$this->confirm);
	}
	
	/**
	 * retourne le contenu de l'element infos pour affichage
	 * @param String $impl [optional] la chaine de liaison
	 */
	public function getMessageInfos($impl = '<br/>') {
		return implode($impl,$this->infos);
	}
	
	/**
	 * Ajoute du contenu dans un element
	 * @param string $var (title, head, content, foot, menu) l'element
	 * @param string $content le contenu
	 * @param boolean $end [optional] le contenu est il ajouté en fin de l'element ?
	 */
	public function addElement($var,$content,$end=true) {
		
		switch($var) {
			case 'head':
			case 'menu':
			case 'content':
			case 'foot':
				$this->addContent('<pre>Tentative d\'ajout dans '.$var.'</pre>', uniqid('err'));
				break;
			default :
				if($content != ""){
					if($end) {
						if(!is_array($content)) {
							array_push($this->$var, $content);
						}
						else {
							foreach ($content as $cont) {
								array_push($this->$var, $cont);
							}
						}
					}
					else {
						if(!is_array($content)) {
							array_unshift($this->$var,$content);
						}
						else {
							$content = array_reverse($content);
							foreach ($content as $cont) {
								array_unshift($this->$var, $cont);
							}
						}
					}
				}
		}
	}
	
	/** 
	 * Ajoute du HTML textuel au DOM du Hearder
	 * @param string $contentToAdd le HTML a rajouter
	 * @param string $idToAppend l'ID de l'element auquel sera accroch� le HTML 
	 * @param string $baseBalise la balise de base du DOM (utilis� selement � la cr�ation du DOM/premier appel de la fonction)
	 * @param array $attributes un tableau d'attributs � ajouter � la balise de base
	 */
	public function addHead($contentToAdd, $idToAppend = "root_head", $baseBalise = "div", $attributes = null) {
		$this->addXml('DOMHead', $contentToAdd, $idToAppend, $baseBalise, $attributes);
	}
	/** 
	 * Ajoute du HTML textuel au DOM du Menu
	 * @param string $contentToAdd le HTML a rajouter
	 * @param string $idToAppend l'ID de l'element auquel sera accroch� le HTML 
	 * @param string $baseBalise la balise de base du DOM (utilis� selement � la cr�ation du DOM/premier appel de la fonction)
	 * @param array $attributes un tableau d'attributs � ajouter � la balise de base
	 */
	public function addMenu($contentToAdd, $idToAppend = "root_menu", $baseBalise = "div", $attributes = null) {
		$this->addXml('DOMMenu', $contentToAdd, $idToAppend, $baseBalise, $attributes);
	}
	/** 
	 * Ajoute du HTML textuel au DOM du Content
	 * @param string $contentToAdd le HTML a rajouter
	 * @param string $idToAppend l'ID de l'element auquel sera accroch� le HTML 
	 * @param string $baseBalise la balise de base du DOM (utilis� selement � la cr�ation du DOM/premier appel de la fonction)
	 * @param array $attributes un tableau d'attributs � ajouter � la balise de base
	 */
	public function addContent($contentToAdd, $idToAppend = "root_content", $baseBalise = "div", $attributes = null) {
		$this->addXml('DOMContent', $contentToAdd, $idToAppend, $baseBalise, $attributes);
	}
	/** 
	 * Ajoute du HTML textuel au DOM du Foot
	 * @param string $contentToAdd le HTML a rajouter
	 * @param string $idToAppend l'ID de l'element auquel sera accroch� le HTML 
	 * @param string $baseBalise la balise de base du DOM (utilis� selement � la cr�ation du DOM/premier appel de la fonction)
	 * @param array $attributes un tableau d'attributs � ajouter � la balise de base
	 */
	public function addFoot($contentToAdd, $idToAppend = "root_foot", $baseBalise = "div", $attributes = null) {
		$this->addXml('DOMFoot', $contentToAdd, $idToAppend, $baseBalise, $attributes);
	}
	
	/**
	 * Ajoute du Xml � une variable DOM de la classe.
	 * Cr�e le DOM si besoin grace au param�tre $baseBalise avec comme ID $idToAppend et comme autres attributs $attributes
	 * @param string $siteVar nom de la variable DOM
	 * @param string $contentToAdd le HTML a rajouter
	 * @param string $idToAppend l'ID de l'element auquel sera accroch� le HTML 
	 * @param string $baseBalise la balise de base du DOM (utilis� selement � la cr�ation du DOM/premier appel de la fonction)
	 * @param array $attributes un tableau d'attributs � ajouter � la balise de base
	 */
	public function addXml($siteVar, $contentToAdd, $idToAppend = "root", $baseBalise = "div", $attributes = null) {

		if(is_null($this->$siteVar)){
			$this->$siteVar = new DOMDocument("1.0","UTF-8");
	        $this->$siteVar->formatOutput = false;
			$this->$siteVar->resolveExternals = true;
			
	        $elt = $this->$siteVar->createElement($baseBalise); 
	        $elt->setAttribute('id', $idToAppend);
	        $elt->setIdAttribute('id', true);
	        if(!is_null($attributes)) {
				foreach ($attributes as $attName => $attValue) {
		        	$elt->setAttribute($attName,$attValue);
		        }
	        }
			$this->$siteVar->appendChild($elt);
		}
		
		$contentToAdd = '<?xml version="1.0" encoding="UTF-8"?>
			<!DOCTYPE root [
			<!ENTITY copy "&#169;">
			<!ENTITY ecirc "&#234;">
			<!ENTITY eacute "&#233;">
			<!ENTITY egrave "&#232;">
			]>
			'.$contentToAdd.'
			';
		
		if($myNode = @DOMDocument::loadXml($contentToAdd)){
			//c'est du HTML			
			if($myNode instanceof DOMDocument){
				$myNode = $myNode->documentElement;
			}
			//$this->setIds($myNode);
			//import du node sur DOMDocument
			//attention, retourne une copie du node import�, copie qui elle appartient au DOMDocument
			$myNode = $this->$siteVar->importNode($myNode,true);
			//ensuite (car il faut �tre sur le DOMDocument final pour que ce soit pris en comtpe), on set l'ID
			//TODO : id�alement, il faudrait parcourir tous les sous elements pour retrouver les IDs...
			
			try {
				$myNode->setIdAttribute('id', true);
				
				
			} catch (Exception $e) {
				//debug("cannot set Id attribute");
			}
			
			
		}
		else {
			//c'est du texte simple
			$myNode = $this->$siteVar->createTextNode(str_replace("]>", "", $contentToAdd));
		}
		//on ajoute notre elt
		if($idToAppend !== null){
			$eltToAppend = $this->$siteVar->getElementById($idToAppend);
			
            if(!$eltToAppend){
            	$this->$siteVar->documentElement->appendChild($myNode);
            }
            else {
           		try {
           			$eltToAppend->appendChild($myNode);
           		} catch (Exception $e) {    
           			$this->$siteVar->documentElement->appendChild($myNode);
           		}
            }
		}
		else {
			debug("no id specified");
			$this->$siteVar->documentElement->appendChild($myNode);
		}
	}
	
	/**
	 * ajoute une Page $page au contenu DOMContent
	 * @param Page $page
	 * @param string $idToAppend l'id sur lequel on vas attacher le contenu de la page
	 * @param string $setBaseBalise si a TRUE, le contenu de la page sera englob� dans une $baseBalise d'ID le nom de la class de $page avec les attributs $baseAttributes
	 * @param string $baseBalise type de la balise englobante si $setBaseBalise===TRUE
	 * @param array $baseAttributes attributs de la balise englobante si $setBaseBalise===TRUE. Ne doit pas contenir l'attibut ID
	 */
	public function addPage(Page $page, $idToAppend = 'root_content', $setBaseBalise = true, $baseBalise = "div", $baseAttributes = null) {
		if(is_null($baseAttributes)){
			$baseAttributes = array();
		}
		$content = $page->get();
		if($setBaseBalise===true) {
			$baseBaliseId = get_class($page);
			$attributes = array();
			foreach ($baseAttributes as $attName => $attValue){
				$attributes[] = $attName."='".$attValue."'";
			}
			$attributes = implode(" ", $attributes);
			$content = "<".$baseBalise." id='".$baseBaliseId."' ".$attributes." >".$content."</".$baseBalise.">";
		}
		
		$this->addContent($content, $idToAppend);
		
		$this->addElement('title', $page->getTitle());
		$this->addElement('errors', $page->getMessageErrors());
		$this->addElement('confirm', $page->getMessageConfirm());
		$this->addElement('infos', $page->getMessageInfos());
	}
}

