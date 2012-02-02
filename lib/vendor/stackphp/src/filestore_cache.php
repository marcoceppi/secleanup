<?php

require_once 'api_exception.php';
require_once 'cache_base.php';

/// Provides a means to use the filesystem for caching purposes.
/**
  * The FilestoreCache uses the filesystem to cache data from the
  * API. This is accomplished by creating a file 'index' that keeps
  * track of the filename=>data mapping for storing the information.
  * This class makes use of the atomic function <kbd>flock</kbd> for
  * ensuring atomic access to the index. You will not be able to
  * guarantee consistency if this method is not available.
  */
class FilestoreCache implements CacheBase
{
    // Information for the filestore
    private $directory = '';
    
    /// Constructor for a filestore cache.
    /**
      * \param $directory an empty directory suitable for storing cached requests
      * \param $attempt_to_create whether to attempt to create the directory if it does not exist
      */
    function __construct($directory, $attempt_to_create=TRUE)
    {
        $this->directory = $directory;
        
        // Check if we need to create the directory
        if($attempt_to_create && !is_dir($directory))
            if(!mkdir($directory))
                throw new APIException("Cache: The specified directory could not be created.");
    }
    
    // Reads the contents of a file atomically
    private function AtomicRead($filename)
    {
        // Attempt to open the file
        $file = fopen($filename, 'r');
        
        // Make sure we opened the file
        if($file === FALSE)
            throw new APIException('Cache: Could not open the file for reading.');
        
        // Now lock the file and determine its size
        flock($file, LOCK_SH);
        fseek($file, 0, SEEK_END);
        $length = ftell($file);
        
        if($length)
        {
            fseek($file, 0);
            $file_contents = fread($file, $length);
        }
        else
            $file_contents = '';
        
        // Unlock and close the file handle
        flock($file, LOCK_UN);
        fclose($file);
        
        return $file_contents;
    }
    
    // Loads the index from disk
    private function LoadIndex()
    {
        if(!is_file("{$this->directory}/index"))
            return array();
        
        // Get the contents of the file
        $index_contents = $this->AtomicRead("{$this->directory}/index");
        
        // Split the lines into an array
        $index_lines = explode("\n", $index_contents);
        
        // Create the array that will contain the index
        $cache_table = array();
        
        if($index_lines !== FALSE)
        {
            // Loop through each of the lines
            foreach($index_lines as $line)
            {
                // If the line is not blank and it contains '\t',
                // add it to the array
                if(trim($line) != '' && strpos($line, "\t") !== FALSE)
                {
                    $data = explode("\t", $line, 2);
                    $cache_table[$data[0]] = unserialize($data[1]);
                }
            }
        }
        
        return $cache_table;
    }
    
    // Writes the index to disk
    private function StoreIndex($cache_table)
    {
        // Attempt to open the index file
        $index = fopen("{$this->directory}/index", 'w');
        
        // Make sure we opened the index
        if($index === FALSE)
            throw new APIException('Cache: Could not open the index file for writing.');
        
        // Now lock the index
        flock($index, LOCK_EX);
        
        // Loop through the data, serializing it
        foreach($cache_table as $key => $value)
        {
            fwrite($index, "$key\t" . serialize($value) . "\n");
        }
        
        // Unlock the file and close it
        flock($index, LOCK_UN);
        fclose($index);
    }
    
    /// Retrieves the data for the given URL from the filestore.
    /**
      * \param $url the URL to retrieve the data for
      * \return the data for the URL or FALSE
      */
    public function RetrieveFromCache($url)
    {
        // Load the index
        $cache_table = $this->LoadIndex();
        
        // Now check to see if the item is in the cache
        if(isset($cache_table[$url]))
        {
            // Make sure that the retrieval_timestamp + ttl are beyond
            // the current time
            $entry = $cache_table[$url];
            
            if($entry['retrieval_timestamp'] + $entry['ttl'] >= time())
                return $this->AtomicRead("{$this->directory}/{$entry['filename']}");
            else
                return FALSE;
        }
        else
            return FALSE;
    }
    
    /// Adds the URL and data to the filestore.
    /**
      * \param $url the URL for the request
      * \param $data the data for the URL
      * \param $ttl the time-to-live (TTL) for the data
      */
    public function AddToCache($url, $data, $ttl=null)
    {
        if($ttl == null)
            $ttl = API::$cache_ttl;
        
        // Load the index
        $cache_table = $this->LoadIndex();
        
        // Now check to see if the item is in the cache
        if(isset($cache_table[$url]))
        {
            // Update the data and get the filename
            $cache_table[$url]['retrieval_timestamp'] = time();
            $cache_table[$url]['ttl']                 = $ttl;
            $filename = $cache_table[$url]['filename'];
        }
        else
        {
            // Generate a unique filename and the array
            // to serialize.
            $filename = microtime(TRUE);
            $entry_data = array('filename'            => $filename,
                                'retrieval_timestamp' => time(),
                                'ttl'                 => $ttl);
            
            // Add it to the array
            $cache_table[$url] = $entry_data;
        }
        
        // Now write the data to the file
        $data_file = fopen("{$this->directory}/$filename", 'w');
        if($data_file === FALSE)
            throw new APIException('Cache: Could not open data file for writing.');
        
        flock($data_file, LOCK_EX);
        fwrite($data_file, $data);
        flock($data_file, LOCK_UN);
        fclose($data_file);
        
        // Write the index
        $cache_table = $this->StoreIndex($cache_table);
    }
    
    /// Clears all entries from the filestore.
    /**
      * Note: this method also deletes the index.
      */
    public function Clear()
    {
        // Load the index
        $cache_table = $this->LoadIndex();
        
        // Delete each of the files
        foreach($cache_table as $key => $value)
            unlink("{$this->directory}/{$value['filename']}");
        
        // Delete the index file if it exists
        if(is_file("{$this->directory}/index"))
            unlink("{$this->directory}/index");
    }
    
    /// Removes expired entries from the filestore.
    public function Cleanup()
    {
        // Load the index
        $cache_table = $this->LoadIndex();
        
        // Delete each of the files that have expired
        foreach($cache_table as $key => $value)
            if($value['retrieval_timestamp'] + $value['ttl'] < time())
            {
                // Delete the entry and delete the file
                unset($cache_table[$key]);
                unlink("{$this->directory}/{$value['filename']}");
            }
        
        // Write the index
        $cache_table = $this->StoreIndex($cache_table);
    }
}

?>