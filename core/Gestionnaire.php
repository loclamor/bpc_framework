<?php
class Gestionnaire {
	
	private static $_instance = Array();
	private $loadedClass = array();
	private $class; //feinte pour acc�der aux variables de conf d'une Entit�
	private $className;
	
	/**
	 * 
	 * Enter description here ...
	 * @param Class $class
	 * @return Gestionnaire
	 */
	public static function getGestionnaire($class = null){
		$class = ''.firstchartoupper($class);
		if(!isset(self::$_instance[$class])){
			if(!class_exists($class)){
				return false;
			}
			self::$_instance[$class] = new Gestionnaire($class);
		}
		return self::$_instance[$class];
	}
	
	public function __construct($class) {
		$this->class = new $class();
		$this->className = get_class($this->class);
	}
	
	/**
	 * @param integer $id
	 * @return Entite
	 */
	public function getOne($id) {
		if(array_key_exists($id, $this->loadedClass)) {
			return $this->loadedClass[$id];
		}
		else {
			$one = new $this->className($id);
			$this->loadedClass[$id] = $one;
			return $one;
		}
	}
	
	public function getAll($orderby = 'id', $desc = false) {
		if(!is_null($orderby) && !empty($orderby)) {
			$desc = $desc?' DESC':' ASC';
			$orderby = ' ORDER BY ' . $orderby.$desc;
		}
		else {
			$orderby = '';
		}
		$res = SQL::getInstance()->exec('SELECT '.$this->class->DB_equiv['id'].' FROM '.TABLE_PREFIX.$this->class->DB_table.$orderby);
		if($res) { //cas ou aucun retour requete (retour FALSE)
			$all = array();
			foreach ($res as $row) {
				$all[] = $this->getOne($row[$this->class->DB_equiv['id']]);
			}
		}
		else {
			$all = false;
		}
		return $all;
	}
	
	public function countAll() {
		$res = SQL::getInstance()->exec('SELECT COUNT(*) as nombre FROM '.TABLE_PREFIX.$this->class->DB_table);
		if($res) { //cas ou aucun retour requete (retour FALSE)
			$all = 0;
			foreach ($res as $row) {
				$all = $row['nombre'];
			}
		}
		else {
			$all = 0;
		}
		return $all;
	}
	
	/**
	 * Retourne les enregistrements corespondant aux conditions
	 * @param array $mixedConditions [var, value] (WHERE var = value AND ...)
	 * @param string $orderby [optional, default 'id'] (ORDER BY $orderby)
	 * @param boolean $desc [optional, default false] si true DESC sinon ASC
	 * @param integer $limitDown [optional, default 0] cf $limitUp
	 * @param integer $limitUp [optional, default 0] si $limitUp > $limitDown alors (LIMIT $limitDown, $limitUp)
	 * @return Array<Entite> ($this->class) ou false si pas de resultat
	 */
	public function getOf(array $mixedConditions, $orderby = 'id', $desc = false, $limitDown = 0, $limitUp = 0) {
		if(!is_null($orderby) && !empty($orderby)) {
			$desc = $desc?'DESC':'ASC';
			$orderby = ' ORDER BY ' . $orderby;
		}
		else {
			$orderby = '';
		}
		if($limitUp > $limitDown){
			$limit = ' LIMIT '.$limitDown.', '.$limitUp;
		}
		else {
			$limit = '';
		}
		$cond = array();
		foreach ($mixedConditions as $var => $value){
			$cond[] = $this->class->DB_equiv[$var].' = \''.$value.'\'';
		}
		$res = SQL::getInstance()->exec('SELECT '.$this->class->DB_equiv['id'].' FROM '.TABLE_PREFIX.$this->class->DB_table.' WHERE '.implode(' AND ',$cond).$orderby.$limit);
		if($res) { //cas ou aucun retour requete (retour FALSE)
			$all = array();
			foreach ($res as $row) {
				$all[] = $this->getOne($row[$this->class->DB_equiv['id']]);
			}
		}
		else {
			$all = false;
		}
		return $all;
	}
	
	/**
	 * Retourne le premier enregistrement respectant les conditions
	 * @param array $mixedConditions [var, value] (WHERE var = value AND ...)
	 * @return Entite $this->class ou false si pas de r�sultat
	 */
	public function getOneOf(array $mixedConditions){
		$ret = $this->getOf($mixedConditions, 'id', false, 0, 1);
		if($ret !== false){
			return $ret[0];
		}
		return $ret;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param array $mixedConditions [var, value]
	 * @return integer
	 */
	public function countOf(array $mixedConditions) {
		$cond = array();
		foreach ($mixedConditions as $var => $value){
			$cond[] = $this->class->DB_equiv[$var].' = \''.$value.'\'';
		}
		$res = SQL::getInstance()->exec('SELECT COUNT(*) as nombre FROM '.TABLE_PREFIX.$this->class->DB_table.' WHERE '.implode(' AND ',$cond));
		if($res) { //cas ou aucun retour requete (retour FALSE)
			$all = 0;
			foreach ($res as $row) {
				$all = $row['nombre'];
			}
		}
		else {
			$all = 0;
		}
		return $all;
	}
	
}