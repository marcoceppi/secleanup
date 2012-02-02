<?php

//====================================
//  Tests the filter class to ensure
//      proper functionality.
//====================================

require_once 'test.php';
require_once '../src/api.php';
require_once '../src/filter.php';

class FilterTest extends Test
{
    function __construct()
    {
        $this->name        = 'Filter';
        $this->description = 'Tests the functionality of filters to ensure that responses contain the specified fields and that HTML data is properly escaped according to the filter\'s settings.';
    }
    
    protected function PerformTest()
    {
        // Create the filter
        $filter = new Filter();
        
        // We want to include something that wouldn't be included by default
        // for answers - link and exclude something that's included by default - score
        $filter->SetIncludeItems('answer.link')->SetExcludeItems('answer.score');
        
        // Get the filter's ID
        $filter_id = $filter->GetID();
        
        // Now make a request for an answer
        $answer = API::Site('stackoverflow')->Answers()->Filter($filter_id)->Exec()->Fetch();
        
        // Make sure the answer contains / excludes what we specified
        if(!isset($answer['link']))
            throw new Exception('"link" missing from response.');
        if(isset($answer['score']))
            throw new Exception('"score" included in response but should not be present.');
        
        // Now lookup the filter by ID
        $filter = new Filter($filter_id);
        $included_items = $filter->GetIncludeItems();
        
        // Make sure that the included items match what we describe
        if(!in_array('answer.link', $included_items))
            throw new Exception('"link" missing from filter description.');
        if(in_array('answer.score', $included_items))
            throw new Exception('"score" included in filter description but should not be present.');
    }
}

?>