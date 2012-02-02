<?php

require_once 'api.php';
require_once 'filter.php';

/// Provides a means of easily specifying an API resource.
/**
  * The URL class simplifies the process of specifying API resources
  * by providing convenience functions that make manipulating the different
  * components of a URL easy and safe. The URL class automatically escapes
  * query string parameters as necessary.
  *
  * An API URL is divided into the following ordered parts (some of which are optional):
  * - prefix (for example, <kbd>http://</kbd> or <kbd>https://</kbd>)
  * - domain name (for example, <kbd>stackapps.com</kbd>)
  * - API version (for example, <kbd>1.1</kbd> or <kbd>2.0</kbd>)
  * - the category of the request (for example, <kbd>questions</kbd>)
  * - a single or vectorized list of question IDs
  * - the method (for example, <kbd>comments</kbd>)
  * - a single or vectorized list of tags
  * - any additional parameters needed
  * - query string values (stored as an array of key/value pairs)
  */
class URL
{
    //======================
    // Predefined constants
    //======================
    
    /// Instructs Stack.PHP to automatically select the correct prefix for each request.
    const AutoRequestPrefix     = 0;
    /// Used to perform a standard request using HTTP.
    const StandardRequestPrefix = 1;
    /// Used to perform a secure request using HTTP over TLS (HTTPS).
    const SecureRequestPrefix   = 2;
    
    //=================
    // Public settings
    //=================
    
    /// The default prefix to use for requests
    public static $default_prefix = self::AutoRequestPrefix;
    
    //==========================
    // Private member variables
    //==========================
    
    private $prefix;                     // the prefix to use for this request
    private $category     = null;        // the category of the request being made
    private $ids          = array();     // an array of IDs
    private $method       = null;        // the intended method
    private $tags         = array();     // an array of tags
    private $parameter    = null;        // any additional parameters
    
    private $query_string = array();     // the query string parameters
    
    private $post_data    = array();     // POST parameters
    
    // Whether or not this URL can be cached
    private $can_cache    = TRUE;
    
    /// Constructor for a URL object.
    /**
      * \param $domain_name the domain name of the URL in the form <kbd>example.com</kbd> or <kbd>example</kbd>
      */
    function __construct($domain_name=null)
    {
        // Set the prefix and filter to the default
        $this->prefix = self::$default_prefix;
        $this->SetQueryStringParameter('filter', Filter::$default_filter);
        
        // Set the API key
        $this->SetQueryStringParameter('key',  API::$key);
        
        // With v2.0 of the API, Stack.PHP now requires the domain name of
        // the site to be specified in the query string - so we will add it
        // to the query string now as well as the current API key.
        if($domain_name !== null)
            $this->SetQueryStringParameter('site', $domain_name);
    }
    
    /// Returns the complete URL as a string.
    /**
      * \param $include_key TRUE to include the API key in the returned string
      * \return a string representing the complete URL
      *
      * This method creates a string representation of the URL by
      * combining the parts of the URL.
      */
    public function CompleteURL($include_key=TRUE)
    {
        // Determine the prefix we are going to use
        $prefix = ($this->prefix == self::SecureRequestPrefix)?'https://':'http://';
        
        // Begin constructing the URL
        $url = "{$prefix}api.stackexchange.com/" . API::GetVersion();
        
        // If the category is set, add it to the URL
        if($this->category !== null)
            $url .= "/{$this->category}";
            
        // Next, add any IDs that we have
        if(count($this->ids))
            $url .= '/' . implode(';', $this->ids);
        
        // Now we add the requested data
        if($this->method !== null)
            $url .= "/{$this->method}";
        
        // Add any tags
        if(count($this->tags))
            $url .= '/' . implode(';', $this->tags);
        
        // Check for an additional parameter
        if($this->parameter !== null)
            $url .= "/{$this->parameter}";
        
        // Convert key => value to 'key=value', being sure
        // to properly escape the value. (We have control over the key,
        // so it's not necessary to encode that.)
        $combined_query_string = array();
        
        foreach($this->query_string as $key => $value)
            if($include_key || $key != 'key')
                $combined_query_string[] = "$key=" . urlencode($value);
        
        // Append the query string to the URL
        $url .= '?' . implode('&', $combined_query_string);
        
        return $url;
    }
    
    /// Sets the prefix for the request.
    /**
      * \param $prefix the desired prefix for the request
      * \return the current instance
      *
      * Note: this value will only be honored if URL::$default_prefix is set to
      * URL::AutoRequestPrefix (the default) - otherwise the value provided will
      * be ignored.
      */
    public function SetPrefix($prefix)
    {
        if(self::$default_prefix == self::AutoRequestPrefix)
            $this->prefix = $prefix;
        
        return $this;
    }
    
    /// Sets the method category.
    /**
      * \param $category the category of API requests
      * \return the current instance
      */
    public function SetCategory($category)
    {
        $this->category = $category;
        return $this;
    }
    
    /// Adds items to the list of IDs.
    /**
      * \param $id the ID of the item to add or an array of IDs
      * \return the current instance
      */
    public function AddID($id)
    {
        if(is_array($id))
            $this->ids = array_merge($this->ids, $id);
        else
            $this->ids[] = $id;
        
        return $this;
    }
    
    /// Sets the method to invoke.
    /**
      * \param $method the name of the method
      * \return the current instance
      */
    public function SetMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    /// Adds items to the list of tags.
    /**
      * \param $tag the tag to add or an array of tags
      * \return the current instance
      */
    public function AddTag($tag)
    {
        if(is_array($tag))
            $this->tags = array_merge($this->tags, $tag);
        else
            $this->tags[] = $tag;
        
        return $this;
    }
    
    /// Sets the additional parameter.
    /**
      * \param $parameter the additional parameter
      * \return the current instance
      */
    public function SetParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }
    
    /// Sets the specified filter for the request.
    /**
      * \param $filter a filter ID or a Filter object
      * \return the current instance
      */
    public function SetFilter($filter)
    {
        if(is_string($filter))
            $this->SetQueryStringParameter('filter', $filter);
        else
            $this->SetQueryStringParameter('filter', $filter->GetID());
        
        return $this;
    }
    
    /// Sets the specified query string parameter to the specified value.
    /**
      * \param $key the query string parameter to be modified
      * \param $value the new value for the parameter
      * \return the current instance
      *
      * Note: if a value already exists for a parameter, this method will
      * overwrite it.
      */
    public function SetQueryStringParameter($key, $value)
    {
        $this->query_string[$key] = $value;
        return $this;
    }
    
    /// Returns the POST data associated with this URL.
    /**
      * \return the POST data or null if none
      */
    public function GetPOSTData()
    {
        return count($this->post_data)?$this->post_data:null;
    }
    
    /// Associates POST data with this URL.
    /**
      * \param $assoc_array an array of key => value pairs to add
      * \return the current instance
      *
      * Note: if a key in $assoc_array matches an existing key in the
      * POST data, then the new value will replace the old one.
      */
    public function SetPOSTData($assoc_array)
    {
        $this->post_data = array_merge($this->post_data, $assoc_array);
        return $this;
    }
    
    /// Adds the specified access token to the request.
    /**
      * \param $access_token the access token for the request
      * \return the current instance
      *
      * Note: the access token is only needed for authenticated methods.
      * Also, calling this method will cuase this request to switch to
      * HTTPS because that is a requirement for all authenticated methods.
      */
    public function SetAccessToken($access_token)
    {
        $this->SetPrefix(URL::SecureRequestPrefix);
        $this->SetQueryStringParameter('access_token', $access_token);
        return $this;
    }
    
    /// Indicates that this URL's response cannot be cached.
    /**
      * \return the current instance
      */
    public function DisableCache()
    {
        $this->can_cache = FALSE;
        return $this;
    }
    
    /// Returns whether this URL can be cached or not.
    /**
      * \return TRUE if this request can be cached
      */
    public function CanCache()
    {
        return $this->can_cache;
    }
}

?>
