<?php

require_once 'api.php';
require_once 'api_exception.php';

/// Provides access to the methods that require OAuth 2.0 authentication.
/**
  * This class manages settings and provides methods for accessing private
  * data on Stack Exchange sites. All of the methods below will help you
  * authenticate and gain the appropriate permissions to access the data.
  */
class Auth
{
    //====================
    //  Public constants
    //====================
    
    /// Provides access to the user's inbox.
    const ReadInbox = 'read_inbox';
    
    /// Provides an access token that does not expire.
    const NoExpiry = 'no_expiry';
    
    //====================
    //  Public settings
    //====================
    
    /// The client ID for the application.
    public static $client_id = null;
    
    /// The client secret for the application (only needed if the explicit OAuth flow is used).
    public static $client_secret = null;
    
    /// Begins the explicit authentication flow.
    /**
      * \param $redirect_uri the URL to redirect to after authentication is complete
      * \param $scope one of Auth::ReadInbox (the default) or Auth::NoExpiry
      * \param $state an optional value that will be returned later
      *
      * Note: this method <b>must</b> be called before any output is sent to the client
      * since it will issue a 'location' HTTP header. Any output after calling this method
      * will likely not be seen by the client.
      *
      * Once the client requests $redirect_uri, you must call Auth::CompleteExplicitFlow()
      * to complete the authentication process.
      */
    public static function BeginExplicitFlow($redirect_uri, $scope=self::ReadInbox, $state=null)
    {
        $request_url = 'https://stackexchange.com/oauth?client_id=' .
                       self::$client_id . "&scope=$scope&redirect_uri=" .
                       urlencode($redirect_uri);
        
        if($state !== null)
            $request_url .= '&state=' . urlencode($state);
        
        // Now issue a redirect to our specified URL
        header("Location: $request_url");
    }
    
    /// Completes the explicit authentication flow.
    /**
      * \param $redirect_uri the URL provided to Auth::BeginExplicitFlow()
      * \return a valid access token
      *
      * Note: if anything goes wrong, this method will throw an APIException.
      */
    public static function CompleteExplicitFlow($redirect_uri)
    {
        if(!isset($_GET['code']))
            throw new APIException('The "code" query string parameter is missing from the URL.');
        
        // Now make the request to retrieve the access token
        $request_parameters = array('client_id'     => self::$client_id,
                                    'client_secret' => self::$client_secret,
                                    'code'          => $_GET['code'],
                                    'redirect_uri'  => $redirect_uri);
        
        // Parse the returned data as if it were a query string
        $data = parse_str(API::GetRawData('https://stackexchange.com/oauth/access_token', $request_parameters));
        
        // We now have access_token in the local scope - return it
        if(isset($access_token))
            return $access_token;
        else
            throw new APIException('The "access_token" parameter was missing from the server\'s response.');
    }
}

?>