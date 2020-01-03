<?php

/*

 * DbClass
 * 
 * Provides access to the database.
 * Implies singleton design pattern.
 */
class DbClass {

    // PRIVATE PROPERTIES
    private static $_instance = null;
    private $host, $db, $charset, $user, $connection;
    private $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    
    // CONSTRUCTOR
    private function __construct(string $host="localhost",string $db="test", string $charset="utf8",string $user="test",string $pass="test") {
        $this->host = $host;
        $this->db = $db;
        $this->charset = $charset;
        $this->user = $user;
        $this->pass = $pass;
    }

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Instance of DbClass
    public static function GetInstance() {
        
        // instanse is null
        if (!self::$_instance) {
            self::$_instance = new DbClass();
        }
        
        return self::$_Instance;
    }
    
    /*
     * Single query
     * Performs a single db query
     * Return: query statement
     */
    public function singleQueryRetStatement(string $sql, array $data = null) {
        
        try {
            $this->_connect();

            $statement = $this->connection->prepare($sql);
            $statement->execute($data);

            $this->_disconnect();

            return $statement;
            
        } catch (Exception $ex) {
            SystemServices::AddToLog($ex->getTraceAsString(), $ex->getMessage());
            return null;
        }
    }
    
    /*
     * Single query
     * Performs a single db query
     * Return: query result
     */
    public function singleQueryRetResult(string $sql, array $data = null): bool {
        
        try {
            $this->_connect();

            $statement = $this->connection->prepare($sql);
            $query_result = $statement->execute($data);

            $this->_disconnect();

            return $query_result;
            
        } catch (Exception $ex) {
            SystemServices::AddToLog($ex->getTraceAsString(), $ex->getMessage());
            return false;
        }
    }
    // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="PRIVATE METHODS">

    // Connect to the db
    private function _connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $this->connection = new PDO($dsn, $this->user, $this->pass, $this->opt);
    }

    // Disconnect from the db
    private function _disconnect() {
        $this->connection = null;
    }

    // </editor-fold>
}
