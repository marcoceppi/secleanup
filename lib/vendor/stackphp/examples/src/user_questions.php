<?php

// Simple example that fetches a list of questions
// asked by a user - pagination is handled by Stack.PHP.

// The config file (used for this example) includes api.php
// from the Stack.PHP source, prepares the cache, and sets
// up the API key(s).
require_once 'config.php';

// Begin by getting a Site object for Stack Overflow.
// We can use 'stackoverflow' or 'stackoverflow.com' here.
$site = API::Site('stackoverflow');

// Now we want to limit the scope of the request to a particular
// user - we do this by invoking Users() on the Site object and
// passing the user's ID as a parameter. Then we turn around and
// fetch the questions for that user
$request = $site->Users(1)->Questions();

// Once we have specified what we want to retrieve, we call the
// Exec method of the request object, which gives us a response
// object that we can use to page through the results.
$response = $request->Exec();

// Now we can simply call Get on the response object (passing the
// parameter TRUE to indicate we want ALL pages fetched). We
// do this in a loop as demonstrated below, storing each question's
// title in an array to display further down the page.
$questions = array();

while($item = $response->Fetch(TRUE))
    $questions[] = $item['title'];

// Note: we could have just enumerated over the questions below when
// we output them BUT it is always better to perform external requests
// BEFORE outputting anything to the client in case an error occurs.
// That way, we can display an error message or redirect to another page.
// (We'll look at error handling in a later example.)

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Stack.PHP - User's Questions</title>
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
</head>
<body>
  <h2>Questions Asked By User #1 on Stack Overflow</h2>
  <ul>
  <?php
  
  // Display our list of question titles
  foreach($questions as $question)
      echo "<li>$question</li>";
  
  ?>
  </ul>
</body>
</html>