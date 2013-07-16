<?php
class Entite {
	public $DB_table = '';
	public $DB_equiv = array();
	public $id;
	
	public function __construct($id = null){
		if(!is_null($id)){
			if(array_key_exists('id', $this->DB_equiv)){
				$this->loadFromDB($this->DB_equiv['id'],$id);
			}
			else {
				//gestion erreur
				echo 'pas de champ id';
			}
		}
	}
	
	/**
	 * charge l'objet � partir de la BD
	 * @param integer $id_k le nom du champ en BD
	 * @param integer $id_v la valeur du champs que l'on veut
	 * ATTENTION : $id_v doit �tre unique en BD
	 */
	public function loadFromDB($id_k,$id_v){
		$requete = 'SELECT * FROM '.TABLE_PREFIX.$this->DB_table.' WHERE '.$id_k.' = '.$id_v;
		$values = SQL::getInstance()->exec2($requete);
		foreach ($values as $key => $value){
			$db_equiv = array_flip($this->DB_equiv); //on inverse les clees et le valeurs pour utiliser les valeurs en tant que clees
			$var = $db_equiv[$key];
			$this->$var = htmlentities(stripslashes($value));
		}
	}
	
	//on va choper tous les getters en setters non d�finit
	public function __call($func,$args) {
		if(APPLICATION_ENV == 'dev' ) {
			echo '<span class="warning">WARNING creer la methode : '.$func.'('.')</span><br/>';
		}
		if(substr($func, 0, 3) == 'get') {
			$varToGet = substr($func, 3);
			$varToGet = strtolower(substr($varToGet, 0, 1)).substr($varToGet, 1);
			return $this->$varToGet;
		}
		elseif(substr($func, 0, 3) == 'set') {
			$varToSet = substr($func, 3);
			$varToSet = strtolower(substr($varToSet, 0, 1)).substr($varToSet, 1);
			$this->$varToSet = $args[0];
		}
	}
	
	public function enregistrer($toUpdate = null) {
		if(!is_null($this->id)) {
			//update
			if(is_null($toUpdate)) {
				$toUpdate = array_flip($this->DB_equiv);
			}
			$requete = 'UPDATE '.TABLE_PREFIX.$this->DB_table.' SET';
			$toSet = array();
			foreach ($this->DB_equiv as $key => $value) {
				if($key != 'id' && !is_null($this->$key) && in_array($key,$toUpdate)) {
					if(is_int($this->$key)) {
						$toSet[] = ' '.$value.' = '.$this->$key;
					}
					else {
							$toSet[] = ' '.$value.' = "'.addslashes(html_entity_decode(nl2br($this->$key))).'"';
					}
				}
			}
			$requete .= implode(',',$toSet);
			$requete .= ' WHERE '.$this->DB_equiv['id'].' = '.$this->id;
			SQL::getInstance()->exec2($requete);
			return true;
		}
		else {
			//insert
			$requete = 'INSERT INTO '.TABLE_PREFIX.$this->DB_table.' ';
			$column = array();
			$values = array();
			foreach ($this->DB_equiv as $key => $value) {
				if($key != 'id' && !is_null($this->$key)) {
					$column[] = $value;
					if(is_int($this->$key)) {
						$values[] = $this->$key;
					}
					else {
						$values[] = '"'.addslashes(html_entity_decode(nl2br($this->$key))).'"';
					}
				}
			}
			//debug($column);debug($values);
			$requete .= '('.implode(', ',$column).') VALUES('.implode(', ',$values).')';
			$insertID = SQL::getInstance()->exec2($requete);
			$this->id = $insertID;
			return $insertID;
		}
	}
	
	public function supprimer(){
		$requete = 'DELETE FROM '.TABLE_PREFIX.$this->DB_table;
		$requete .= ' WHERE '.$this->DB_equiv['id'].' = '.$this->id;
		SQL::getInstance()->exec2($requete);
		return true;
	}
}