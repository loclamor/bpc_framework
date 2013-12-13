<?php
class Entite {
	/**
	 * String
	 * class corresponding table name in DataBase (without TABLE_PREFIX)
	 */
	public $DB_table = '';
	/**
	 * Array
	 * mapping between class members and DB attributes
	 */
	public $DB_equiv = array(); // classMember => DBAttr
	/**
	 * Array
	 * mapping between class members and their type
	 * Used by Form to automaticaly generate a HTML form element mapping members of the class
	 * non listed members will not appears in HTML form, except the ID witch will be hidden if no specified
	 * Type could be : varchar(length), enum(val1, val2, val3 [,...]), text, date, integer, hidden
	 */
	public $memberType = array(); // classMember => type
	/**
	 * the entite ID
	 */
	public $id;
	
	/**
	 * Create a new Entite
	 * @param ID $id optional, if defined, load the Entite identified by its ID from database
	 */
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
	 * load an object from the database
	 * @param String $id_k database column name
	 * @param undefined $id_v needed value
	 * ATTENTION : $id_v must to be unique
	 */
	public function loadFromDB($id_k,$id_v){
		$requete = 'SELECT * FROM '.TABLE_PREFIX.$this->DB_table.' WHERE '.$id_k.' = '.$id_v;
		$values = SQL::getInstance()->exec2($requete);
		foreach ($values as $key => $value){
			$db_equiv = array_flip($this->DB_equiv); //on inverse les clees et le valeurs pour utiliser les valeurs en tant que clees
			$var = $db_equiv[$key];
			$this->$var = (stripslashes($value));
		}
	}
	
	/**
	 * catch undefined getters and setters
	 */
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
	
	/**
	 * save the Entite in database
	 * @param Array $toUpdate [optional] list the classMembers attributes to update in database. If null, all attributes will be updated
	 * $toUpdate is not used in case of first save (SQL Insert)
	 * @return true/falsein case of update, depending update success ; in case of first save (SQL Insert), return the new ID
	 */
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
							$toSet[] = ' '.$value.' = "'.addslashes(htmlspecialchars(nl2br($this->$key))).'"';
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
						$values[] = '"'.addslashes(htmlspecialchars(nl2br($this->$key))).'"';
					}
				}
			}
			//debug($column);debug($values);
			$requete .= '('.implode(', ',$column).') VALUES('.implode(', ',$values).')';
			$insertID = SQL::getInstance()->exec2($requete);
			$this->id = $insertID;
            //update the Entite with default values from database if any
            if(array_key_exists('id', $this->DB_equiv)){
				$this->loadFromDB($this->DB_equiv['id'],$this->id);
			}
			return $insertID;
		}
	}
	
	/**
	 * Delete the Entite from the database
	 * The corresponding class instance is not destroy and could be saved again (new SQL Insertion)
	 * @return true on success
	 */
	public function supprimer(){
		$requete = 'DELETE FROM '.TABLE_PREFIX.$this->DB_table;
		$requete .= ' WHERE '.$this->DB_equiv['id'].' = '.$this->id;
		SQL::getInstance()->exec2($requete);
		return true;
	}
}
