<?php

require_once 'badge_request.php';
require_once 'inbox_item_request.php';
require_once 'paged_request.php';
require_once 'question_request.php';
require_once 'suggested_edit_request.php';
require_once 'tag_request.php';

/// A request for users.
class UserRequest extends PagedRequest
{
    /// Constructor for a user request.
    /**
      * \param $domain_name the domain name of the site
      * \param $access_token a valid access token
      */
    function __construct($domain_name, $access_token=null)
    {
        parent::__construct($domain_name, 'user', 'users');
        
        // Set the access token and /me method if provided
        if($access_token !== null)
            $this->url->SetCategory('me')->SetAccessToken($access_token)->DisableCache();
    }
    
    /// Retrieves the answers written by the current set of users.
    /**
      * \return a PostRequest object
      */
    public function Answers()
    {
        $url_copy = clone $this->url;
        return new PostRequest($url_copy, 'answer', null, 'answers');
    }
    
    /// Retrieves the badges earned by the current set of users.
    /**
      * \return a BadgeRequest object
      */
    public function Badges()
    {
        $url_copy = clone $this->url;
        return new BadgeRequest($url_copy, 'badges');
    }
    
    /// Retrieves the comments posted by the current set of users.
    /**
      * \return a PagedRequest object
      */
    public function Comments()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'comment', null, 'comments');
    }
    
    /// Retrieves the comments posted by the current set of users in reply to the specified user.
    /**
      * \param $user_id a user ID
      * \return a PagedRequest object
      */
    public function CommentsTo($user_id)
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'comment', null, "comments/$user_id");
    }
    
    /// Retrieves only the moderators that have been elected.
    /**
      * \return the current instance
      */
    public function Elected()
    {
        $this->url->SetParameter('elected');
        return $this;
    }
    
    /// Retrieves the questions that the current set of users have favorited.
    /**
      * \return a QuestionRequest object
      */
    public function Favorites()
    {
        $url_copy = clone $this->url;
        return new QuestionRequest($url_copy, 'favorites');
    }
    
    /// Retrieves the items in the user's inbox.
    /**
      * \param $access_token a valid access token
      * \return an InboxItemRequest object
      */
    public function Inbox($access_token=null)
    {
        $url_copy = clone $this->url;
        return new InboxItemRequest($url_copy, $access_token);
    }
    
    /// Retrieves all of the comments that mention the current set of users.
    /**
      * \return a PagedRequest object
      */
    public function Mentioned()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'comment', null, 'mentioned');
    }
    
    /// Retrieves only the moderator users.
    /**
      * \return the current instance
      */
    public function Moderators()
    {
        $this->url->SetMethod('moderators');
        return $this;
    }
    
    /// Retrieves the privileges available for a user.
    /**
      * \return a PagedResponse object
      */
    public function Privileges()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'privilege', null, 'privileges');
    }
    
    /// Retrieves the questions asked by the current set of users.
    /**
      * \return a QuestionRequest object
      */
    public function Questions()
    {
        $url_copy = clone $this->url;
        return new QuestionRequest($url_copy, 'questions');
    }
    
    /// Retrieves reputation changes for the current set of users.
    /**
      * \return a PagedRequest object
      */
    public function Reputation()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'reputation', null, 'reputation');
    }
    
    /// Retrieves the edit suggestions made by the current set of users.
    /**
      * \return a SuggestedEditRequest object
      */
    public function SuggestedEdits()
    {
        $url_copy = clone $this->url;
        return new SuggestedEditRequest($url_copy, 'suggested-edits');
    }
    
    /// Retrieves the set of tags the current set of users are active in.
    /**
      * \param $tag either a single tag or an array of tags
      * \return a TagRequest object
      */
    public function Tags($tag=null)
    {
        $url_copy = clone $this->url;
        $request = new TagRequest($url_copy);
        
        if($tag !== null)
            $request->Tag($tag);
        
        return $request;
    }
    
    /// Retrieves the actions that a user has performed.
    /**
      * \return a PagedRequest object
      */
    public function Timeline()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'user_timeline', null, 'timeline');
    }
    
    /// Retrieves the top 30 tags a user has posted answers for.
    /**
      * \return a PagedResponse object
      */
    public function TopAnswerTags()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'top_tag', null, 'top-answer-tags');
    }
    
    /// Retrieves the top 30 tags a user has asked questions for.
    /**
      * \return a PagedResponse object
      */
    public function TopQuestionTags()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'top_tag', null, 'top-question-tags');
    }
    
    /// Causes users to be sorted according to their time of last modification.
    /**
      * \return the current instance
      */
    public function SortByModified()
    {
        $this->SortBy('modified');
        return $this;
    }
    
    /// Causes users to be sorted according to their reputation.
    /**
      * \return the current instance
      */
    public function SortByReputation()
    {
        $this->SortBy('reputation');
        return $this;
    }
}

?>