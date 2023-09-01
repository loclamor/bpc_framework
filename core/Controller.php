<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Controller
 *
 * @author loclamor
 */
abstract class Controller {
	
	public $isJSON = DEFAULT_JSON;
	public $allowAllOrigin = DEFAULT_ALLOW_ALL_ORIGIN;
	
	public $loggerBaseString = "";
	
	public $actionName = "default_action";
	public $className = "default_controller";
	public $controllerName = "default";
	
	private $site;
	
	public $scripts = array();
	
	public function __construct(){
		$this->log = new Logger('./logs', "");
	}
	
	public function log($msg, $logLevel = LOG_LEVEL) {
		if( $logLevel > LOG_LEVEL ) {
			return;
		}
		$this->log->setBaseString($this->actionName . " : ");
		$this->log->log('controllers', $this->className, $this->loggerBaseString . " " . $msg, Logger::GRAN_MONTH);
	}
	
	public function getAction( $_action ){
		$_action = firstchartolower( $_action );
		$this->actionName = $_action;
		
		$subClass = get_class($this);
		$this->className = $subClass;

		$simpleName = firstchartolower( str_replace( "Controller_", "", $subClass ) );
		$this->controllerName = $simpleName;
		$pathView = BPCF_ROOT."/view/".$simpleName."/".$_action.".phtml";

		$content = "";

		try {
			if(!method_exists($subClass, $_action)) {
				throw new Exception("l'action '$_action' du controller '$simpleName' n'existe pas !");
			}
			$content = $this->$_action();
		}
		catch (Exception $e) {
			throw new Exception($e->getMessage(), 0, $e);
		}
		
		if( file_exists( $pathView ) ){
			ob_start();
			require $pathView;
			$content = ob_get_clean();
		}
		
		if( $this->isJSON ) {
			ob_end_clean(); //efface le flux de sorti principal (car on ne veut que le json, pas les entêtes du HTML du fichier principal)
            if( $this->allowAllOrigin )
                header("Access-Control-Allow-Origin: *");
            header('Content-type: application/json; charset=utf-8');
            if(is_array($content)) {
            	$content = json_encode($content);
            }
            echo $content;
            die();
		}

		return '<div id="'.$simpleName.'-'.$_action.'" >'.$content.'</div>';
	}
	
	public function getScripts() {
		$scripts = "";
		foreach( $this->scripts as $s ) {
			$scripts .= '<script src="'.$s.'"></script>';
		}
		return '<div id="'.$this->controllerName.'-'.$this->actionName.'">'.$scripts.'</div>';
	}
	
	public function addScript( $s ) { 
		$this->scripts[] = $s;
	}
	
	public function setSite(Site $site) {
		$this->site = $site;
	}
	public function getSite() {
		return $this->site;
	}
	
}

?>
