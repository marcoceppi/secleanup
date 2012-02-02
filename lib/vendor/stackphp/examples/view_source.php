<?php

// Provides the ability to view the source of an example.

// Make sure a valid filename was supplied
if(!isset($_GET['file']) ||
   !preg_match('/^\w*\.php$/', $_GET['file']) ||
   !is_file('src/' . $_GET['file']) ||
   !strcasecmp($_GET['file'], 'config.php'))
{
    echo 'Invalid file specified.';
    exit;
}

?>
<html>
<head>
  <title>Stack.PHP - View Source of '<?php echo $_GET['file']; ?>'</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>
  <?php
  
  // Now syntax highlight the file
  // NOTE: this _looks_ like a terrible security concern, but it
  // isn't since we whitelisted accepted characters above - there
  // won't be any sneaky directory traversing or anything.
  highlight_file('src/' . $_GET['file']);
  
  ?>
</body>
</html>