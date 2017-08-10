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
	
	public function log($msg) {
		$this->log->setBaseString($this->actionName . " : ");
		$this->log->log('controllers', $this->className, $this->loggerBaseString . " " . $msg, Logger::GRAN_MONTH);
	}
	
	public function getAction( $action ){
		$action = firstchartolower( $action );
		$this->actionName = $action;
		
		$subClass = get_class($this);
		$this->className = $subClass;

		$this->$action();
		
		
		$simpleName = firstchartolower( str_replace( "Controller_", "", $subClass ) );
		$this->controllerName = $simpleName;
		$pathView = BPCF_ROOT."/view/".$simpleName."/".$action.".phtml";
		
		$content = "";
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
            echo $content;
            die();
		}

		return '<div id="'.$simpleName.'-'.$action.'" >'.$content.'</div>';
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
