<?php

require_once 'post_request.php';

/// A request for questions.
class QuestionRequest extends PostRequest
{
    /// Constructor for a question request.
    /**
      * \param $url the domain name of the site or a URL object
      * \param $method the method being called
      */
    function __construct($url, $method=null)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url, 'question', 'questions', $method);
    }
    
    /// Retrieves the answers to the current set of questions.
    /**
      * \return a PostRequest object
      */
    public function Answers()
    {
        $url_copy = clone $this->url;
        return new PostRequest($url_copy, 'answer', null, 'answers');
    }
    
    /// Returns questions that are linked to the current set of questions.
    /**
      * \return the current instance
      */
    public function Linked()
    {
        $this->url->SetMethod('linked');
        return $this;
    }
    
    /// Restricts the questions returned to those that have no answers.
    /**
      * \return the current instance
      */
    public function NoAnswers()
    {
        $this->url->SetParameter('no-answers');
        return $this;
    }
    
    /// Returns questions that are related to the current set of questions.
    /**
      * \return the current instance
      */
    public function Related()
    {
        $this->url->SetMethod('related');
        return $this;
    }
    
    /// Retrieves the revisions for the current set of questions.
    /**
      * \return a TimelineRequest object
      */
    public function Timeline()
    {
        $url_copy = clone $this->url;
        return new PagedRequest($url_copy, 'question_timeline', null, 'timeline');
    }
    
    /// Restricts the questions returned to those that have no accepted answers.
    /**
      * \return the current instance
      */
    public function Unaccepted()
    {
        $this->url->SetParameter('unaccepted');
        return $this;
    }
    
    /// Restricts the questions returned to those that are unanswered.
    /**
      * \return the current instance
      */
    public function Unanswered()
    {
        $this->url->SetParameter('unanswered');
        return $this;
    }
    
    /// Returns questions that match the specified search terms.
    /**
      * \param $search_text text to search for in question titles
      * \return the current instance
      */
    public function Search($search_text=null)
    {
        // the /search method does not have the '/questions' prefix
        // so we need to reset the category
        $this->url->SetCategory('search');
        
        if($search_text !== null)
            $this->InTitle($search_text);
        
        return $this;
    }
    
    /// Returns questions that are similar to the current set of questions.
    /**
      * \param $title a title to test for
      * \return the current instance
      */
    public function Similar($title=null)
    {
        // the /similar method does not have the '/questions' prefix
        // so we need to reset the category
        $this->url->SetCategory('similar');
        
        if($title !== null)
            $this->Title($title);
        
        return $this;
    }
    
    /// Causes questions to be sorted according to when they were added to a user's favorites.
    /**
      * \return the current instance
      */
    public function SortByAdded()
    {
        $this->SortBy('added');
        return $this;
    }
    
    /// Causes questions to be sorted according to the 'hot' tab on the site.
    /**
      * \return the current instance
      */
    public function SortByHot()
    {
        $this->SortBy('hot');
        return $this;
    }
    
    /// Causes hot questions for the current month to be returned.
    /**
      * \return the current instance
      */
    public function SortByMonth()
    {
        $this->SortBy('month');
        return $this;
    }
    
    /// Causes hot questions for the current week to be returned.
    /**
      * \return the current instance
      */
    public function SortByWeek()
    {
        $this->SortBy('week');
        return $this;
    }
    
    /// Returns only questions with the specified tags.
    /**
      * \param $tags either a single tag or an array of tags
      * \return the current instance
      */
    public function Tagged($tags)
    {
        if(is_array($tags))
            $this->url->SetQueryStringParameter('tagged', implode(';', $tags));
        else
            $this->url->SetQueryStringParameter('tagged', $tags);
        
        return $this;
    }
    
    /// Returns only questions without the specified tags.
    /**
      * \param $tags either a single tag or an array of tags
      * \return the current instance
      */
    public function NotTagged($tags)
    {
        if(is_array($tags))
            $this->url->SetQueryStringParameter('nottagged', implode(';', $tags));
        else
            $this->url->SetQueryStringParameter('nottagged', $tags);
        
        return $this;
    }
    
    /// Returns only questions that contain the specified text in the title.
    /**
      * \param $title the text to match in the title
      * \return the current instance
      */
    public function InTitle($title)
    {
        $this->url->SetQueryStringParameter('intitle', $title);
        return $this;
    }
    
    /// Returns questions that contain a similar title.
    /**
      * \param $title the title to match
      * \return the current instance
      */
    public function Title($title)
    {
        $this->url->SetQueryStringParameter('title', $title);
        return $this;
    }
}

?>