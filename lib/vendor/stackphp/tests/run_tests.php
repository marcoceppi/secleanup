<?php

//=========================================
//  Enumerates all of the tests available
// and captures the output in an HTML file.
//=========================================

// Send a header indicating text output:
header('Content-type: text/plain');

require_once '../src/api.php';

// A list of all tests to run:
$tests = array('api_test.php'      => 'APITest',
               'cache_test.php'    => 'CacheTest',
               'filter_test.php'   => 'FilterTest',
               'global_test.php'   => 'GlobalTest',
               'question_test.php' => 'QuestionTest',
               'url_test.php'      => 'URLTest',
               'user_test.php'     => 'UserTest');

// Set our API key and enable debug mode
API::$key = 'ABqBaKNdubSh1TTyhKC35w((';
API::EnableDebugMode();

// We want to return text content
header('Content-type: text/html');

// Display the status
echo "Stack.PHP Test Suite\n====================\n\n";
echo 'Preparing to perform ' . count($tests) . " test(s)...\n\n";

// Test enumeration variables
$num_tests_run    = 0;        // the number of tests we ran
$num_tests_passed = 0;        // the number of tests that passed
$test_html      = array();  // stores the HTML for each test

// Grab the starting time
$start_time = microtime(TRUE);

// Enumerate through each of the tests
foreach($tests as $filename => $classname)
{
    // Load the file
    require_once $filename;
    
    // Create an instance of the class
    $test = new $classname();
    
    // Display its name
    echo '* Running "' . $test->GetName() . "\" test...\n";
    
    // Run the test
    $test->Run();
    
    // Capture and store the HTML
    $test_html[] = $test->GetHTML();
    
    // If the test passed, increase our total
    if($test->Passed())
        $num_tests_passed++;
    
    $num_tests_run++;
}

// Grab the ending time
$end_time = microtime(TRUE);

// Calculate the total time
$total_time = sprintf('%.2f', $end_time - $start_time);
echo "\nCompleted $num_tests_run test(s) in $total_time seconds.\n";

// Create the HTML for the results page
echo "\nGenerating HTML page for results...\n";

$test_html_string = implode('', $test_html);
$total_processed = API::GetTotalRequests();
$total_requested = API::GetAPIRequests();

$style  = ($num_tests_passed == $num_tests_run)?'passed':'failed';
$status = ($num_tests_passed == $num_tests_run)?"all tests passed":($num_tests_run - $num_tests_passed) . ' test(s) failed';

$current_date = date('r');

$html = <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>Stack.PHP Test Results</title>
<style type='text/css'>

body {
    font-family: arial, sans, helvetica;
    margin: 4px 24px;
}

pre a {
    color: #00f;
    text-decoration: none;
}

pre a:hover {
    color: #55f;
    text-decoration: underline;
}

.status .passed {
    color: #090;
    font-weight: bold;
}

.status .failed {
    color: #f00;
    font-weight: bold;
}

.test {
    background-color: #eee;
    padding: 8px;
    border-radius: 12px;
}

.test .name {
    font-weight: bold;
    margin-right: 10px;
}

.test .description {
    font-style: italic;
}

.test .duration {
    display: block;
    margin-top: 12px;
}

.test .status {
    display: block;
}

.output, .exception {
    margin: 8px 20px;
}

.output .label, .exception .label {
    display: block;
    margin-top: 16px;
    font-weight: bold;
}

.output pre, .exception pre {
    margin-bottom: 24px;
    max-height: 200px;
    overflow-y: auto;
    word-wrap: break-word;
}

.exception pre {
    color: #900;
}

.footer {
    font-size: 10pt;
}

</style>
</head>
<body>
<h2>Stack.PHP Test Results</h2>
<p>
  <span class='status'><b>Status:</b> <span class='$style'>$status</span></span><br />
  <b>Timing:</b> began <i>$current_date</i> and completed in <i>$total_time seconds</i><br />
</p>
$test_html_string
<br /><hr />
<span class="footer">
  Total requests processed: <b>$total_processed</b> - Total API requests made: <b>$total_requested</b>
</span>
</body>
</html>
EOD;

// Create the output file and write the HTML to it
$file = fopen('results.html', 'w');
fwrite($file, $html);
fclose($file);

echo "Detailed results written to 'results.html'.\n";