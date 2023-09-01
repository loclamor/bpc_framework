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
		$class = ucfirst($class);
		if(!isset(self::$_instance[$class])){
			if(!class_exists($class)){
                $class = 'Model_'.ucfirst($class);
                if(!class_exists($class)){
                    return false;
                }
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
	 * Clean and format an SQL orderby clause :
	 * 'p1 order , p2 order' become '`p1` order, `p2` order'
	 * @param String $orderby dirty orderby clause
	 * @return String the cleaned orderby clause
	 **/
	private function cleanOrderBy($orderby) {
		$cleaned = [];
		$parts = explode(',', $orderby);
		foreach($parts as $part) {
			$p_part = explode(' ', trim($part));
			$cleaned[] = 'te.`'.trim($p_part[0]).'`'.(count($p_part)>1?' '.trim($p_part[1]):'');
		}
		return implode(', ', $cleaned);
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
	
	/**
	 * 
	 * @param int $page index de la page 
	 * @param int $length longueur de la page
	 * @param string $orderby colone de tri
	 * @param boolean $desc tri DESC ou pas 
	 * @param array $mixedConditions conditions de filtre [var: value] ou [var: [op, value]] (WHERE var = value AND ...)
	 */
	public function getPaginate($page = 1, $length = 25, $orderby = 'id', $desc = false, array $mixedConditions = [], $join = '') {
		$limit = $length;
		$offset = ($page -1) * $length;
		return $this->getOf($mixedConditions, $orderby, $desc, $offset, $limit, $join);
	}
	
	public function getAll($orderby = 'id', $desc = false, $limit = null, $offset = 0) {
		$dbequiv = $this->class->getDBEquiv();
		if(!is_null($orderby) && !empty($orderby)) {
			$desc = $desc?' DESC':' ASC';
			$orderby = ' ORDER BY ' . $this->cleanOrderBy($orderby.$desc);
		}
		else {
			$orderby = '';
		}
		$limitclause = '';
		if (!is_null($limit)) {
			$limitclause = " LIMIT $offset, $limit";
		}
		$all = $this->getSQL('SELECT '.$dbequiv['id'].' FROM `'.TABLE_PREFIX.$this->class->DB_table.'` te'.$orderby.$limitclause);
		return $all;
	}
	
	public function countAll() {
		$res = SQL::getInstance()->exec('SELECT COUNT(*) as nombre FROM `'.TABLE_PREFIX.$this->class->DB_table.'`');
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
	 * @param array $mixedConditions [var: value] or [var: [op, value]] (WHERE var = value AND ...)
	 * @param string $orderby [optional, default 'id'] (ORDER BY $orderby)
	 * @param boolean $desc [optional, default false] si true DESC sinon ASC
	 * @param integer $limitDown [optional, default 0] cf $limitUp
	 * @param integer $limitUp [optional, default 0] si $limitUp > $limitDown alors (LIMIT $limitDown, $limitUp)
	 * @return Array<Entite> ($this->class) ou false si pas de resultat
	 */
	public function getOf(array $mixedConditions, $orderby = 'id', $desc = false, $offset = 0, $limit = 0, $join = '') {
		$dbequiv = $this->class->getDBEquiv();
		if(!is_null($orderby) && !empty($orderby)) {
			$desc = $desc?' DESC':' ASC';
			$orderby = ' ORDER BY ' . $this->cleanOrderBy($orderby.$desc);
		}
		else {
			$orderby = '';
		}
		if($limit > 0){
			$limit = ' LIMIT '.$offset.', '.$limit;
		}
		else {
			$limit = '';
		}
		$conditions = '';
		$params = array();
		if (is_array($mixedConditions) && count($mixedConditions) > 0) {
			$cond = array();
			foreach ($mixedConditions as $var => $value){
				$pk = 'p_'.(count($params)+1);
				$val = $value;
	            if( is_array( $value ) ) {
	                //forme [var: [op, value]]
	                $cond[] = 'te.`'.$dbequiv[$var].'` '.$value[0].($value[1] !== null? ' \'{{'.$pk.'}}\'' : ' NULL');
	                $val = $value[1];
	            }
	            else {
	                //forme [var: value] === [var: ["=", value] ]
	                $cond[] = 'te.`'.$dbequiv[$var].'` = '.($value!==null?'\'{{'.$pk.'}}\'':' NULL');
	            }
	            $params[$pk] = $val;
			}
			$conditions = 'WHERE '.implode(' AND ',$cond);
		}
		$join = " $join ";
		$all = $this->getSQL( 'SELECT te.`'.$dbequiv['id'].'` FROM `'.TABLE_PREFIX.$this->class->DB_table.'` as te '.$join.$conditions.$orderby.$limit, $params );
		return $all;
	}
	
	/**
	 * Retourne le premier enregistrement respectant les conditions
	 * @param array $mixedConditions [var: value] or [var, [op, value]] (WHERE var = value AND ...)
	 * @return Entite $this->class ou false si pas de r�sultat
	 */
	public function getOneOf(array $mixedConditions, $orderby = 'id', $desc = false, $join = ''){
		$ret = $this->getOf($mixedConditions, $orderby, $desc, 0, 1, $join);
		if($ret !== false){
			return $ret[0];
		}
		return $ret;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param array $mixedConditions [var: value] or [var : [op, value]] (WHERE var = value AND ...)
	 * @return integer
	 */
	public function countOf(array $mixedConditions, $join = '') {
		$dbequiv = $this->class->getDBEquiv();
		$conditions = '';
		$params = array();
		if (is_array($mixedConditions) && count($mixedConditions) > 0) {
			$cond = array();
	        foreach ($mixedConditions as $var => $value){
	        	$pk = 'p_'.(count($params)+1);
				$val = $value;
	            if( is_array( $value ) ) {
	                //forme [var, [op, value]]
	                $cond[] = 'te.`'.$dbequiv[$var].'` '.$value[0].($value[1] !== null? ' \'{{'.$pk.'}}\'' : ' NULL');
	                $val = $value[1];
	            }
	            else {
	                //forme [var, value] === [var, ["=", value] ]
	                $cond[] = 'te.`'.$dbequiv[$var].'` = '.($value!==null?'\'{{'.$pk.'}}\'':' NULL');
	            }
	            $params[$pk] = $val;
			}
			$conditions = 'WHERE '.implode(' AND ',$cond);
		}
		$join = " $join ";
		$res = SQL::getInstance()->exec('SELECT COUNT(*) as nombre FROM `'.TABLE_PREFIX.$this->class->DB_table.'` as te '.$join.$conditions, false, $params);
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
	
	public function getSQL( $sqlStr, $params = array() ) {
		$dbequiv = $this->class->getDBEquiv();
		$res = SQL::getInstance()->exec( $sqlStr, false, $params );
		if($res) { //cas ou aucun retour requete (retour FALSE)
			$all = array();
			foreach ($res as $row) {
				$e = $this->getOne($row[$dbequiv['id']]);
				foreach($row as $k => $v) {
					if(!isset($dbequiv[$k])) {
						$e->$k = $v;
					}
				}
				$all[] = $e;
			}
		}
		else {
			$all = false;
		}
		return $all;
	}
	
	public function countSQL( $sqlStr, $params = array() ) {
		$slqCount = 'SELECT COUNT(*) as nombre FROM ('.$sqlStr.') selectcount';
		$res = SQL::getInstance()->exec( $slqCount, false, $params );
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