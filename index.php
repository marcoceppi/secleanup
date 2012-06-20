<?php

/**
 * We meet again Stack.PHP.
 */

require('lib/vendor/stack.php');
include_once('key.inc');

try
{
	API::$key = $key;
	$askubuntu = API::Site('askubuntu');
	$unanswered = $askubuntu->Questions()->Unanswered()->Exec()->Total();
	$questions = $askubuntu->Questions()->Exec()->Total();
}
catch( Exception $e )
{
	echo '<pre>';
	var_dump($e);
	echo '</pre>';
	die("API isn't working!!");
}
// Figure out the % of unanswered then subtract from 100
$curr_accept_rate = 100 - round((($unanswered / $questions) * 100), 2);

// Figure out how many more questions need to be "answered" before we bump to the next %

$next_percentage = (INT)$curr_accept_rate + 1;
$answerd_questions_until_next_percentage = (($questions * (100 - $next_percentage))/100);

$questions_till_we_rule_the_next_percentage = $unanswered - $answerd_questions_until_next_percentage;

$percent_complete_to_next_percent = (INT)(100 - ($questions_till_we_rule_the_next_percentage / ((INT)($questions / 100))) * 100);
/*
echo "Unanswered: " . $unanswered . "<br>";
echo "Total Qs: " . $questions . "<br>";
echo "Cur Rate: " . $curr_accept_rate . "<br>";
echo "Next %: " . $next_percentage . '<br>';
echo "Answered Questions until next percentage: " . $answerd_questions_until_next_percentage . '<br>';
echo "Questions until we rule the world: " . $questions_till_we_rule_the_next_percentage . '<br>';
*/

try
{
	$qs = $askubuntu->Questions()->Unanswered()->Tagged('bug')->Exec();
	while( $item = $qs->Fetch(TRUE) )
	{
		$bugs[] = $item;
	}
}
catch( Excetion $e )
{
	die($e);
}

//var_dump($bugs);


// Look! Ghetto templating
require_once('tpl/index.tpl');
