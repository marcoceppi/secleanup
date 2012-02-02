<?php

//=================================================
//       Tests the user request class to
// ensure that the proper URLs are being generated
//=================================================

require_once 'test.php';
require_once '../src/api.php';
require_once '../src/filter.php';
require_once '../src/user_request.php';

class UserTest extends Test
{
    function __construct()
    {
        $this->name        = 'User';
        $this->description = 'Tests the UserRequest object to ensure that the correct URLs are generated as parameters, methods, and access tokens are modified.';
    }
    
    protected function PerformTest()
    {
        // Create the site object
        $superuser = API::Site('superuser');
        
        // Use the /me route (we have a user request returned)
        $request = $superuser->Me('dummyvalue');
        
        $this->CompareOutput($request->URL(), 'https://api.stackexchange.com/2.0/me?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=superuser&access_token=dummyvalue');
        
        // Get the user's inbox
        $request = $request->Inbox();
        $this->CompareOutput($request->URL(), 'https://api.stackexchange.com/2.0/me/inbox?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=superuser&access_token=dummyvalue');
        
        // Create a user request for user number 1
        $request = new UserRequest('serverfault');
        
        $this->CompareOutput($request->URL(), 'http://api.stackexchange.com/2.0/users?filter=' . urlencode(Filter::$default_filter) . '&key=' . urlencode(API::$key) . '&site=serverfault');
    }
}

?>