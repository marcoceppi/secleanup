<?php

require_once 'api.php';
require_once 'api_exception.php';
require_once 'badge_request.php';
require_once 'error_codes.php';
require_once 'event_request.php';
require_once 'paged_response.php';
require_once 'post_request.php';
require_once 'question_request.php';
require_once 'tag_request.php';
require_once 'url.php';
require_once 'user_request.php';

/// A Stack Exchange site providing an API.
/**
  * This class is instantiated by providing either an array
  * item containing site data or a string containing part of
  * the site's domain name.
  */
class Site implements ArrayAccess
{
    // Information about the site
    private $api_domain = '';
    
    // Data about the site
    private $site_information = null;
    
    /// Constructor for a Site object.
    /**
      * \param $site_data a string containing part of the domain name or an array item containing site data
      */
    function __construct($site_data)
    {
        if(is_string($site_data))
        {
            $this->api_domain = $site_data;
            
            // Remove any HTTP prefix
            if(substr($this->api_domain, 0, 7) == 'http://')
                $this->api_domain = substr($this->api_domain, 7);
            
            // Make sure the site doesn't begin with 'api.'
            // (this is mostly for compatibility)
            if(substr($this->api_domain, 0, 4) == 'api.')
                $this->api_domain = substr($this->api_domain, 4);
        }
        else if(is_array($site_data))
        {
            // Make sure the right values
            if(!isset($site_data['api_site_parameter']))
                throw new APIException('The array passed to the Site constructor is invalid.');
            
            // Grab the site domain
            $this->api_domain = $site_data['api_site_parameter'];
            
            // Also grab the site information
            $this->site_information = $site_data;
        }
        else
            throw new APIException('Parameter provided to Site constructor must be a string or an array.');
    }
    
    /// Returns a post request object.
    /**
      * \param $id either an answer ID or an array of answer IDs
      * \return a PostRequest object
      *
      * This method is used for retrieving answers from the site. Similar
      * to the Questions() method, you can chain methods to set parameters
      * and call Exec() to issues the request.
      */
    public function Answers($id=null)
    {
        $request = new PostRequest($this->api_domain, 'answer', 'answers');
        
        // Add any IDs supplied
        if($id !== null)
            $request->ID($id);
        
        return $request;
    }
    
    /// Returns a badge request object.
    /**
      * \param $id either a badge ID or an array of badge IDs
      * \return a BadgeRequest object
      */
    public function Badges($id=null)
    {
        $request = new BadgeRequest($this->api_domain);
        
        // Add any IDs supplied
        if($id !== null)
            return $request->ID($id);
        
        return $request;
    }
    
    /// Returns a comment request object.
    /**
      * \param $id either a comment ID or an array of comment IDs
      * \return a PagedRequest object
      */
    public function Comments($id=null)
    {
        $request = new PagedRequest($this->api_domain, 'comment', 'comments');
        
        // Add any IDs supplied
        if($id !== null)
            $request->ID($id);
        
        return $request;
    }
    
    /// Returns all errors that can be thrown on the site.
    /**
      * \param $error a specific error to simulate
      * \return the current instance
      */
    public function Errors($error=null)
    {
        $request = new PagedRequest($this->api_domain, 'error', 'errors');
        
        if($error !== null)
            $request->ID($error);
        
        return $request;
    }
    
    /// Returns events that have happened on the site within the last 15 minutes.
    /**
      * \param $access_token a valid access token
      * \return an EventRequest object
      */
    public function Events($access_token)
    {
        return new EventRequest($this->api_domain, $access_token);
    }
    
    /// Returns information about the site.
    /**
      * \return a PagedRequest object
      */
    public function Info()
    {
        return new PagedRequest($this->api_domain, 'info', 'info');
    }
    
    /// Returns a request for the user that corresponds with the specified access token.
    /**
      * \param $access_token a valid access token
      * \return a UserRequest object
      */
    public function Me($access_token)
    {
        return new UserRequest($this->api_domain, $access_token);
    }
    
    /// Returns a post request object.
    /**
      * \param $id either a post ID or an array of post IDs
      * \return a PostRequest object
      */
    public function Posts($id=null)
    {
        $request = new PostRequest($this->api_domain);
        
        // Add any supplied IDs
        if($id !== null)
            $request->ID($id);
        
        return $request;
    }
    
    /// Returns a paged response object for retrieving privileges.
    /**
      * \return a PagedRequest object
      */
    public function Privileges()
    {
        return new PagedRequest($this->api_domain, 'privilege', 'privileges');
    }
    
    /// Returns a question request object.
    /**
      * \param $id either a question ID or an array of question IDs
      * \return a QuestionRequest object
      *
      * This method is used for retrieving questions from the site. Simply chain
      * methods to the return value of this method and then call Exec() to issue
      * the request and receive a response object.
      *
      * <b>Example:</b> <kbd>$response = $site->Questions()->SortByVotes()->Min(100)->Exec();</kbd>
      */
    public function Questions($id=null)
    {
        $request = new QuestionRequest($this->api_domain);
        
        // Add the IDs if supplied
        if($id !== null)
            $request->ID($id);
        
        return $request;
    }
    
    /// Returns a revision request object.
    /**
      * \param $id either a post ID or an array of post IDs
      * \return a PagedRequest object
      */
    public function Revisions($id)
    {
        $request = new PagedRequest($this->api_domain, 'revision', 'revisions');
        $request->ID($id);
        
        return $request;
    }
    
    /// Returns questions that match the specified search terms.
    /**
      * \param $search_text text to search for in question titles
      * \return a QuestionRequest object
      */
    public function Search($search_text)
    {
        return $this->Questions()->Search($search_text);
    }
    
    /// Returns questions that are similar to the current set of questions.
    /**
      * \param $title a title to test for
      * \return a QuestionRequest object
      */
    public function Similar($title)
    {
        return $this->Questions()->Similar($title);
    }
    
    /// Retrieves all of the pending edit suggestions for the site.
    /**
      * \return a PagedRequest object
      */
    public function SuggestedEdits()
    {
        return new PagedRequest($this->api_domain, 'suggested-edit', 'suggested-edits');
    }
    
    /// Returns a tag request object.
    /**
      * \param $tags either a tag or an array of tags
      * \return a TagRequest object
      */
    public function Tags($tags=null)
    {
        $request = new TagRequest($this->api_domain);
        
        // Add the tags if supplied
        if($tags !== null)
            $request->Tag($tags);
        
        return $request;
    }
    
    /// Returns a user request object.
    /**
      * \param $id either a user ID or an array of user IDs
      * \return a UserRequest object
      *
      * This method is used for retrieving users and data from those users on
      * the site. Other data, such as questions and answers from the user(s) can
      * be retrieved by chaining method calls and then calling Exec() to make the
      * request.
      *
      * <b>Example:</b> <kbd>$response = $site->Users(12)->Questions()->Unanswered()->Exec();</kbd>
      */
    public function Users($id=null)
    {
        $request = new UserRequest($this->api_domain);
        
        // Add the IDs if supplied
        if($id !== null)
            $request->ID($id);
        
        return $request;
    }
    
    /// Returns the data for this site.
    /**
      * \return an associative array of site data
      *
      * Note: if this data has not been retrieved yet, this function will call the Stats() method to retrieve it.
      */
    public function Data()
    {
        // Get the information if we don't have it.
        if($this->site_information == null)
            $this->Stats();
        
        return $this->site_information;
    }
    
    /// Returns the friendly name of the site.
    /**
      * \return the friendly name of the site
      *
      * Note: if this data has not been retrieved yet, this function will call the Stats() method to retrieve it.
      */
    public function Name()
    {
        // Get the information if we don't have it.
        if($this->site_information == null)
            $this->Stats();
        
        return $this->site_information['name'];
    }
    
    /// Returns the URL of the actual site.
    /**
      * \return the URL of the site
      *
      * Note: if this data has not been retrieved yet, this function will call the Stats() method to retrieve it.
      */
    public function URL()
    {
        // Get the information if we don't have it.
        if($this->site_information == null)
            $this->Stats();
        
        return $this->site_information['site_url'];
    }
    
    //=======================
    //  ArrayAccess methods
    //=======================
    
    /// Do not use this function. It exists to satisy the interface ArrayAccess.
    public function offsetSet($offset, $value) { }
    /// Do not use this function. It exists to satisy the interface ArrayAccess.
    public function offsetUnset($key) { }
    
    /// Determines whether the given key exists.
    /**
      * \param $key the key to look up
      * \return TRUE if $key exists
      */
    public function offsetExists($key)
    {
        return isset($this->site_information[$key]);
    }
    
    /// Returns the value of the provided key.
    /**
      * \param $key the key to look up
      * \return the value of $key
      */
    public function offsetGet($key)
    {
        return isset($this->site_information[$key])?
               $this->site_information[$key]:
               null;
    }
}

?>