<?php

//=================================================
//      Tests the question request class to
// ensure that the proper URLs are being generated
//=================================================

require_once 'test.php';
require_once '../src/api.php';
require_once '../src/filter.php';
require_once '../src/question_request.php';

class QuestionTest extends Test
{
    function __construct()
    {
        $this->name        = 'Question';
        $this->description = 'Tests the QuestionRequest object to ensure that the correct URLs are generated as parameters and methods are modified.';
    }
    
    protected function PerformTest()
    {
        // Create a QuestionRequest object (using the full domain name for
        // Stack Apps - might as well test that too)
        $request = new QuestionRequest('stackapps.com');
        
        $this->CompareOutput($request->URL(), 'http://api.stackexchange.com/2.0/questions?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com');

        // Now fetch only questions with no answers
        $request->NoAnswers();
        $this->CompareOutput($request->URL(), 'http://api.stackexchange.com/2.0/questions/no-answers?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com');

        // Reduce this list down to questions that have votes > 8
        $request->SortByVotes()->Min(8);
        $this->CompareOutput($request->URL(), 'http://api.stackexchange.com/2.0/questions/no-answers?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&sort=votes&min=8');

        // Execute the request to obtain a paged response
        $response = $request->Exec();

        // Make sure all questions have no answers and a score >= 18
        while($item = $response->Fetch(FALSE))
            if($item['score'] < 8 || $item['answer_count'])
                throw new Exception("Question #{$item['question_id']} does not match the specified criterion.");
    }
}

?>