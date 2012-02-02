<?php

// Provides a compact representation of an example.
class Example
{
    public $title, $description, $filename, $requires_js;
    
    function __construct($title, $description, $filename, $requires_js=FALSE)
    {
        $this->title = $title;
        $this->description = $description;
        $this->filename = $filename;
        $this->requires_js = $requires_js;
    }
}

// An array of all examples grouped by category
$categories = array(
    'Simple Examples' =>
        array(new Example('Fetch User\'s Questions', 'fetches a list of all questions asked by a user', 'user_questions.php'),
              new Example('Search for User', 'searches for users whose names match the specified string', 'user_search.php'),
              new Example('Error Handling', 'demonstrates the proper way to handle errors in Stack.PHP', 'error_handling.php'),
              new Example('Simple API', 'exposes Stack.PHP\'s data to other clients', 'simple_api.php')),
    'Output Helpers' =>
        array(new Example('Comprehensive Site List', 'displays a list of all Stack Exchange sites', 'site_list.php'),
              new Example('List of Users', 'display a list of users on a particular site', 'user_list.php'),
              new Example('User Info', 'display the information for a specific user', 'user_info.php', TRUE)),
    'Authentication' =>
        array(new Example('Explicit OAuth', 'demonstrates authenticating a user using the explicit OAuth flow', 'auth_explicit.php'),
              new Example('Implicit OAuth', 'demonstrates authenticating a user using the client-side implicit OAuth flow', 'auth_implicit.php', TRUE)));

?>
<html>
<head>
  <title>Stack.PHP - Examples</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel='stylesheet' type='text/css' href='common/style.css' />
</head>
<body>
  <h2>Stack.PHP Examples</h2>
  <p>These links provide examples of how to use Stack.PHP for different tasks. These examples also provide a means for testing the authentication methods and output helper classes.</p>
  <table>
  <?php
  
  foreach($categories as $title => $examples)
  {
      // Display the title
      echo "<tr><th colspan='3'>$title</th></tr>";
      
      // Display each example
      foreach($examples as $example)
      {
          $requires_js = ($example->requires_js)?' (requires JavaScript)':'';
          
          echo "<tr><td><a href='src/{$example->filename}'>{$example->title}</a></td><td><a href='view_source.php?file={$example->filename}'>[view source]</a></td><td>{$example->description}$requires_js</td></tr>";
      }
  }
  
  ?>
  </table>
</body>
</html>