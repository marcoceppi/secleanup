<?php

// Simple example that demonstrates the retrieval of a user's
// profile, including their information.

require_once 'config.php';
require_once '../../src/output_helper.php';

// Generate the site combobox
$combo = OutputHelper::CreateCombobox(API::Sites(), 'site');
$site_html = $combo->FetchMultiple()->SetIndices('name', 'api_site_parameter')->SetCurrentSelection()->GetHTML();

?>
<!DOCTYPE html>
<html>
<head>
  <title>Stack.PHP - User Information</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
  <?php echo OutputHelper::GetHelperCSS(); ?>
  <?php echo OutputHelper::GetHelperJS(); ?>
</head>
<body>
  <form id='form'>
    <div id='site_selector'>
      <b>Select a Stack Exchange site:</b>
      <?php echo $site_html; ?>
      <input type="submit" value="Go" />
    </div>
    <?php
    
    if(isset($_GET['site']))
    {
        ?>
        <div>
            <b>User:</b>
            <?php echo OutputHelper::DisplayUserSelector('user_id', $_GET['site'], isset($_GET['user_id'])?$_GET['user_id']:''); ?>
            | <input type="submit" value="Go" />
        </div><br />
        <?php
        
        if(isset($_GET['user_id']) && $_GET['user_id'] != '')
        {
            // Retrieve the user's account
            $user = API::Site($_GET['site'])->Users($_GET['user_id']);
            $user_data = $user->Exec()->Fetch();
            
            if($user_data === FALSE)
                echo '<pre>Error: the supplied user_id parameter is invalid.</pre>';
            else
            {
                ?>
                <hr /><br />
                <div class='user-profile'>
                  <div class='gravatar'>
                    <img src='<?php echo $user_data['profile_image']; ?>&s=64' />
                  </div>
                  <b>Username:</b> <?php echo $user_data['display_name']; ?><br />
                  <b>Reputation:</b> <kbd><?php echo $user_data['reputation']; ?></kbd><br />
                </div>
                <?php
                
                // Get the user's answers - but we want the question's titles
                // so we need a custom filter
                $filter = new Filter();
                $filter->SetIncludeItems(array('answer.title', 'answer.link'));
                
                // Check to see if the user has answered any questions
                $users_answers = $user->Answers()->SortByVotes()->Filter($filter->GetID())->Exec()->Pagesize(5);
                $total_answers = $users_answers->Total(FALSE);
                
                if($total_answers)
                {
                    echo "<br /><h2>Top {$total_answers} Answer(s)</h2>";
                    echo '<ul>';
                    while($answer = $users_answers->Fetch(FALSE))
                        echo "<li><a href='{$answer['link']}'>{$answer['title']}</a></li>";
                    echo '</ul>';
                }
                else
                    echo '<br /><p>This user has not answered any questions.</p>';
            }
        }
    }
    
    ?>
  </form>
</body>
</html>