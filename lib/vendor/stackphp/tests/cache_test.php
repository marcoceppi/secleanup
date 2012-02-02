<?php

//========================================
//  Tests the SQL cache class to ensure
// that all responses are cached properly
//    with respect to their TTL, etc.
//========================================

require_once 'test.php';
require_once '../src/api.php';
require_once '../src/filestore_cache.php';
require_once '../src/sql_cache.php';

class CacheTest extends Test
{
    function __construct()
    {
        $this->name        = 'Cache';
        $this->description = 'Tests the SQL cache to ensure that data can be stored and retrieved correctly and ensures that the TTL value is respected.';
    }
    
    // Runs the test suite on a particular cache instance
    private function TestCache($type, $cache)
    {
        echo "* Testing $type cache...\n";
        
        // Clear the cache just in case
        $cache->Clear();
        
        // Add two values to the cache - one of which
        // has a TTL value of 1
        $cache->AddToCache('a', 'b', 1);
        $cache->AddToCache('c', 'd');
        
        // Pause for 2 seconds
        sleep(2);
        
        if($cache->RetrieveFromCache('a') !== FALSE)
            throw new Exception('Value for "a" stored in cache still available after TTL expired.');
        if($cache->RetrieveFromCache('c') != 'd')
            throw new Exception('Value for "c" in cache does not match stored value.');
        
        // Register this cache with the API to ensure that
        // Stack.PHP methods are using it correctly.
        API::SetCache($cache);
        
        // Get the total number of questions on Stack Apps
        // and then count how many API requests were sent
        API::Site('stackapps')->Questions()->Exec()->Total();
        $num_api_requests = API::GetAPIRequests();
        
        // Now re-issue the same request and compare the number of API requests sent
        API::Site('stackapps')->Questions()->Exec()->Total();
        
        if($num_api_requests != API::GetAPIRequests())
            throw new Exception('API response was not retrieved from the cache.');
        
        // Clear the cache
        $cache->Clear();
        
        // Now perform the request again and make sure that the number
        // of API requests made increases.
        API::Site('stackapps')->Questions()->Exec()->Total();
        
        if($num_api_requests == API::GetAPIRequests())
            throw new Exception('There was an error clearing the cache.');
    }
    
    protected function PerformTest()
    {
        // Test the SQL cache class
        $sqlite_filename = sys_get_temp_dir() . '/stackphp.sqlite';
        
        // Make sure the file is blank
        if(is_file($sqlite_filename))
            if(!unlink($sqlite_filename))
                throw new Exception('The SQLite database file could not be removed.');
        
        $this->TestCache('SQL', new SQLCache(SQLCache::SQLite, $sqlite_filename));
        
        // Test the filestore class
        $this->TestCache('Filestore', new FilestoreCache(sys_get_temp_dir() . '/stackphp'));
    }
}

?>