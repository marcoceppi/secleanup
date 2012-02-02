<?php

require_once 'api.php';
require_once 'api_exception.php';
require_once 'cache_base.php';

// The schema for the cache table
define('CACHE_SCHEMA', <<<EOD
CREATE TABLE cache ( url                 TINYTEXT,
                     data                MEDIUMTEXT,
                     retrieval_timestamp INT,
                     ttl                 INT )

EOD
);

/// Provides a means to use an SQL database for caching purposes.
class SQLCache implements CacheBase
{
    // Constants for different supported database types
    
    /// Represents a MySQL driver.
    const MySQL        = 'mysql:host=';
    /// Represents a PostgreSQL driver.
    const PostgreSQL   = 'pgsql:host=';
    /// Represents a SQLite driver.
    const SQLite       = 'sqlite:';
    /// Represents an in-memory SQLite database.
    const SQLiteMemory = 'sqlite::memory:';
    
    // The connection to the database
    private $database = null;
    
    /// Constructor for an SQL cache.
    /**
      * \param $type the type of database to connect to
      * \param $host the host running the SQL server or a local filename (where supported)
      * \param $username the username for the SQL database
      * \param $password the password for the SQL database
      * \param $database the database to use for caching
      *
      * Note: this function also makes sure that the table 'cache' exists,
      * attempting to create it if it does not.
      */
    function __construct($type, $host='', $username=null, $password=null, $database=null)
    {
        try
        {
            $this->database = new PDO("$type$host" . (($database !== null)?";dbname=$database":''),
                                      $username,
                                      $password);
            
            // Check if we need to create the 'cache' table
            $this->CreateTableIfNeeded();
        }
        catch(PDOException $e)
        {
            throw new APIException('Cache: ' . $e->getMessage(), null, '', $e->getCode());
        }
    }
    
    // Creates the 'cache' table if necessary
    private function CreateTableIfNeeded()
    {
        // Prepare a statement
        $statement = $this->database->prepare("SELECT * FROM CACHE");
        
        // Create the table otherwise
        if($statement === FALSE)
        {
            $statement = $this->database->prepare(CACHE_SCHEMA);
            
            // Check to make sure the table was created
            if($statement === FALSE || $statement->execute() === FALSE)
                throw new APIException('Cache: An error occurred while creating the cache table.');
        }
    }
    
    /// Retrieves the data for the given URL from the database.
    /**
      * \param $url the URL to retrieve the data for
      * \return the data for the URL or FALSE
      */
    public function RetrieveFromCache($url)
    {
        // Prepare a statement
        $statement = $this->database->prepare('SELECT data FROM cache WHERE url = ? AND retrieval_timestamp + ttl >= ?');
        if($statement === FALSE)
            throw new APIException('Cache: Unable to create lookup SQL query.');
        
        $statement->bindValue(1, $url);
        $statement->bindValue(2, time(), PDO::PARAM_INT);
        
        // Try to execute the statement
        if($statement->execute() === FALSE)
            throw new APIException('Cache: Unable to execute lookup SQL query.');
        
        if($row = $statement->fetch(PDO::FETCH_ASSOC))
            return $row['data'];
        else
            return FALSE;
    }
    
    /// Adds the URL and data to the database.
    /**
      * \param $url the URL for the request
      * \param $data the data for the URL
      * \param $ttl the time-to-live (TTL) for the data
      */
    public function AddToCache($url, $data, $ttl=null)
    {
        if($ttl == null)
            $ttl = API::$cache_ttl;
        
        // Prepare a statement
        $statement = $this->database->prepare('INSERT INTO cache ( url, data, retrieval_timestamp, ttl ) VALUES (?,?,?,?)');
        if($statement === FALSE)
            throw new APIException('Cache: Unable to create insertion SQL query.');
        
        $statement->bindValue(1, $url);
        $statement->bindValue(2, $data);
        $statement->bindValue(3, time(), PDO::PARAM_INT);
        $statement->bindValue(4, $ttl, PDO::PARAM_INT);
        
        // Try to execute the statement
        if($statement->execute() === FALSE)
            throw new APIException('Cache: Unable to execute insertion SQL query.');
    }
    
    /// Clears all rows from the database.
    public function Clear()
    {
        // Prepare a statement
        $statement = $this->database->query('DELETE FROM cache');
        
        // Try to execute the statement
        if($statement === FALSE || $statement->execute() === FALSE)
            throw new APIException('Cache: Unable to create or execute deletion SQL query.');
    }
    
    /// Removes expired rows from the database.
    public function Cleanup()
    {
        // Prepare a statement
        $statement = $this->database->prepare('DELETE FROM cache WHERE retrieval_timestamp + ttl < ?');
        if($statement === FALSE)
            throw new APIException('Cache: Unable to create deletion SQL query.');
        
        $statement->bindValue(1, time(), PDO::PARAM_INT);
        
        // Try to execute the statement
        if($statement->execute() === FALSE)
            throw new APIException('Cache: Unable to execute deletion SQL query.');
    }
}

?>