<?php

// Simple example that demonstrates how to successfully authenticate
// against the API and retrieve a user's inbox items using the explicit
// OAuth flow.

require_once 'config.php';
require_once '../../src/output_helper.php';

// Start the session where we will store the user's access token
session_start();

// Check to see if we have the access token already. If not,
// we check to see if either we need to extract it or begin
// the OAuth transaction.
if(!isset($_SESSION['access_token']))
{
    // Get the URL of this page.
    $this_page = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
    
    // First check to see if error is set
    if(isset($_GET['error']))
    {
        echo 'An error has ocurred: <kbd>' . $_GET['error_description'] . '</kbd><br /><br /><a href="' .
             $this_page . '">Try again</a>';
        exit;
    }
    
    // If 'code' is not set, then begin the OAuth transaction
    if(!isset($_GET['code']))
    {
        Auth::BeginExplicitFlow($this_page);
        exit;
    }
    
    // Otherwise finish the transaction
    $_SESSION['access_token'] = Auth::CompleteExplicitFlow($this_page);
}

// We now have a valid access token in $_SESSION
// We can make authenticated requests now.

?>
<!DOCTYPE html>
<html>
<head>
  <title>Stack.PHP - User Authentication</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
</head>
<body>
  <form id='form'>
    <?php
    
    // Get the inbox items
    $response = API::Inbox($_SESSION['access_token'])->Exec();
    
    // List them
    echo '<h2>Here is the contents of your inbox:</h2><ul>';
    
    while($item = $response->Fetch(FALSE))
    {
        $content = "<a href='{$item['link']}'>{$item['title']}</a>";
        echo '<li>' . (($item['is_unread'])?"<b>$content</b>":$content) . '</li>';
    }
    
    echo '</ul>';
    
    ?>
  </form>
</body>
</html>
