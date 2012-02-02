<?php

//============================================
//  Verifies that the URL class is properly
// constructing the URL path for the provided
//               arguments.
//============================================

require_once 'test.php';
require_once '../src/api.php';
require_once '../src/filter.php';
require_once '../src/url.php';

class URLTest extends Test
{
    function __construct()
    {
        $this->name        = 'URL';
        $this->description = 'Verifies that parameters passed to the URL class result in properly constructed URLs.';
    }
    
    protected function PerformTest()
    {
        // We will create a URL for Stack Apps
        $url = new URL('stackapps.com');
        
        // Verify that it matches the provided data
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com');
        
        // Set a query string parameter with a 'bad' value that needs escaping
        $url->SetQueryStringParameter('test', '* *&=??');
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&test=%2A+%2A%26%3D%3F%3F');
        
        // Check to see if the value is replaced by setting a new value
        $url->SetQueryStringParameter('test', 'new');
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&test=new');
        
        // Now make some adjustments to the path settings
        $url->SetCategory('questions');
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0/questions?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&test=new');
        
        $url->AddID(45);
        $url->AddID(46);
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0/questions/45;46?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&test=new');
        
        $url->SetMethod('answers');
        $this->CompareOutput($url->CompleteURL(), 'http://api.stackexchange.com/2.0/questions/45;46/answers?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=stackapps.com&test=new');
    }
}

?>
