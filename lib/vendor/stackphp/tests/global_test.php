<?php

//=====================================
// Ensures that the global methods are
//  returning the correct information
//=====================================

// This file performs tests on site.php
require_once '../src/api.php';
require_once '../src/site.php';

class GlobalTest extends Test
{
    function __construct()
    {
        $this->name        = 'Global Methods';
        $this->description = 'Tests all of the global methods (except for those covered by other tests) to ensure that data is returned in the expected format.';
    }
    
    public function PerformTest()
    {
        // Note that API::Sites does NOT return a paged
        // request object, but a derivitave of PagedResponse
        
        // Enumerate all Stack Exchange sites
        $sites = API::Sites();
        
        $count = 0; // count the sites
        while($site = $sites->Fetch())
        {
            if(!isset($site['name']))
                throw new Exception('Site array returned is invalid.');
        
            ++$count;
        }
        
        if($count < 100)
            throw new Exception('Expected more than 100 Stack Exchange sites.');
        
        // the StackAuth /users/{GUID}/associated route
        $response = API::AssociatedUsers(1);
        
        if($response->Total() < 50)
            throw new Exception('Expected more than 50 associated accounts.');
    }
}

?>