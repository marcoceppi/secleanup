<?php

require_once 'api.php';
require_once 'api_exception.php';
require_once 'filter.php';
require_once 'url.php';

/// An object capable of requesting paged data sequentially.
/**
  * This class is returned by a call to PagedRequest::Exec() and provides
  * a means of stepping through the items returned from your request. By
  * default, additional pages are requested and returned behind the scenes
  * to provide a seamless stream of items.
  *
  * You fetch the next item in the sequence using PagedResponse::Fetch()
  * which returns FALSE when no more items are available. This class is also
  * aware of what filters need to be used to obtain the desired information.
  * For example, to call PagedResponse::Total(), this class needs to have either
  * received a reply containing the total number of items or it will then issue
  * an appropriate request to obtain the information.
  */
class PagedResponse
{
	// The URL that will serve as the base for the requests
    private $url;
    
    // The name of the items expected to be returned - used for
    // optional checking of return type (enabled by default).
    private $return_object;
    
    // Current set of data
	private $data = null;
    
    // Data used for enumeration
    private $index = 0;   // index into the current page
	private $total = -1;  // total items (-1 for unknown)
    
    // Additional data used for enumeration
    private $pagesize = 30;   // the number of items in a page (default: 30)
	private $page     = 1;   // the current page
    
    /// Constructor for a response.
	/**
	  * \param $base_url an instance of the URL class
	  * \param $return_object the name of the object the items will be returned in
	  */
    function __construct($base_url, $return_object)
	{
	    $this->url           = $base_url;
        $this->return_object = $return_object;
	}
    
    /// Retrieves the data from the URL and verifies the type of items returned.
    /**
      * \param $include_total whether this filter needs to include total number of items
      */
    protected function RetrieveDataFromURL($include_total)
    {
        $url_copy = clone $this->url;
        if($include_total)
            $url_copy->SetFilter(Filter::$default_pagination_filter);
        
        $this->data = API::GetJSON($url_copy);
        
        // Make sure that the returned object contains the correct items - this
        // is done by checking for the 'type' item in the response. Note that we
        // are not guaranteed that it will be there.
        if(isset($this->data['type']) && $this->data['type'] != $this->return_object)
            throw new APIException("Expected '{$this->return_object}' items in server response but received {$this->data['type']} items.");
        
        // Set the index to 0 and set the total number of items
        $this->index = 0;
    }
	
	/// Returns the next item in the set.
	/**
	  * \param $fetch_next whether to fetch the next page if there are no more items in the current page
	  * \return the next item in the set or FALSE if none
	  */
	public function Fetch($fetch_next=TRUE)
	{
        // If the current data is empty, then no API requests
        // have been made yet. Fetch the first page (if the user
        // specified a page, it would have been fetched).
        if($this->data == null)
            $this->Page();
        
        // Now we are guaranteed to have something in 'data'.
        // Make sure we don't go beyond the total number of items.
	    if($this->total == -1)
        {
            if($this->index >= count($this->data['items']) && $this->index < $this->pagesize)
                return false;
        }
        else
        {
            if(($this->page - 1) * $this->pagesize + $this->index >=  // the absolute offset
               $this->total)
		        return FALSE;
        }
        
		// Now check to see if we need to fetch a new page.
		// This happens if we have no more data and the index
        // is past the current page.
		if($this->index >= $this->pagesize)
		{
            if($fetch_next)
		        $this->Page($this->page + 1);
		    else
			    return FALSE;
		}
		
        // Otherwise we return the next item. Simple!
		return $this->data['items'][$this->index++];
	}
    
    /// Resets the internal index.
    /**
      * \param $reset_all whether to reset the index to the beginning of all pages
      */
    public function Reset($reset_all=TRUE)
    {
        if($reset_all)
        {
            // By setting the page to 1 and the data
            // to null, we force the next 'Fetch' to
            // retrieve the very first page.
            $this->page = 1;
            $this->data = null;
        }
        else
            $this->index = 0;
    }
	
	/// Fetches the specified page and resets the index.
	/**
	  * \param $page the page number to fetch
      * \param $include_total whether this filter needs to include total number of items
	  * \return the current instance
      *
      * Note: $page defaults to either the first page or the most recently specified page
	  */
	public function Page($page=null, $include_total=FALSE)
	{
        if($page === null)
            $page = $this->page;
        
        // Set the new current page
		$this->page = $page;
        $this->url->SetQueryStringParameter('page', $page);
        
        // Retrieve the page
        $this->RetrieveDataFromURL($include_total);
        
        // Get the total number of items
        if(isset($this->data['total']))
            $this->total = $this->data['total'];
        
        // Set the pagesize
        if(isset($this->data['pagesize']))
            $this->pagesize = $this->data['pagesize'];
		
	    return $this;
	}
    
    /// Sets the pagesize to the specified value.
	/**
	  * \param $pagesize the number of items that each page should contain
	  * \return the current instance
      *
      * Note: this method should not be called after the first request is
      * made as it will invalidate the internal index.
	  */
    public function Pagesize($pagesize)
    {
        // Set the new pagesize
        $this->pagesize = $pagesize;
        $this->url->SetQueryStringParameter('pagesize', $pagesize);
        
        return $this;
    }
    
    /// Returns the total number of items.
    /**
      * \param $all_pages TRUE to include the total of all pages
      * \return the total number of items
      *
      * This method's behavior depends heavily on its arguments and the current
      * filter. If the filter provides 'total' in the response, then this method
      * will return that value by default. If not, then a separate request will
      * be made to retrieve that data using a custom filter.
      *
      * If you merely want the total number of items in the current page, then simply
      * use FALSE for $all_pages. If no data has been fetched yet, this method will
      * fetch the default page (likely the first).
      *
      * Note that you do not need access to 'total' to simply enumerate all items across
      * all pages. Use code like this:
      *
      * <pre>while($item = $response->Fetch())</pre>
      *
      * This will continue to fetch the next page even if 'total' is not returned in the
      * response.
      */
    public function Total($all_pages=TRUE)
    {
        if($all_pages)
        {
            // If no data or total missing from response, fetch the total
            if($this->data === null || $this->total == -1)
                $this->Page(null, TRUE);
        
            return $this->total;
        }
        else
        {
            if($this->data === null)
                $this->Page();
            
            return count($this->data['items']);
        }
    }
}

?>