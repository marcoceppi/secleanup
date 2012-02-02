<?php

require_once 'paged_request.php';
require_once 'periods.php';
require_once 'question_request.php';
require_once 'tag_synonym_request.php';

/// A request for tags.
class TagRequest extends PagedRequest
{
    /// Constructor for a tag request.
    /**
      * \param $url the domain name of the site or a URL object
      */
    function __construct($url)
    {
        // Pass this information along to the parent constructor
        parent::__construct($url,
                            'tag',
                            null,
                            'tags');
    }
    
    /// Returns tags related to the current set of tags.
    /**
      * \return the current instance
      */
    public function Related()
    {
        $this->url->SetParameter('related');
        return $this;
    }
    
    /// Returns all of the tag synonyms.
    /**
      * \return a TagSynonymRequest object
      */
    public function Synonyms()
    {
        $url_copy = clone $this->url;
        return new TagSynonymRequest($url_copy);
    }
    
    /// Returns the top answerers for a given period.
    /**
      * \param $period one of Period::AllTime or Period::Month
      * \return a PagedRequest object
      */
    public function TopAnswerers($period)
    {
        $url_copy = clone $this->url;
        $url_copy->SetParameter('top-answerers/' . $period);
        return new PagedRequest($url_copy, 'tag_score');
    }
    
    /// Returns the top answers a user has posted with the provided tags.
    /**
      * \return a PagedRequest object
      */
    public function TopAnswers()
    {
        $url_copy = clone $this->url;
        $url_copy->SetParameter('top-answers');
        return new PagedRequest($url_copy, 'answer', null, null);
    }
    
    /// Returns the top askers for a given period.
    /**
      * \param $period one of Period::AllTime or Period::Month
      * \return a PagedRequest object
      */
    public function TopAskers($period)
    {
        $url_copy = clone $this->url;
        $url_copy->SetParameter('top-askers/' . $period);
        return new PagedRequest($url_copy, 'tag_score');
    }
    
    /// Returns the top questions a user has asked with the provided tags.
    /**
      * \return a QuestionRequest object
      */
    public function TopQuestions()
    {
        $url_copy = clone $this->url;
        $url_copy->SetParameter('top-questions');
        
        // This is a bit of a hack - we specify the method ('tags') which
        // will already be specified but is needed to prevent PostRequest
        // from overwriting the category.
        return new QuestionRequest($url_copy, 'tags');
    }
    
    /// Returns tag wikis for the current set of tags.
    /**
      * \return a PagedRequest object
      */
    public function Wikis()
    {
        $url_copy = clone $this->url;
        $url_copy->SetParameter('wikis');
        return new PagedRequest($url_copy, 'tag_wiki', null);
    }
    
    /// Causes tags to be sorted according to their popularity
    /**
      * \return the current instance
      */
    public function SortByPopular()
    {
        $this->SortBy('popular');
        return $this;
    }
}

?>