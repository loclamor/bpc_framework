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
	
	public function getAction( $action ){
		$action = firstchartolower( $action );
		$this->$action();
		
		$subClass = get_class($this);
		$simpleName = firstchartolower( str_replace( "Controller_", "", $subClass ) );
		$pathView = "view/".$simpleName."/".$action.".phtml";
		
		$content = "";
		if( file_exists( $pathView ) ){
			ob_start();
			require $pathView;
			$content = ob_get_clean();
		}
		
		if( $this->isJSON ) {
                    if( $this->allowAllOrigin )
                        header("Access-Control-Allow-Origin: *");
                    header('Content-type: application/json; charset=utf-8');
                    echo $content;
                    die();
		}

		return '<div id="'.$simpleName.'-'.$action.'" >'.$content.'</div>';
	}
	
}

?>
