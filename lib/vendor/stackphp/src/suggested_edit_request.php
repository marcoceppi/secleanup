<?php

require_once 'paged_request.php';

/// A request for suggested edits.
class SuggestedEditRequest extends PagedRequest
{
    /// Constructor for a suggested edit request.
    /**
      * \param $url the domain name of the site or a URL object
      * \param $method the method being called
      */
    function __construct($url, $method=null)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url,
                            'suggested_edit',
                            ($method === null)?'suggested-edits':null,
                            ($method !== null)?$method:null);
    }
    
    /// Causes suggested edits to be sorted according to their date of approval.
    /**
      * \return the current instance
      */
    public function SortByApproval()
    {
        $this->SortBy('approval');
        return $this;
    }
    
    /// Causes suggested edits to be sorted according to their date of rejection.
    /**
      * \return the current instance
      */
    public function SortByRejection()
    {
        $this->SortBy('rejection');
        return $this;
    }
}

?>