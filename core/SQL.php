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
	public function exec($requete){
		$this->setLastQuery($requete);
		$this->nb_query++;
		//on fait la connexion à mysql
		mysql_connect(MYSQL_SERVER,  MYSQL_USER, MYSQL_PWD);
		mysql_select_db(MYSQL_DB);
		
		$this->setLastError();
		//on fait la requete
		$rep = mysql_query($requete);
		//debug($rep);
		$this->setLastError(mysql_error());
		if($this->last_sql_error != ''){
			//echo $this->last_sql_error;
			$this->nb_sql_errors++;
			$this->log->log('sql', 'erreurs_sql', $this->getLastQuery() . ' : ' . $this->last_sql_error, Logger::GRAN_MONTH);
		}
		
		$row = false;
		if(strtoupper(substr($requete, 0, 6)) == 'SELECT') {
			if(!is_null($rep) && !empty($rep)) {
			/*	if(mysql_num_rows($rep) > 1) {
					while($res = mysql_fetch_assoc($rep)){
						$row[] = $res;
					}
				}
				else {
					$row = mysql_fetch_assoc($rep);
				}
				*/
				while($res = mysql_fetch_assoc($rep)){
					$row[] = $res;
				}
			}
		}
		elseif(strtoupper(substr($requete, 0, 6)) == 'INSERT') {
			$row = mysql_insert_id();
		}

		//on se déconnecte
		mysql_close();
		//on retourne le tableau de rÃ©sultat
		return $row;
	}
	
	//execution de requete SQL reserve à l'administration
	public function exec2($requete){
		$this->setLastQuery($requete);
		$this->nb_adm_query++;
		
		//on fait la connexion Ã  mysql
		mysql_connect(MYSQL_SERVER,  MYSQL_USER, MYSQL_PWD);
		mysql_select_db(MYSQL_DB);
		
		$this->setLastError();
		//on fait la requete
		$rep = mysql_query($requete);
		//debug($rep);
		$this->setLastError(mysql_error());
		
		if($this->last_sql_error != ''){
			//echo $this->last_sql_error;
			$this->nb_sql_errors++;
			$this->log->log('sql', 'erreurs_sql', $this->getLastQuery() . ' : ' . $this->last_sql_error, Logger::GRAN_MONTH);
		}
		
		$row = false;
		if(strtoupper(substr($requete, 0, 6)) == 'SELECT') {
			if(!is_null($rep) && !empty($rep)) {
				if(mysql_num_rows($rep) > 1) {
					while($res = mysql_fetch_assoc($rep)){
						$row[] = $res;
					}
				}
				else {
					$row = mysql_fetch_assoc($rep);
				}
			}
		}
		elseif(strtoupper(substr($requete, 0, 6)) == 'INSERT') {
			$row = mysql_insert_id();
		}

		//on se dÃ©connecte
		mysql_close();
		//on retourne le tableau de rÃ©sultat
		return $row;
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
}