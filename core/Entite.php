<?php
class Entite implements JsonSerializable {
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
	 * mapping between DB attributes and SQL types (int(X), varchar(X), datetime...)
	 * used to automatically generate DB model on the fly
	 * Type can be appended by ' NOT NULL' and optionaly 'DEFAULT XXX' in SQL syntax.
	 * NOT NULL option should have DEFAULT value if existing table already have rows.
	 */
	public $DB_type = array(); // DBAttr => DBtype
	
	/**
	 * Array
	 * mapping between class members and their type
	 * Used by Form to automaticaly generate a HTML form element mapping members of the class
	 * non listed members will not appears in HTML form, except the ID witch will be hidden if no specified
	 * Type could be : varchar(length), enum(val1, val2, val3 [,...]), text, date, integer, hidden
	 */
	public $memberType = array(); // classMember => type
	
	/**
	 * Custom list of entity members to NOT serialize on json_encode
	 */
	public $donotSerialize = array();
	
	/**
	 * Custom list of entity members to NOT sync over Database
	 */
	public $donotSyncDatabase = array();
	/**
	 * the entite ID
	 */
	public $id;
	
	private $log;
	
	/**
	 * Create a new Entite
	 * @param ID $id optional, if defined, load the Entite identified by its ID from database
	 */
	public function __construct($id = null){
		$this->log = new Logger('./logs', "");
		$dbequiv = $this->getDBEquiv();
		if(ENTITIES_AUTO_INSTALL === true) {
			SQL::getInstance()->manualConnection();
			$tableName = TABLE_PREFIX.$this->DB_table;
			// check table exists ; create table elsewhere
			if (!SQL::getInstance()->checkTableExists($tableName)) {
				//create table
				SQL::getInstance()->createEmptyTable($tableName);
			}
			// get table description
			$struct = SQL::getInstance()->getTableDescription($tableName);
			
			// check existing fields ; create missing with correct type
			foreach( $this->DB_type as $field => $type ) {
				if( !array_key_exists($field, $struct) ) {
					SQL::getInstance()->addFieldToTable($field, $type, $tableName);
				}
			}
			SQL::getInstance()->manualClose();
		}
		if(!is_null($id)){
			if(array_key_exists('id', $dbequiv)){
				$this->loadFromDB($dbequiv['id'],$id);
			}
			else {
				//gestion erreur
				echo 'pas de champ id';
			}
		}
	}
	
	public function log($msg, $logLevel = LOG_LEVEL) {
		if( $logLevel > LOG_LEVEL ) {
			return;
		}
		$this->log->setBaseString(get_class($this) . " : ");
		$this->log->log('sql', 'entite', $msg, Logger::GRAN_MONTH);
	}
	
	/**
	 * load an object from the database
	 * @param String $id_k database column name
	 * @param undefined $id_v needed value
	 * ATTENTION : $id_v must to be unique
	 */
	public function loadFromDB($id_k,$id_v){
		$dbequiv = $this->getDBEquiv();
		$requete = 'SELECT * FROM `'.TABLE_PREFIX.$this->DB_table.'` WHERE `'.$id_k.'` = '.$id_v;
		$values = SQL::getInstance()->exec($requete, true);
		if (!$values) {
			 SQL::getInstance()->getLogger()->log('sql', 'erreurs_sql', "loadFromDB : 'SELECT * FROM `". TABLE_PREFIX.$this->DB_table.'` WHERE `'.$id_k.'` = '.$id_v."' : empty response !", Logger::GRAN_MONTH);
			throw new \Exception($this->DB_table . ' : `' .$id_k.'` = `'.$id_v . "` n'existe pas !");
		}
		foreach ($values as $key => $value){
			$db_equiv = array_flip($dbequiv); //on inverse les clees et le valeurs pour utiliser les valeurs en tant que clees
			$var = $db_equiv[$key];
			if($value === null) {
				$this->$var = null;
			}
			elseif (is_numeric($value)) {
				$this->$var = $value;
			}
			else {
				$this->$var = (stripslashes($value));
			}
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
		$dbequiv = $this->getDBEquiv();
		if(!is_null($this->id)) {
			//update
			if(is_null($toUpdate)) {
				$toUpdate = array_flip($dbequiv);
			}
			$requete = 'UPDATE `'.TABLE_PREFIX.$this->DB_table.'` SET';
			$toSet = array();
			foreach ($dbequiv as $key => $value) {
				if($key != 'id' && !is_null($this->$key) && in_array($key,$toUpdate)) {
					if($this->$key === null) {
						$toSet[] = ' `'.$value.'` = NULL';
					}
					elseif(is_numeric($this->$key)) {
						$toSet[] = ' `'.$value.'` = '.$this->$key;
					}
					else {
							$toSet[] = ' `'.$value.'` = "'.addslashes(htmlspecialchars(nl2br($this->$key))).'"';
					}
				}
			}
			$requete .= implode(',',$toSet);
			$requete .= ' WHERE `'.$dbequiv['id'].'` = '.$this->id;
			SQL::getInstance()->exec($requete);
			return true;
		}
		else {
			//insert
			$requete = 'INSERT INTO `'.TABLE_PREFIX.$this->DB_table.'` ';
			$column = array();
			$values = array();
			foreach ($dbequiv as $key => $value) {
				if($key != 'id' && !is_null($this->$key)) {
					$column[] = $value;
					if($this->$key === null) {
						$values[] = 'NULL';
					}
					elseif(is_numeric($this->$key)) {
						$values[] = $this->$key;
					}
					else {
						$values[] = '"'.addslashes(htmlspecialchars(nl2br($this->$key))).'"';
					}
				}
			}
			//debug($column);debug($values);
			$requete .= '('.implode(', ',$column).') VALUES('.implode(', ',$values).')';
			$insertID = SQL::getInstance()->exec($requete);
			$this->id = $insertID;
            //update the Entite with default values from database if any
            if(array_key_exists('id', $dbequiv)){
				$this->loadFromDB($dbequiv['id'],$this->id);
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
		$dbequiv = $this->getDBEquiv();
		$requete = 'DELETE FROM `'.TABLE_PREFIX.$this->DB_table.'`';
		$requete .= ' WHERE `'.$dbequiv['id'].'` = '.$this->id;
		SQL::getInstance()->exec($requete);
		return true;
	}
	
	/**
     * As Entite implements JsonSerializable, jsonSerialize method is called on json_encode
     * @return array to jsonify contening only public setted properties of the EntiteJson.
     */
    public function jsonSerialize() {
        $json = array();
        $keys = $this->getPublicProperties();
        foreach ($keys as $key) {
            //return only defined attributes 
            if( !in_array($key, $this->getDoNotSerialize()) ) 
            {
                $value = $this->$key;
                $json[$key] = $value;
            }
        }
        return $json;
    }
    
    private function getDoNotSerialize() {
    	return array_merge(array('DB_table', 'DB_equiv', 'DB_type', 'memberType', 'donotSyncDatabase', 'donotSerialize'), $this->donotSerialize);
    }
    
    private function getDoNotSyncDatabase() {
    	return array_merge(array('DB_table', 'DB_equiv', 'DB_type', 'memberType', 'donotSerialize', 'donotSyncDatabase'), $this->donotSyncDatabase);
    }
    
   /**
    * Get only public properties of the class :
    * @see http://stackoverflow.com/questions/13124072/how-to-programatically-find-public-properties-of-a-class-from-inside-one-of-its#answer-15847048
    * @return array of public class properties
    */
    private function getPublicProperties() {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        return array_map(function($prop) { return $prop->getName(); }, $properties);
    }
    
    /**
     * @return array the real DB_equiv completed with public properties not in DB_equiv definition
     **/
    public function getDBEquiv() {
    	$realEquiv = array();
    	$locals = $this->getPublicProperties();
    	foreach($locals as $local) {
    		if(!in_array($local, $this->getDoNotSyncDatabase()) ) {
	    		if(array_key_exists($local, $this->DB_equiv)) {
	    			$realEquiv[$local] = $this->DB_equiv[$local];
	    		}
	    		else {
	    			$realEquiv[$local] = $local;
	    		}
    		}
    	}
    	return $realEquiv;
    }
    
    public function __toString() {
    	return json_encode($this);
    }
}
