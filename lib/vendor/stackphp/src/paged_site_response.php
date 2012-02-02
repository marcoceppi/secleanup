<?php

// A class in this file derives from PagedResponse and
// makes use of the Site class
require_once 'api.php';
require_once 'paged_response.php';
require_once 'site.php';

/// Represents a response that contains a list of sites.
class PagedSiteResponse extends PagedResponse
{
    /// Constructor for a paged site response.
    /**
      * \param $base_url an instance of the URL class
      */
    function __construct($base_url)
    {
        // Call the parent constructor
        parent::__construct($base_url, 'site');
    }
    
    /// Retrieves the data from the URL and verifies the type of items returned.
    /**
      * \param $include_total whether this filter needs to include total number of items
      */
    protected function RetrieveDataFromURL($include_total)
    {
        // Temporarily override the API's default cache TTL
        $old_ttl = API::$cache_ttl;
        API::$cache_ttl = 86400;
        parent::RetrieveDataFromURL($include_total);
        API::$cache_ttl = $old_ttl;
    }
    
    /// Returns the next site in the set.
	/**
	  * \param $fetch_next whether to fetch the next page if there are no more sites in the current page
	  * \return the next site in the set or FALSE if none
	  */
	public function Fetch($fetch_next=TRUE)
	{
        // If a site was fetched, create a Site object to return
        if($site = parent::Fetch($fetch_next=TRUE))
            return new Site($site);
        
        // Otherwise return false
        return FALSE;
    }
}

?>