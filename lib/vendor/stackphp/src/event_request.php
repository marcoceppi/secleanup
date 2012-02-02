<?php

require_once 'paged_request.php';

/// A request for events.
class EventRequest extends PagedRequest
{
    /// Constructor for a badge request.
    /**
      * \param $url the domain name of the site
      * \param $access_token a valid access token
      */
    function __construct($url, $access_token)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url, 'event', 'events');
        
        // This data should not be cached (future work needs
        // to be done to correct this limitation)
        $this->url->SetAccessToken($access_token)->DisableCache();
    }
    
    /// Causes only events that have taken place since the specified time to be returned.
    /**
      * \param $date a timestamp that represents the minimum time that returned events should have
      * \return the current instance
      */
    public function Since($date)
    {
        $this->url->SetQueryStringParameter('since', $date);
        return $this;
    }
}

?>