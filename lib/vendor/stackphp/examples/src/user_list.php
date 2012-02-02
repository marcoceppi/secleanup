<?php

// Simple example that demonstrates listing users from a
// Stack Exchange site. Makes use of the output helper functions.

require_once 'config.php';
require_once '../../src/output_helper.php';

// Generate the site combobox
$combo = OutputHelper::CreateCombobox(API::Sites(), 'site');
$site_html = $combo->FetchMultiple()->SetIndices('name', 'api_site_parameter')->SetCurrentSelection()->GetHTML();

?>
<!DOCTYPE html>
<html>
<head>
  <title>Stack.PHP - User List</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
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
        $site = API::Site($_GET['site']);
        $request = $site->Users();
        
        if(isset($_GET['sort']))
            $request->SortBy($_GET['sort']);
        
        if(isset($_GET['order']) && $_GET['order'] == 'asc')
            $request->Ascending();
        else
            $request->Descending();
        
        $response = $request->Exec();
        
        $table = OutputHelper::CreateTable($response);
        
        $table->SetSortImages('../common/sort_asc.png',
                              '../common/sort_desc.png');
        
        // Create the method that will display the user's name with a link
        // (In PHP 5.3, we can just embed the function as a parameter to AddColumn)
        function DisplayUsername($item)
        {
            $mod = ($item['user_type'] == 'moderator')?' &diams;':'';
            
            return "<a href='{$item['link']}'>{$item['display_name']}$mod</a>";
        }
        
        $table->AddColumn(new TableColumn('display_name',  'Username',    'name',         'DisplayUsername'));
        $table->AddColumn(new TableColumn('reputation',    'Reputation',  'reputation'));
        $table->AddColumn(new TableColumn('location',      'Location'));
        $table->AddColumn(new TableColumn('creation_date', 'Date Joined', 'creation',     Format::RelativeDate));
        
    ?>
    <div>
      <img src="<?php $info = $site->Info()->Filter('!-pya(u(Z')->Exec()->Fetch(); echo $info['site']['icon_url'] ?>"
           style='vertical-align: text-bottom; width: 24px; height: 24px;' />
      <b>Sort order:</b>
      <?php
          
          echo $table->GetSortHTML('sort', isset($_GET['sort'])?$_GET['sort']:'reputation') . ' ';
          echo $table->GetOrderHTML('order', isset($_GET['order'])?$_GET['order']:'');
          
      ?>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <input type='submit' value='Sort' />
    </div>
    <br />
    <?php
        
        echo $table->GetHTML(isset($_GET['sort'])?$_GET['sort']:null,
                             isset($_GET['order'])?$_GET['order']:null);
    }
    
  ?>
  </form>
</body>
</html>