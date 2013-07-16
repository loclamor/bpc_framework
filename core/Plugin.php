<?php
abstract class Plugin {
	
	/**
	 *	The dom content
	 * @var DOMDocument 
	 */
	protected $DOMContent;
	/**
	 *
	 * @var DOMXPath
	 */
	protected $XPathContent;
	
	public function __construct(){
		
	}
	
	public function setDomContent(DOMDocument & $dom){
		$this->DOMContent = $dom;
		$this->setIds($this->DOMContent->documentElement);
		$this->XPathContent = new DOMXPath($this->DOMContent);
		// Register the php: namespace (required)
		$this->XPathContent->registerNamespace("php", "http://php.net/xpath");

		// Register PHP functions (no restrictions)
		//$this->XPathContent->registerPhpFunctions();
	}
	
	public function exec(){
		$this->setIds($this->DOMContent->documentElement);
	}
	
	
	private function setIds(DOMNode &$node){
		if(!in_array($node->nodeType, array(XML_TEXT_NODE, XML_ENTITY_REF_NODE)) ){
			try {
				$node->setIdAttribute('id', true);			
			} catch (Exception $e) {

			}
			$nodes = $node->childNodes;
			for($i=0; $i<$nodes->length; $i++) {
				$n = $nodes->item($i);
				$this->setIds($n);
			}
		}
	}
	
}