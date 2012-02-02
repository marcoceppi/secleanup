<?php

// Demonstrates the proper way to process errors when they
// occur while using Stack.PHP.

require_once 'config.php';

// We begin by creating a Site object for Stack Overflow.
$site = API::Site('stackoverflow');

// Now in order to demonstrate error handling technique, we
// will intentionally generate an error in the API by passing
// an invalid "number" to the /users/{ID} route.
$response = $site->Users('bob')->Exec();

// Nothing up until this point will throw an exception. But
// as soon as we begin fetching objects, the API may throw
// an exception as well as Stack.PHP's internal classes (like
// the cache). Therefore we wrap the following code in a
// try ... catch block.
try
{
    // This will fail!
    $item = $response->Fetch();
}
catch(APIException $e)
{
    // We have lots of interesting information inside
    // $e which we can display below.
    $exception = $e;
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Stack.PHP - Error Handling</title>
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
</head>
<body>
  <h2>Error Handling</h2>
  <p>Note: do not be alarmed when you see an error below. This is a contrived example to demonstrate proper error handling technique.</p>
  <?php
  
  if(isset($exception))
  {
      // This is the URL that generated the error.
      $url = $exception->URL()->CompleteURL();
      
      echo '<p><span style="color: red;">An error has occurred:</span></p>';
      echo '<ul><li><b>Error message:</b> ' . $exception->Message() . '</b></li>';
      echo '<li><b>Error details:</b> ' . $exception->Details() . '</li>';
      echo "<li><b>URL:</b> <a href='$url'>$url</a></li></ul>";
  }
  else
      echo '<p>No error occurred.</p>';  // this really shouldn't happen
  
  
  ?>
</body>
</html>