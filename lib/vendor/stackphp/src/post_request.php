<?php

require_once 'paged_request.php';
require_once 'suggested_edit_request.php';

/// A request for posts.
class PostRequest extends PagedRequest
{
    /// Constructor for a post request.
    /**
      * \param $url the domain name of the site or a URL object
      * \param $return_object the type of data expected to be returned
      * \param $category the category of requests being made
      * \param $method the method being called
      */
    function __construct($url, $return_object=null, $category=null, $method=null)
    {
        // Assume some defaults
        if($category === null)
            $category = 'posts';
        
        // Pass this information along to the parent constructor
        parent::__construct($url,
                            ($return_object === null)?'post':$return_object,
                            ($method === null)?$category:null,
                            $method);
    }
    
    /// Retrieves the comments associated with the posts.
    /**
      * \return a PagedRequest object
      */
    public function Comments()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'comment', null, 'comments');
    }
    
    /// Retrieves the revisions for the specified posts.
    /**
      * \return a PagedRequest object
      */
    public function Revisions()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'revision', null, 'revisions');
    }
    
    /// Retrieves the suggested edits pending for the specified posts.
    /**
      * \return a SuggestedEditsRequest object
      */
    public function SuggestedEdits()
    {
        $url_copy = clone $this->url;
        return new SuggestedEditRequest($url_copy, 'suggested-edits');
    }
}

?>