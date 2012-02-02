<?php

//=================================
//   Performs an exhaustive test
// of all site-specific API routes
//=================================

require_once 'test.php';
require_once '../src/api.php';

class APITest extends Test
{
    function __construct()
    {
        $this->name        = 'API Routes';
        $this->description = 'Performs an exhaustive test of all site-specific routes, ensuring that returned data matches the specified criterion.';
    }
    
    private function AnalyzeResponse($test_name, $response_object, $tests, $min=0, $max=0)
    {
        // Display the name of the test
        echo "* $test_name\n";
        
        // Check the total number of results returned
        $total = $response_object->Total();
        
        if($total < $min)
            throw new Exception("Fewer than $min items were returned in the response body.");
        
        if($max && $total > $max)
            throw new Exception("More than $max items were returned in the response body.");
        
        // Now perform the tests on every item returned
        while($item = $response_object->Fetch(FALSE))
        {
            foreach($tests as $index => $test)
            {
                // Make sure that index exists in the item
                if(!isset($item[$index]))
                    throw new Exception("The index '$index' does not exist in the returned item.");
                
                $result = eval("return (\$item[\$index] $test);");
                
                if($result === FALSE || $result === null)
                    throw new Exception("The value of '$index' does not meet the requirements '$test'.");
            }
        }
    }
    
    protected function PerformTest()
    {
        $au = API::Site('askubuntu');

        $this->AnalyzeResponse('/answers',
                               $au->Answers()->Exec(),
                               array());

        // At the current time, one of the posts has a score
        // of 8. It's pretty safe to assume that that won't
        // drop below 6 as we test for here.
        $this->AnalyzeResponse('/answers/{IDs}',
                               $au->Answers(array(2, 10))->Exec(),
                               array('score' => '>6'),
                               2, 2);

        $this->AnalyzeResponse('/answers/{IDs}/comments',
                               $au->Answers(array(2, 10))->Comments()->Exec(),
                               array(),
                               6);

        // At the time of this writing, there were 85 badges -
        // this number will not likely decrease.
        $this->AnalyzeResponse('/badges',
                               $au->Badges()->Exec(),
                               array(),
                               85);

        // The Teacher and Student badge seem to consistently
        // have badges with ID #1 and #2.
        $this->AnalyzeResponse('/badges/{IDs}',
                               $au->Badges(array(1,2))->Exec(),
                               array(),
                               2);

        // We are assuming hard numbers for the next two queries
        $this->AnalyzeResponse('/badges/name',
                               $au->Badges()->Name()->Exec(),
                               array('badge_type' => '=="named"'),
                               71);

        $this->AnalyzeResponse('/badges/recipients',
                               $au->Badges()->Recipients()->Exec(),
                               array(),
                               50000);

        $this->AnalyzeResponse('/badges/{IDs}/recipients',
                               $au->Badges(1)->Recipients()->Exec(),
                               array('badge_id' => '==1'),
                               5000);

        $this->AnalyzeResponse('/badges/tags',
                               $au->Badges()->Tags()->Exec(),
                               array('badge_type' => '=="tag_based"'),
                               14);

        $this->AnalyzeResponse('/comments',
                               $au->Comments()->Exec(),
                               array(),
                               30);

        // Use the specified comment IDs which have a score
        // of at least 8.
        $this->AnalyzeResponse('/comments/{IDs}',
                               $au->Comments(array(54321, 9498, 32219))->Exec(),
                               array('score' => '>=8'),
                               3, 3);

        $this->AnalyzeResponse('/errors',
                               $au->Errors()->Exec(),
                               array('error_id' => '>=400'),
                               9);

        // /events would go here but it is an authenticated method

        $this->AnalyzeResponse('/posts',
                               $au->Posts()->SortByCreation()->Ascending()->Exec(),
                               array('creation_date' => '>=1231400875'),
                               70000);

        $this->AnalyzeResponse('/posts/{IDs}',
                               $au->Posts(array(1,2))->Exec(),
                               array('creation_date' => '<=1280344530'),
                               2,2);

        $this->AnalyzeResponse('/posts/{IDs}/comments',
                               $au->Posts(array(2,3))->Comments()->Exec(),
                               array(),
                               6);

        $this->AnalyzeResponse('/posts/{IDs}/revisions',
                               $au->Posts(5)->Revisions()->Exec(),
                               array('post_id' => '==5'),
                               4);

        $this->AnalyzeResponse('/posts/{IDs}/suggested-edits',
                               $au->Posts(1)->SuggestedEdits()->Exec(),
                               array());

        $this->AnalyzeResponse('/privileges',
                               $au->Privileges()->Exec(),
                               array(),
                               24);

        $this->AnalyzeResponse('/questions',
                               $au->Questions()->SortByVotes()->Descending()->Min(40)->Exec(),
                               array('score' => '>=40'),
                               24);

        // Send two question IDs and make sure
        // we get those two back.
        $this->AnalyzeResponse('/questions/{IDs}',
                               $au->Questions(array(30334, 28086))->Exec(),
                               array('score' => '>=40'),
                               2, 2);

        $this->AnalyzeResponse('/questions/{IDs}/answers',
                               $au->Questions(array(30334))->Answers()->SortByVotes()->Min(16)->Exec(),
                               array('score' => '>=16'),
                               9);

        // Fetch the comments for the question
        $this->AnalyzeResponse('/questions/{IDs}/comments',
                               $au->Questions(array(6586))->Comments()->Exec(),
                               array('post_id' => '==6586'),
                               4);

        $this->AnalyzeResponse('/questions/{IDs}/linked',
                               $au->Questions(array(30334))->Linked()->Exec(),
                               array(),
                               22);

        $this->AnalyzeResponse('/questions/{IDs}/related',
                               $au->Questions(array(30334))->Related()->Exec(),
                               array(),
                               25);

        $this->AnalyzeResponse('/questions/{IDs}/timeline',
                               $au->Questions(array(30334))->Timeline()->Exec(),
                               array('question_id' => '==30334'),
                               573);

        // There are more than 2000, but for now just use that
        // as the lower floor
        $this->AnalyzeResponse('/questions/no-answers',
                               $au->Questions()->NoAnswers()->Exec(),
                               array('answer_count' => '==0'),
                               2000);

        $this->AnalyzeResponse('/questions/unanswered',
                               $au->Questions()->Unanswered()->Exec(),
                               array(),
                               4000);

        $this->AnalyzeResponse('/revisions/{IDs}',
                               $au->Revisions(array('669b80703cb44546919dfb94a988c809'))->Exec(),
                               array('post_id' => '==2'),
                               1, 1);

        // Now for the search routes
        $this->AnalyzeResponse('/search',
                               $au->Questions()->Search('install firefox')->Exec(),
                               array(),
                               10);

        $this->AnalyzeResponse('/similar',
                               $au->Questions()->Similar('i cannot boot ubuntu after installing windows')->Exec(),
                               array(),
                               24);

        // Assume that all tags have a count >0
        $this->AnalyzeResponse('/tags',
                               $au->Tags()->InName('window')->Exec(),
                               array('count' => '>0'),
                               10);
        
        $this->AnalyzeResponse('/tags/synonyms',
                               $au->Tags()->Synonyms()->Exec(),
                               array(),
                               70);
        
        $this->AnalyzeResponse('/tags/{TAGS}/related',
                               $au->Tags('compiz')->Related()->Exec(),
                               array(),
                               30);

        $this->AnalyzeResponse('/tags/{TAGS}/synonyms',
                               $au->Tags('ubuntu-desktop')->Synonyms()->Exec(),
                               array('to_tag' => "=='ubuntu-desktop'"),
                               1);

        $this->AnalyzeResponse('/tags/{TAG}/top-askers/{PERIOD}',
                               $au->Tags('compiz')->TopAskers(Period::Month)->Exec(),
                               array('score' => '>=0'));

        $this->AnalyzeResponse('/tags/{TAG}/top-answerers/{PERIOD}',
                               $au->Tags('compiz')->TopAnswerers(Period::Month)->Exec(),
                               array('score' => '>=0'));

        $this->AnalyzeResponse('/tags/{TAGS}/wikis',
                               $au->Tags('compiz')->Wikis()->Exec(),
                               array('tag_name' => "=='compiz'"),
                               1, 1);

        // User methods
        $this->AnalyzeResponse('/users',
                               $au->Users()->SortByReputation()->InName('castro')->Exec(),
                               array(),
                               1);

        $this->AnalyzeResponse('/users/{IDs}',
                               $au->Users(235)->Exec(),
                               array('user_id'    => '==235',
                                     'reputation' => '>15000'),
                               1, 1);

        $this->AnalyzeResponse('/users/{IDs}/answers',
                               $au->Users(235)->Answers()->Exec(),
                               array(),
                               443);

        $this->AnalyzeResponse('/users/{IDs}/badges',
                               $au->Users(235)->Badges()->Exec(),
                               array('award_count' => '>0'),
                               66);

        $this->AnalyzeResponse('/users/{IDs}/comments',
                               $au->Users(235)->Comments()->SortByVotes()->Min(5)->Exec(),
                               array('score' => '>=5'),
                               13);

        $this->AnalyzeResponse('/users/{IDs}/comments/{ID}',
                               $au->Users(235)->CommentsTo(41)->Exec(),
                               array(),
                               1);

        // We cannot assume any number of favorites
        // here since a user can modify them at will.
        // We also turn on our filter.
        $this->AnalyzeResponse('/users/{IDs}/favorites',
                               $au->Users(235)->Favorites()->Filter('!Dm8Xl')->Exec(),
                               array('favorite_count' => '>=1'));

        $this->AnalyzeResponse('/users/{IDs}/mentioned',
                               $au->Users(235)->Mentioned()->Exec(),
                               array(),
                               247);

        $this->AnalyzeResponse('/users/{ID}/privileges',
                               $au->Users(235)->Privileges()->Exec(),
                               array(),
                               23);

        $this->AnalyzeResponse('/users/{IDs}/questions',
                               $au->Users(235)->Questions()->SortByVotes()->Min(10)->Exec(),
                               array('score' => '>=10'),
                               27);

        // We can't assume a minimum here because anyone could answer the question
        // at any given time.
        $this->AnalyzeResponse('/users/{IDs}/questions/no-answers',
                               $au->Users(235)->Questions()->NoAnswers()->Exec(),
                               array('answer_count' => '==0'));

        $this->AnalyzeResponse('/users/{IDs}/questions/unaccepted',
                               $au->Users(235)->Questions()->Unaccepted()->Exec(),
                               array('answer_count' => '>0'));

        $this->AnalyzeResponse('/users/{IDs}/questions/unanswered',
                               $au->Users(235)->Questions()->Unanswered()->Exec(),
                               array());

        $this->AnalyzeResponse('/users/{IDs}/reputation',
                               $au->Users(235)->Reputation()->Exec(),
                               array('user_id' => '==235'));

        // This user should never have any suggested edits
        $this->AnalyzeResponse('/users/{IDs}/suggested-edits',
                               $au->Users(235)->SuggestedEdits()->Exec(),
                               array(),
                               0, 0);

        $this->AnalyzeResponse('/users/{IDs}/tags',
                               $au->Users(235)->Tags()->Exec(),
                               array('user_id' => '==235'),
                               390);

        // There will never be more than 30, and right now there are
        // at least one of each.
        $this->AnalyzeResponse('/users/{ID}/tags/{TAGS}/top-answers',
                               $au->Users(235)->Tags('compiz')->TopAnswers()->Exec(),
                               array(),
                               1, 30);

        $this->AnalyzeResponse('/users/{ID}/tags/{TAGS}/top-questions',
                               $au->Users(235)->Tags('compiz')->TopQuestions()->Exec(),
                               array(),
                               1, 30);

        $this->AnalyzeResponse('/users/{IDs}/timeline',
                               $au->Users(235)->Timeline()->Exec(),
                               array('user_id' => '==235'),
                               7907);

        $this->AnalyzeResponse('/users/{ID}/top-answer-tags',
                               $au->Users(235)->TopAnswerTags()->Exec(),
                               array(),
                               1, 30);

        $this->AnalyzeResponse('/users/{ID}/top-question-tags',
                               $au->Users(235)->TopQuestionTags()->Exec(),
                               array(),
                               1, 30);

        $this->AnalyzeResponse('/users/moderators',
                               $au->Users()->Moderators()->Exec(),
                               array('user_type' => "=='moderator'"),
                               21);

        $this->AnalyzeResponse('/users/moderators/elected',
                               $au->Users()->Moderators()->Elected()->Exec(),
                               array('user_type'   => "=='moderator'",
                                     'is_employee' => '==FALSE'),
                               3, 3);

        // We skip the /inbox method - it requires authentication

        // The /error route
        try
        {
            $au->Errors(ErrorCode::Offline);
        }
        catch(APIException $e)
        {
            if($e->ErrorCode() != ErrorCode::Offline)
                throw new Exception('The error returned did match the error requested.');
        }
    }
}

?>