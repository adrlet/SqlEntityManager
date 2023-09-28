<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/Convertors/Stringizable.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/Database/GlobalDatabase.php';

/*
 * Class:  Database 
 * --------------------
 *  Abstract class handling connection to DBMS
 *  Implementation requires providing means to connect to DBMS
 *  and preserve the connection object
 *
 *  Attributes:
 *  	$host : String
 * 		$port : Int
 * 		$dbmsName : String
 *		$databaseName : String
 *		$login : String
 *		$password : String
 *		$pdoConnection : PDO
 * 
 *  Methods:
 *  	__construct(array $config = null)
 *  	__destruct()
 * 		public reconnect(array $config) : void
 * 		protected connect() : void
 * 		protected parseConfig(array $configRows) : void
 * 		public getConnection() : PDO
 * 		public toString() : string
 * 
 */
class Database implements Stringizable
{
	use GlobalDatabase;

	// The name or address of host containing DBMS 
	protected $host;

	// The port of DBMS process
	protected $port;
	
	// Specifies type of dbms
	protected $dbmsName;

	// Specifies the database withing dbms
	protected $databaseName;
	
	// The login of operating account
	protected $login;

	// Password of operating account
	protected $password;
	
	// PDO object of connection
	protected $pdoConnection = null;
	
	/*
	* Method: __construct 
	* --------------------
	*  Initiates connection on construct
	*  The config format depends on the attributes of class:
	*  host=address or host name
	*  port=port
	*  dbms=type of dbms
	*  db=database name
	*  login=login
	*  password=password
	*  If config array is not provided then the global database is used
	*
	*  config: Optional config in form of an array of pairs key=value : array
	*
	*  returns: void
	*/
	function __construct(array $config = null)
	{
		$this->reconnect($config);
	}
	
	/*
	* Method: __destruct 
	* --------------------
	*  The destructor unsets PDO connection
	*
	*  returns: void
	*/
	function __destruct()
	{
		if(is_null($this->pdoConnection) == false)
			unset($this->pdoConnection);

		//$this->pdoConnection = null; 
	}
	
	/*
	* Method: reconnect 
	* --------------------
	*  Makes an new connection on provided config
	*  If not delivered, use the global database  
	*
	*  config: Optional config in form of an array of pairs key=value : array
	*
	*  returns: void
	*/
	public function reconnect(array $config = null) : void
	{
		// Use provided config if provided
		if(is_null($config) == false)
		{
			$this->parseConfig($config);
			$this->connect();
		}
		// Else use the global database
		elseif(is_null(Database::$globalDatabase) == false)
			$this->pdoConnection = Database::$globalDatabase->getConnection();
	}
	
	/*
	* Method: connect 
	* --------------------
	*  Creates PDO connection on object config attributes
	*
	*  returns: void
	*/
	protected function connect() : void
	{
		$this->pdoConnection = new PDO($this->dbmsName.':'.
		'host='.$this->host.';'.
		'dbname='.$this->databaseName.';'.
		'port='.$this->port,
		$this->login, $this->password);
		
		$this->pdoConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	/*
	* Method: connect 
	* --------------------
	*  Reads array of pairs key=value and fills attributes of database object
	*  
	*  configRows: array of pairs key=value : array
	*
	*  returns: void
	*/
	protected function parseConfig(array $configRows) : void
	{
		// Iterate pairs
		foreach($configRows as $configValue)
		{
			// Remove extra spaces and split the value and pair
			$configPair = array_map('trim', explode('=', $configValue));
			
			// Select the attribute upon the key content
			switch($configPair[0])
			{
			case 'host':
				$this->host = $configPair[1];
				break;
				
			case 'port':
				$this->port = $configPair[1];
				break;
				
			case 'dbms':
				$this->dbmsName = $configPair[1];
				break;
				
			case 'db':
				$this->databaseName = $configPair[1];
				break;
			
			case 'login':
				$this->login = $configPair[1];
				break;
				
			case 'password':
				$this->password = $configPair[1];
				break;
			}
		}
	}
	
	/*
	* Method: getConnection 
	* --------------------
	*  Basic getter method for PDO connection
	*
	*  returns: PDO connection
	*/
	public function getConnection() : PDO
	{
		return $this->pdoConnection;
	}
	
	/*
	* Method: toString 
	* --------------------
	*  Casts config attributes to string excluding the sensitive login and password
	*  toString won't provide informations if its retrieved from static context
	*
	*  returns: string of DBMS configuration
	*/
	public function toString() : string
	{
		return 'host:'.$this->host.'/n'.
		'port:'.$this->port.'/n'.
		'dbms:'.$this->dbmsName.'/n'.
		'databaseName'.$this->databaseName.'/n';
	}
}

if(Database::presentGlobalDatabase())
	Database::setGlobalDatabaseFromConfig();

?>