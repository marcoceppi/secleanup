<?php

require_once 'paged_request.php';

/// A request for badges.
class BadgeRequest extends PagedRequest
{
    /// Constructor for a badge request.
    /**
      * \param $url the domain name of the site or a URL object
      * \param $method the method being called
      */
    function __construct($url, $method=null)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url, 'badge', ($method === null)?'badges':null, $method);
    }
    
    /// Returns all name-based badges.
    /**
      * \return the current instance
      */
    public function Name()
    {
        $this->url->SetMethod('name');
        return $this;
    }
    
    /// Returns recently awarded badges.
    /**
      * \return the current instance
      */
    public function Recipients()
    {
        $this->url->SetMethod('recipients');
        return $this;
    }
    
    /// Returns all tag-based badges.
    /**
      * \return the current instance
      */
    public function Tags()
    {
        $this->url->SetMethod('tags');
        return $this;
    }
    
    /// Causes badges to be sorted according to the date they were awarded.
    /**
      * \return the current instance
      */
    public function SortByAwarded()
    {
        $this->SortBy('awarded');
        return $this;
    }
    
    /// Causes badges to be sorted according to their rank.
    /**
      * \return the current instance
      */
    public function SortByRank()
    {
        $this->SortBy('rank');
        return $this;
    }
    
    /// Causes badges to be sorted according to their type.
    /**
      * \return the current instance
      */
    public function SortByType()
    {
        $this->SortBy('type');
        return $this;
    }
}

?>