<?php

require_once 'paged_request.php';

/// A request for items in a user's inbox.
class InboxItemRequest extends PagedRequest
{
    /// Constructor for an inbox item request.
    /**
      * \param $url the domain name of the site or a URL object
      * \param $access_token a valid access token
      */
    function __construct($url, $access_token=null)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url,
                            'inbox_item',
                            null,
                            'inbox');
        
        // Disable the cache for this request if we have an access token
        if($access_token !== null)
            $this->url->SetAccessToken($access_token)->DisableCache();
    }
    
    /// Retrieves only unread items from the users inbox.
    /**
      * \return the current instance
      */
    public function Unread()
    {
        $this->url->SetParameter('unread');
        return $this;
    }
    
    /// Causes only inbox items that were added later than the specified time to be returned.
    /**
      * \param $date a timestamp that represents the minimum time that returned items should have
      * \return the current instance
      */
    public function Since($date)
    {
        $this->url->SetQueryStringParameter('since', $date);
        return $this;
    }
}

?>