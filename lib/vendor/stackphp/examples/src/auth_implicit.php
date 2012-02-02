<?php

// Simple example that demonstrates how to successfully authenticate
// against the API and retrieve a user's inbox items using the implicit
// OAuth flow.

define('IMPLICIT', TRUE);

require_once 'config.php';
require_once '../../src/output_helper.php';

// Determine what page we want to be redirected to after
$redirect_page = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?auth_redirect';

// Check if this is that page and if so, abort
if(isset($_GET['auth_redirect']))
{
    echo OutputHelper::GetHelperJS();
    echo 'Please wait...';
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Stack.PHP - Client Side Authentication</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
  <?php echo OutputHelper::GetHelperCSS(); ?>
  <?php echo OutputHelper::GetHelperJS(); ?>
  <script type='text/javascript'>
  
  function DisplayInbox(data) {
      
      // Check for an error
      if(typeof data['error_message'] != 'undefined')
          return Failure(data['error_message']);
      
      // Build a string containing the response
      var item_string = '<ul>';
      
      for(var i=0;i<data['items'].length;++i) {
          
          var display_title = (data['items'][i]['is_unread'])?
                              '<b>' + data['items'][i]['title'] + '</b>':
                              data['items'][i]['title'];
          item_string += '<li><a href="' + data['items'][i]['link'] + 
                         '">' + display_title + '</a></li>';
          
      }
      
      item_string += '</ul>';
      
      document.getElementById('results').innerHTML = '<h2>Inbox data:</h2>' + item_string;
      
  }
  
  function Success(access_token) {
      
      document.getElementById('results').innerHTML = 'Loading inbox...';
      
      // Now fetch the inbox data
      var script_response = document.createElement('script');
      script_response.src = 'https://api.stackexchange.com/2.0/inbox?key=' +
                            StackPHP.APIKey + '&access_token=' + access_token +
                            '&callback=DisplayInbox';
      
      document.getElementsByTagName('head')[0].appendChild(script_response);
      
  }
  
  function Failure(error_message) {
      
      document.getElementById('results').innerHTML = '<h2>Error:</h2><p><kbd>' + error_message +
                                                     '</kbd></p><p>Please refresh the page and try again.</p>';
      
  }
  
  </script>
</head>
<body>
  <div id='results'>
      <input type='button' value='Proceed with authentication' onclick='StackPHP.BeginImplicitFlow("<?php echo $redirect_page; ?>", "read_inbox", Success, Failure)' />
  </div>
</body>
</html>