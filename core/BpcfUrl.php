<?php
class BpcfUrl {
	
	public $params = array();
	public $base = '';
	
	/**
	 * @param clean bool url "propre"
	 */
	public function __construct($controller=null, $action=null, $parameters=array(), $base='') {
		if($controller != null) {
			$this->addParam('controller', $controller);
			if($action != null) {
				$this->addParam('action', $action);
			}
		}
		if(is_array($parameters)) {
			$this->addParams($parameters);
		}
		$this->base = $base;
	}
	
	/**
	 * @return string l' url
	 */
	public function getUrl() {
		$url = array();
		foreach ($this->params as $key => $value){
			if(!empty($key) && !is_null($key)) {
				$url[] = $key.'='.$value;
			} 
		}
		$baseUrl = implode('&', $url);
		return $this->base.'?'.$baseUrl;
	}
	
	/**
	 * ajoute un param�tre � l'url
	 */
	public function addParam($key, $value) {
		$this->params[$key] = $value;
	}
	
	/**
	 * ajoute des param�tres � l'url
	 */
	public function addParams(array $params){
		foreach ($params as $key => $value) {
			$this->addParam($key, $value);
		}
	}
	
	/**
	 * enl�ve un param�tre � l'url
	 */
	public function removeParam($key) {
		unset($this->params[$key]);
	}
	
	public function __toString() {
		return $this->getUrl();
	}
}