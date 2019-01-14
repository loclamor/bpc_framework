<?php
class SQL {
	/**
	 * @var SQL
	 */
	private static $instance = null;
	private $last_sql_query = null;
	private $last_sql_error = null;
	private $nb_query = 0;
	private $nb_adm_query = 0;
	private $nb_sql_errors = 0;
	
	private $manualConnection = false;
	
	private $log;
	
	public static function getInstance() {
		if(is_null(self::$instance)) {
		self::$instance = new SQL();
		}
		return self::$instance;
	}
	
	public function __construct(){
		$this->log = new Logger('./logs');
		$this->log->setBaseString('SQL : ');
	}
	/**
	 * 
	 * @param string $requete
	 * @return array or false if no result
	 */
	public function exec($requete, $oneRow = false, $params = array()){
		$rep = $this->_exec($requete, $params);
		
		$row = false;
		if(strtoupper(substr($requete, 0, 6)) == 'SELECT') {
			if(!is_null($rep) && !empty($rep)) {
				if( $oneRow ) {
					$row = mysql_fetch_assoc($rep);
				}
				else {
					while($res = mysql_fetch_assoc($rep)){
						$row[] = $res;
					}
				}
			}
		}
		elseif(strtoupper(substr($requete, 0, 6)) == 'INSERT') {
			$row = mysql_insert_id();
		}
		else {
			$row = true;
		}

		//on se déconnecte
		$this->closeConnection();
		//on retourne le tableau de rÃ©sultat
		return $row;
	}
	
	/**
	 * Check if table_name exists in database
	 * @return true/false depending success
	 **/
	public function checkTableExists($tableName) {
		$rep = $this->_exec("SHOW TABLES LIKE '$tableName'");
		$row = mysql_fetch_assoc($rep);
		$this->closeConnection();
		return $row !== false;
	}
	
	/**
	 * Create empty table_name in database.
	 * Empty means only with id primary auto_increment field
	 * @return true/false depending success
	 **/
	public function createEmptyTable($tableName) {
		$rep = $this->_exec("CREATE TABLE `$tableName` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`), KEY `id` (`id`) )");
		$this->closeConnection();
		if ( $rep === true ) {
			$this->log->log('sql', 'info_sql', "create empty table `$tableName`", Logger::GRAN_MONTH);
			return true;
		}
		return false;
	}
	
	/**
	 * Get table description (mysql DESCRIBE)
	 * @return associated array field => type
	 **/
	public function getTableDescription($tableName) {
		$rep = $this->_exec("DESCRIBE `$tableName`");
		$struct = array();
		while($res = mysql_fetch_assoc($rep)){
			$struct[$res['Field']] = $res['Type'];
		}
		$this->closeConnection();
		return $struct;
	}
	
	/**
	 * Add specified typed field to table
	 * @return true/false depending success.
	 **/
	public function addFieldToTable($field, $type, $table) {
		$rep = $this->_exec("ALTER TABLE `$table` ADD COLUMN `$field` $type");
		$this->closeConnection();
		if ( $rep === true ) {
			$this->log->log('sql', 'info_sql', "alter table `$table` add field `$field` '$type'", Logger::GRAN_MONTH);
			return true;
		}
		return false;
	}
	
	private function _exec($requete, $params = array()) {
		$this->setLastQuery($requete);
		$this->nb_query++;
		//on fait la connexion à mysql
		$this->openConnection();
		
		$this->setLastError();
		
		if(is_array($params) && count($params) > 0) {
			foreach($params as $p_key => $p_value) {
				$e_value = mysql_real_escape_string($p_value);
				$requete = str_replace('{{'.$p_key.'}}', $e_value, $requete);
			}
			$this->log->log('sql', 'info_sql', '"' . $this->getLastQuery() . '"\n escaped in "' . $requete . '"', Logger::GRAN_MONTH);
		}
		
		//on fait la requete
		$rep = mysql_query($requete);
		//debug($rep);
		$this->setLastError(mysql_error());
		if($this->last_sql_error != ''){
			//echo $this->last_sql_error;
			$this->nb_sql_errors++;
			$this->log->log('sql', 'erreurs_sql', $this->getLastQuery() . ' : ' . $this->last_sql_error, Logger::GRAN_MONTH);
		}
		return $rep;
	}
	
	public function manualConnection() {
		if($this->manualConnection === false) {
			$this->openConnection();
			$this->manualConnection = 1;
		}
		else {
				$this->manualConnection++;
		}
	}
	
	public function manualClose() {
		if($this->manualConnection === 1) {
			mysql_close();
			$this->manualConnection = false;
		}
		else {
			$this->manualConnection--;
		}
	}
	
	private function openConnection() {
		if($this->manualConnection === false) {
			@mysql_connect(MYSQL_SERVER,  MYSQL_USER, MYSQL_PWD);
			@mysql_select_db(MYSQL_DB);
		}
	}
	
	private function closeConnection() {
		if($this->manualConnection === false) {
			mysql_close();
		}
	}
	
	public function setLastError($err = ''){
		$this->last_sql_error = $err;
	}
	
	public function getLastError() {
		return $this->last_sql_error;
	}
	
	public function setLastQuery($q = null){
		$this->last_sql_query = $q;
	}
	
	public function getLastQuery() {
		return $this->last_sql_query;
	}
	
	public function getNbQuery() {
		return $this->nb_query;
	}
	
	public function getNbAdmQuery() {
		return $this->nb_adm_query;
	}
	
	public function getNbErrors() {
		return $this->nb_sql_errors;
	}
	
	public function getLogger() {
		return $this->log;
	}
}