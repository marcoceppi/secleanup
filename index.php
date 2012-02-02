<?php

/**
 * We meet again Stack.PHP.
 */

require('lib/vendor/stack.php');

$askubuntu = API::Site('askubuntu');

$unanswered = $askubuntu->Questions()->Unanswered()->Exec()->Total();
$questions = $askubuntu->Questions()->Exec()->Total();

// Figure out the % of unanswered then subtract from 100
$curr_accept_rate = 100 - round((($unanswered / $questions) * 100), 2);

// Figure out how many more questions need to be "answered" before we bump to the next %

$next_percentage = (INT)$curr_accept_rate + 1;
$answerd_questions_until_next_percentage = (($questions * (100 - $next_percentage))/100);

$questions_till_we_rule_the_next_percentage = $unanswered - $answerd_questions_until_next_percentage;

/*
echo "Unanswered: " . $unanswered . "<br>";
echo "Total Qs: " . $questions . "<br>";
echo "Cur Rate: " . $curr_accept_rate . "<br>";
echo "Next %: " . $next_percentage . '<br>';
echo "Answered Questions until next percentage: " . $answerd_questions_until_next_percentage . '<br>';
echo "Questions until we rule the world: " . $questions_till_we_rule_the_next_percentage . '<br>';
*/
?>
<html>
<head>
<body>
<h1>Hi!</h1>
<h3>Ask Ubuntu is in <strike>CRISIS MODE</strike> - it's embarassing really. We're an awsome community of users which a smudge on our record. Acceptance rate.</h3>
<h3>Right now. This very moment. We have an acceptance rate of <b><?php echo $curr_accept_rate; ?>%</b>.</h3>
<h3>Damn. We have <b><?php echo number_format($unanswered); ?></b> unanswered questions, we need to "answer" <b><?php echo number_format($questions_till_we_rule_the_next_percentage); ?>
 more questions to hit <b><?php echo $next_percentage; ?>%</b></h3>

<h1><a href="http://askubuntu.com/questions/?tab=unanswered" target="_blank">START ANSWERING!</a></h1>
</body>
</html>
