<?php

// Displays a list of all Stack Exchange sites using the
// TableOutput class.

// Note that we also include the OutputHelper class in
// addition to the config file.
require_once 'config.php';
require_once '../../src/output_helper.php';

// Begin by creating the response object that will be used
// by the TableOutput class.
$response = API::Sites();

// Now create the TableOutput from our response object.
$table = OutputHelper::CreateTable($response);

// The next step is to define the columns we want to include
// in our table. Each column is an instance of the TableColumn
// class. The first parameter to the TableColumn constructor is
// the index into the item that will be displayed in that column.
// The second parameter is the human-readable name for the column
// (which will be displayed in the header). The third parameter
// only applies if we want to enable sorting. The fourth parameter
// indicates the format of the data.

// For the first column, we want to display the logo of the site.
// The format we are looking for is Format::Image - BUT there is
// one small problem - the original is 192x192 - far from icon
// size. Thankfully there is also Format::Icon which resizes the
// image to icon size (32x32).
$table->AddColumn(new TableColumn('icon_url', '', null, Format::Icon));

// The second column will combine the site title and its intended
// audience (2 lines). In order to do that, we'll need a custom
// format. We do that by creating a function that returns the data
// in the custom format when provided with the item's data.
function GetTitleAndAudience($item)
{
    // Insert line breaks every 50 characters or so
    $audience = preg_replace('/(.{50,}?) /', '$1<br />', $item['audience']);
    
    return "<b>{$item['name']}</b><div style='font-size: 10pt; margin-bottom: 6px;'>$audience</div>";
}

// Pass this information to the column constructor, referring
// to our custom format function by name.
$table->AddColumn(new TableColumn('', 'Site Name', null, 'GetTitleAndAudience'));

// We'll do a similar thing for the third column, where we will generate
// special text for the site state based on the value returned by the API.
function GetSiteState($item)
{
    switch($item['site_state'])
    {
        case 'normal':
            return '<span style="color: green;">Normal</span>';
        case 'closed_beta':
            return '<span style="color: red;">Closed Beta</span>';
        case 'open_beta':
            return '<span style="color: blue;">Open Beta</span>';
        case 'linked_meta':
            return '<span style="color: grey;">Linked Meta</span>';
        default:
            return '<span style="color: lightgrey;">[unknown]</span>';
    }
}

$table->AddColumn(new TableColumn('', 'State', null, 'GetSiteState'));

// The fourth column will indicate when the site launched. The data is
// provided to us as a timestamp, but we can apply the RelativeDate
// format to make it a little bit more human-readable.
$table->AddColumn(new TableColumn('launch_date', 'Launch Date', null, Format::RelativeDate));

// The final column will be a link to the site. There is a format for
// links: Format::Hyperlink.
$table->AddColumn(new TableColumn('site_url', 'Link', null, Format::Hyperlink));

// Generate the HTML for the table (do it now since
// that's when the requests are actually made).
$html = $table->GetHTML();

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Stack.PHP - Site List</title>
  <link rel='stylesheet' type='text/css' href='../common/style.css' />
</head>
<body>
  <h2>Stack Exchange Site List</h2>
  <?php echo $html; ?>
</body>
</html>