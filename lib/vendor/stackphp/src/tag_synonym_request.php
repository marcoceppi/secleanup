<?php

require_once 'paged_request.php';

/// A request for tag synonyms.
class TagSynonymRequest extends PagedRequest
{
    /// Constructor for a tag synonym request.
    /**
      * \param $url a URL object
      */
    function __construct($url)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url, 'tag_synonym');
        
        $this->url->SetParameter('synonyms');
    }
    
    /// Causes synonyms to be sorted according to the number of times they have been applied.
    /**
      * \return the current instance
      */
    public function SortByApplied()
    {
        $this->SortBy('applied');
        return $this;
    }
}

?>