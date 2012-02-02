<?php

// Contains configuration parameters for the examples
// You will have to substitute your own values here.

require_once '../../src/api.php';
require_once '../../src/auth.php';
require_once '../../src/filestore_cache.php';

// Replace this data with your own if you want to test
// the authentication examples. They will NOT work
// until the keys below are replaced with your own.

// You will need both a server-side and client-side key
// in order to test all examples. Enter the appropriate
// values below.
if(defined('IMPLICIT'))
{
    API::$key = 'ABqBaKNdubSh1TTyhKC35w((';
    Auth::$client_id = 14;
}
else
{
    API::$key = 'h2Ao77BlzltDV4dovmOKtA((';
    Auth::$client_id = 0;
    Auth::$client_secret = '';
}

// Set the cache we will use
API::SetCache(new FilestoreCache('../cache'));

?>