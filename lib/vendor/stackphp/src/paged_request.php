<?php

require_once 'paged_response.php';
require_once 'url.php';

/// A request for a paginated resource.
/**
  * Note: if you used a previous version of Stack.PHP, this class now supersedes the
  * RequestBase class. The separation was due to some API methods returning non-paged
  * responses - but this is no longer the case.
  * 
  * All request classes derive from this class. This class provides the core functionality
  * common to all derived classes, such as initializing the URL class, setting certain
  * common parameters, and issuing requests.
  */
class PagedRequest
{
    /// The URL that will be modified as methods are called.
    protected $url;
    
    // The type of data expected to be returned
    private $return_object = '';
    
    /// Constructor for a paged request.
    /**
      * \param $url either the domain name of the site or a URL object
      * \param $return_object the type of data expected to be returned
      * \param $category the category of the requests being made
      * \param $method the method to invoke
      */
    function __construct($url, $return_object, $category=null, $method=null)
    {
        // If the first parameter is an object, then we assume it
        // is a URL instance and set our URL member to it. Otherwise
        // set the domain name.
        if(is_object($url))
            $this->url = $url;
        else
            $this->url = new URL($url);
        
        $this->return_object = $return_object;
        
        // Apply any optional paremeters passed along
        if($category !== null) $this->url->SetCategory($category);
        if($method !== null) $this->url->SetMethod($method);
    }
    
    /// Performs the HTTP request.
    /**
      * \return a PagedResponse object that can be used to retrieve the items
      */
    public function Exec()
    {
        return new PagedResponse($this->url, $this->return_object);
    }
    
    /// Adds the provided ID(s) to the request.
    /**
      * \param $id either a single ID or an array of IDs
      * \return the current instance
      */
    public function ID($id)
    {
        $this->url->AddID($id);
        return $this;
    }
    
    /// Adds the provided tags to the request.
    /**
      * \param $tag either a single tag or an array of tags
      * \return the current instance
      */
    public function Tag($tag)
    {
        $this->url->AddTag($tag);
        return $this;
    }
    
    /// Ensures that only items created after the specified date are returned in the response.
    /**
      * \param $date a timestamp representing the minimum date for returned items
      * \return the current instance
      */
    public function FromDate($date)
    {
        $this->url->SetQueryStringParameter('fromdate', $date);
        return $this;
    }
    
    /// Ensures that only items created before the specified date are returned in the response.
    /**
      * \param $date a timestamp representing the maximum date for returned items
      * \return the current instance
      */
    public function ToDate($date)
    {
        $this->url->SetQueryStringParameter('todate', $date);
        return $this;
    }
    
    /// Sets the minimum value for the range of the current sort.
    /**
      * \param $minimum the minimum value
      * \return the current instance
      */
    public function Min($minimum)
    {
        $this->url->SetQueryStringParameter('min', $minimum);
        return $this;
    }
    
    /// Sets the maximum value for the range of the current sort.
    /**
      * \param $maximum the maximum value
      * \return the current instance
      */
    public function Max($maximum)
    {
        $this->url->SetQueryStringParameter('max', $maximum);
        return $this;
    }
    
    /// Sets the specified filter for the request.
    /**
      * \param $filter a filter ID or a Filter object
      * \return the current instance
      */
    public function Filter($filter)
    {
        $this->url->SetFilter($filter);
        return $this;
    }
    
    /// Returns the URL constructed from this request.
    /**
      * \return a string containing the request URL
      */
    public function URL()
    {
        return $this->url->CompleteURL();
    }
    
    /// Causes the items to be returned in ascending order.
    /**
      * \return the current instance
      */
    public function Ascending()
    {
        $this->url->SetQueryStringParameter('order', 'asc');
        return $this;
    }
    
    /// Causes the items to be returned in descending order.
    /**
      * \return the current instance
      */
    public function Descending()
    {
        $this->url->SetQueryStringParameter('order', 'desc');
        return $this;
    }
    
    /// Returns only the items that contain the specified text in their names.
    /**
      * \param $name the name to filter by
      * \return the current instance
      */
    public function InName($name)
    {
        $this->url->SetQueryStringParameter('inname', $name);
        return $this;
    }
    
    /// Sort the results by the provided sort method.
    /**
      * \param $method the method to sort the results by
      * \return the current instance
      *
      * Note: there are specific sorting methods available, so use this method only
      * when the desired sort is determined at runtime.
      */
    public function SortBy($method)
    {
        $this->url->SetQueryStringParameter('sort', $method);
        return $this;
    }
    
    /// Orders the returned items by activity.
    /**
      * \return the current instance.
      */
    public function SortByActivity()
    {
        $this->SortBy('activity');
        return $this;
    }
    
    /// Orders the returned items by creation date.
    /**
      * \return the current instance
      */
    public function SortByCreation()
    {
        $this->SortBy('creation');
        return $this;
    }
    
    /// Orders the returned items by their name.
    /**
      * \return the current instance
      */
    public function SortByName()
    {
        $this->SortBy('name');
        return $this;
    }
    
    /// Orders the returned items by their score.
    /**
      * \return the current instance
      */
    public function SortByVotes()
    {
        $this->SortBy('votes');
        return $this;
    }
}

?>