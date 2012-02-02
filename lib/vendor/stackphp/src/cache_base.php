<?php

/// Represents a means of caching API responses.
/**
  * Note that some requests will disable the cache if the response contains authenticated
  * data. This is done as a security precaution to prevent information leakage.
  */
interface CacheBase
{
    /// Retrieves the data for the given URL from the cache.
    /**
      * \param $url the URL to retrieve the data for
      * \return the data for the URL or FALSE
      */
    public function RetrieveFromCache($url);
    
    /// Adds the URL and data to the cache.
    /**
      * \param $url the URL for the request
      * \param $data the data for the URL
      * \param $ttl the time-to-live (TTL) for the data
      */
    public function AddToCache($url, $data, $ttl=null);
    
    /// Clears all entries from the cache.
    public function Clear();
    
    /// Removes expired entries from the cache.
    public function Cleanup();
}

?>