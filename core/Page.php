<?php
/**
 * @deprecated use Controller instead
 */
abstract class Page {
	private $content = array();
	private $title = array();
	
	private $errors = array();
	private $confirm = array();
	private $infos = array();
	
	public function __construct($mixed = null) {
		//appel du controller de la classe fille
		$this->controller($mixed);
		//r�cup�ration du nom de la classe fille
		$subClass = get_class($this);
		//le but est ici d'ajouter le r�pertoire view avant le nom de la vue
		$pathArray = explode("_",$subClass);
		$fileName = $pathArray[count($pathArray)-1];
		$pathArray[count($pathArray)-1] = 'view';
		$pathArray[] = $fileName;
		
		$newPathArray = array();
		foreach ($pathArray as $val){
			$newPathArray[] = firstchartolower($val);
		}
		
		$path = 'class/'.implode('/',$newPathArray).'.phtml';
		//enfin on importe, si le PHTML a �t� d�fini
		if(file_exists($path)){
			ob_start();
			require $path;
			$file = ob_get_clean();
			$this->addElement('content', $file);
		}
	}
	
	//oblig� de mettre un param $mixed pour les pages appel�es avec un ou des param�tres
	public abstract function controller($mixed) ;
	
	public function get($glue = "\n"){
		return implode($glue, $this->content);
	}
	

	/**
	 * Ajoute du contenu dans un element particulier
	 * @param String $var l'element
	 * @param String $content le contenu
	 * @param boolean $end indique si on ajoute le contenu � la fin du contenu de l'element ou pas
	 */
	public function addElement($var,$content,$end=true) {
		if($end) {
			array_push($this->$var, $content);
		}
		else {
			array_unshift($this->$var,$content);
		}
	}
	
	/**
	 * Ajoute du contenu dans l'element contenu
	 * Equivaut � addElement('content', $content,$end)
	 * @param String $content le contenu
	 * @param boolean $end indique si on ajoute le contenu � la fin de l'element contenu ou pas
	 */
	public function add($content,$end=true) {
		$this->addElement('content', $content,$end);
	}
	
	
	public function addTitle($title,$end=true) {
		$this->addElement('title', $title,$end);
	}
	
	public function getTitle($glue = ' - '){
		if($glue !== false) {
			return implode($glue, $this->title);
		}
		else {
			return $this->title;
		}
	}
	
	public function getMessageErrors($glue = '<br/>'){
		if($glue !== false) {
			return implode($glue, $this->errors);
		}
		else {
			return $this->errors;
		}
	}
	public function getMessageConfirm($glue = '<br/>'){
		if($glue !== false) {
			return implode($glue, $this->confirm);
		}
		else {
			return $this->confirm;
		}
	}
	public function getMessageInfos($glue = '<br/>'){
		if($glue !== false) {
			return implode($glue, $this->infos);
		}
		else {
			return $this->infos;
		}
	}
}