<?php

/** \mainpage
  *
  * \section intro Introduction
  * Stack.PHP is a PHP library that makes it easy to use the Stack
  * Exchange API in web applications and command-line utilities.
  *
  * Stack.PHP offers a number of objects, classes, and convenience
  * methods that invoke the appropriate API methods behind the scenes.
  *
  * \section features Features
  * Stack.PHP comes with many features that will make developing
  * applications that use the API much simpler:
  *
  * - <b>Support for <i>every</i> method and parameter.</b> Every single
  *   method and parameter in the API is exposed in some way or another
  *   in Stack.PHP. There is no need to ever manually specify a URL to
  *   achieve a certain result.
  * - <b>Built-in OAuth consumer.</b> You can access authenticated methods in
  *   the API easily with Stack.PHP's Auth class. Both explicit (server-side)
  *   and implicit (client-side) authentication are supported.
  * - <b>Built-in request throttling.</b> Never worry about the speed at
  *   which requests are dispatched anymore. Stack.PHP will delay your
  *   requests to ensure that they comply with the published guidelines.
  * - <b>Automatic pagination.</b> Fetching multiple pages of content with
  *   the API can be tricky. Stack.PHP takes all of the confusion and guesswork
  *   out of pagination by providing you with an iterator-like object that
  *   will take care of fetching the pages for you.
  * - <b>A powerful and extensible caching system.</b> It doesn't make sense to
  *   hit the API servers with the same request over and over again. Stack.PHP
  *   offers a flexible set of caching classes that make it extremely simple to
  *   cache requests. Both an SQL and a filestore caching class are included with
  *   Stack.PHP - and if they don't suit your needs, you can easily create your
  *   own caching class.
  *
  * \section starting Getting Started
  * You can find a terrific introduction to basic usage of Stack.PHP here:
  * https://docs.google.com/document/pub?id=11n_dp6t2jpPcgqNuoEOO0fwLvKxYp_HH-zGoycAnUmY
  */

require_once 'api_exception.php';
require_once 'filter.php';
require_once 'inbox_item_request.php';
require_once 'paged_response.php';
require_once 'paged_site_response.php';
require_once 'site.php';
require_once 'url.php';

/// Main class which provides access to all sites and routes.
/**
  * This class provides static methods for storing and retrieving
  * information needed for requests as well as means for creating
  * instances of the Site class which is used for all other entry
  * points.
  */
class API
{
    //====================
    //  Public settings
    //====================
    
    /// The API key to use for making requests.
    /**
      * This value is not strictly required as the API allows up to 300 requests
      * per day without an API key. However, for any production quailty application,
      * you are highly encouraged to register for one as it increases your daily quota
      * to 10000 requests per day. You also require an API key for authenticated requests.
      */
    public static $key = '';
    
    /// The TTL value to use when caching requests (in seconds).
    /**
      * This value determines the duration of time that requests will be cached for when
      * a cache has been set with API::SetCache(). The default is 10 minutes. Note that
      * certain requests will override this value when necessary to comply with the API's
      * best usage policy.
      */
    public static $cache_ttl = 600;
    
    //==================================
    // Private settings used internally
    //==================================
    
    // Whether to enable verbose mode (used for debugging)
    private static $debug = FALSE;
    
    // The current version of the API
    private static $api_version = '2.0'; // We want it to be treated as a string
    
    // Request throttling variables
    private static $request_timeout = 0.033333333333;  // 1 / 30
    private static $last_request    = 0;
    
    // Statistical variables
    private static $total_requests  = 0;  // the number of requests issued by request classes
    private static $requests_to_api = 0;  // the number of requests actually made to the API
    
    // Cache variables
    private static $cache = null;
    
    /// Enables / disables the debug mode.
    /**
      * \param $enable_debug TRUE to enable debug mode
      *
      * Enabling the debug mode will produce a lot of extra output. This will simply
      * be sent to STDOUT for a CLI-based application and will be <kbd>echo</kbd>'d to
      * the client when used in a web application. If the latter behavior is not desired,
      * you can use PHP's output buffering methods to capture the output instead.
      *
      * Debug mode also enables additional information in the response by changing one
      * of the default filters that are used.
      */
    public static function EnableDebugMode($enable_debug=TRUE)
    {
        self::$debug = $enable_debug;
        
        // Enable the debug filter
        Filter::$default_filter = Filter::$default_pagination_filter = '!DnBO_';
    }
    
    /// Returns the version of the API that Stack.PHP is using.
    /**
      * \return the current API version
      */
    public static function GetVersion()
    {
        return self::$api_version;
    }
    
    /// Fetches the raw response from the provided URL.
    /**
      * \param $url the URL to retrieve
      * \param $post_data an array of data to POST
      * \return raw data from the URL
      *
      * All HTTP requests to the API are routed through this method which
      * ensures that the proper encoding header is sent (this also informs
      * curl that we may be expecting compressed data in return). This method
      * also accepts an array of POST values that are converted to a properly
      * encoded string.
      */
    public static function GetRawData($url, $post_data=null)
    {
        // Initialize curl
        $ch = curl_init();
        
        // Set the options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');  // Required by API
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // If POST data was supplied, add it now
        if($post_data !== null)
        {
            // Generate the POST string
            $post_array = array();
            foreach($post_data as $key => $value)
                $post_array[] = $key . '=' . urlencode($value);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $post_array));
        }
        
        // Get the data returned by the method and close our handle
        $data = curl_exec($ch);
        
        // Make sure the returned data is not FALSE, indicating an error
        if($data === FALSE)
        {
            // Grab the error and close curl
            $error_desc = curl_error($ch);
            curl_close($ch);
            
            throw new APIException('Curl was unable to retrieve the data from the specified URL.',
                                   $url, $error_desc);
        }
        
        // Close the handle and return the data we retrieved
        curl_close($ch);
        return $data;
    }
    
    /// Fetches JSON data from the provided URL.
    /**
      * \param $url an instance of the URL class containing the URL to retrieve
      * \return JSON-decoded data from the URL
      *
      * This method retrieves data from an API route and decodes the JSON returned.
      * Invoking this method does not necessarily make an API call if a cache has been
      * set with API::SetCache(). Parameters that do not affect the data returned are
      * removed and the resulting URL is looked up in the cache. If it exists, the data
      * is returned immediately using the cached response.
      *
      * This method also ensures that the request rate-limit is adhered to. This method
      * will sleep just long enough to ensure that the request does not exceed the rate-limit.
      */
    public static function GetJSON($url)
    {
        // Increment the request count regardless of whether we use the cache or not
        ++self::$total_requests;
        
        // First check the cache (if available) for this request, dropping
        // the API key so that we don't make extra requests with different keys.
        if(self::$cache !== null && $data = self::$cache->RetrieveFromCache($url->CompleteURL(FALSE)))
        {
            if(self::$debug)
                echo "[DEBUG]: Found '" . $url->CompleteURL() . "' in cache.\n";
            
            // Unserialize the data and return it
            return unserialize($data);
        }
        
        // Log the request if in debug mode
        if(self::$debug)
            echo "[DEBUG]: Issuing request '" . $url->CompleteURL() . "'\n";
        
        // It's not in the cache, so we are making a request and the request-to-api
        // count should be incremented
        ++self::$requests_to_api;
        
        // Calculate the elapsed time since the last request
        $elapsed_time = microtime(TRUE) - self::$last_request;
        
        // If not enough time has elapsed to comply with rate-limit, then wait until it has
        if($elapsed_time < self::$request_timeout)
        {
            $sleep_duration = self::$request_timeout - $elapsed_time;
            
            // If debugging is enabled, indicate how long we are pausing for
            if(self::$debug)
                printf("[DEBUG]: Pausing for %.3f seconds...\n", $sleep_duration);
            
            usleep($sleep_duration);
        }
        
        // Record the time that this request was made - we need to record this
        // before any inspection of the data takes place because the rate-limit
        // still applies to errors, etc.
        self::$last_request = microtime(TRUE);
        
        // Retrieve the data
        $data = self::GetRawData($url->CompleteURL(), $url->GetPOSTData());
        
        // Try to decode the data
        $data = json_decode($data, TRUE);
        if($data === null)
            throw new APIException('There was an error decoding the JSON data returned by the API.', $url);
        
        // Check for the error contents
        if(isset($data['error_id']))
            throw new APIException('An API error has occurred.',
                                   $url,
                                   $data['error_message'],
                                   $data['error_id']);
        
        // The data is valid, so add it to the cache if applicable using the default TTL
        if(self::$cache !== null && $url->CanCache())
            self::$cache->AddToCache($url->CompleteURL(FALSE), serialize($data), self::$cache_ttl);
        
        // return the JSON data
        return $data;
    }
    
    /// Sets the internal cache to the provided instance of a cache class.
    /**
      * \param $cache_instance an instance of a class derived from CacheBase
      * \param $cleanup whether to perform cleanup of expired entries in the cache
      */
    public static function SetCache($cache_instance, $cleanup=TRUE)
    {
        self::$cache = $cache_instance;
        
        // If requested, perform necessary cleanup
        if($cleanup)
            self::$cache->Cleanup();
    }
    
    /// Returns the user accounts that correspond to the specified account IDs.
    /**
      * \param $ids either a single account ID or an array of account IDs
      * \return a PagedResponse object
      */
    public static function AssociatedUsers($ids)
    {
        $url = new URL();
        $url->SetCategory('users');
        $url->AddID($ids);
        $url->SetMethod('associated');
        
        return new PagedResponse($url, 'network_user');
    }
    
    // De-authenticates the current application for the specified access tokens.
	/**
      * \param $access_token either a single access token or an array of access tokens
      * \return a PagedRequest object
      */
    public static function DeauthenticateApplication($access_token)
    {
        $url = new URL();
        $url->SetCategory('apps');
        $url->AddID($access_token);
        $url->SetMethod('de-authenticate');
        
        return new PagedResponse($url, 'access_token');
    }
    
    /// Returns the inbox items for the specified access token.
    /**
      * \param $access_token a valid access token
      * \return an InboxItemRequest object
      */
    public static function Inbox($access_token)
    {
        return new InboxItemRequest(new URL(), $access_token);
    }
    
    /// Invalidates the supplied access tokens.
    /**
      * \param $access_token either a single access token or an array of access tokens
      * \return a PagedRequest object
      */
    public static function InvalidateAccessTokens($access_token)
    {
        $url = new URL();
        $url->SetCategory('access-tokens');
        $url->AddID($access_token);
        $url->SetMethod('invalidate');
        
        return new PagedResponse($url, 'access_token');
    }
    
    /// Retrieves information about the specified access tokens.
    /**
      * \param $access_token either a single access token or an array of access tokens
      * \return a PagedRequest object
      */
    public static function ReadAccessTokens($access_token)
    {
        $url = new URL();
        $url->SetCategory('access-tokens');
        $url->AddID($access_token);
        
        return new PagedResponse($url, 'access_token');
    }
    
    /// Creates a Site object for the provided site.
    /**
      * \param $site_domain a portion of the site's domain name
      * \return a Site object
      */
    public static function Site($site_domain)
    {
        return new Site($site_domain);
    }
    
	/// Returns a paged response of all Stack Exchange sites.
	/**
	  * \return a PagedSiteResponse object
	  */
	public static function Sites()
	{
        $url = new URL();
        $url->SetMethod('sites');
        
	    return new PagedSiteResponse($url);
	}
    
    /// Returns the total number of requests made for API data.
    /**
      * \return the total number of requests
      *
      * This number technically represents the total number of times that
      * API::GetJSON() has been called. If no cache has been set, this number
      * is equal to the number of API requests actually made.
      */
    public static function GetTotalRequests()
    {
        return self::$total_requests;
    }
    
    /// Returns the number of requests actually sent to the API.
    /**
      * \return the number of requests sent to the API
      *
      * This number represents the number of requests actually sent to
      * the API servers. In the absence of a cache, this number is identical
      * to API::GetTotalRequests().
      */
    public static function GetAPIRequests()
    {
        return self::$requests_to_api;
    }
}

?>