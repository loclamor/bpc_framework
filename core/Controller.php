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

		return '<div id="'.$simpleName.'-'.$action.'" >'.$content.'</div>';
	}
	
}

?>
