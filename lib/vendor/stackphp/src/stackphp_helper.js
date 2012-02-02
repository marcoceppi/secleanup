//================================
// Helper functions for Stack.PHP
//================================

// An object containing all Stack.PHP utility methods.
var StackPHP = {
    
    // The API key to use (blank by default)
    APIKey: '',
    ClientID: 0,
    
    // Used for generating unique numbers
    UniqueNumber: 0,
    
    // Generates a number that is unique for the current page
    GenerateUniqueNumber: function() {
        
        return StackPHP.UniqueNumber++;
        
    },
    
    // Creates a DIV with a given ID
    CreateDIV: function(div_id, div_html, div_classes) {
        
        // Create the element
        var element = document.createElement('div');
        element.setAttribute('id', div_id);
        document.getElementsByTagName('body')[0].appendChild(element);
        
        // Assign the provided HTML to the element
        if(typeof div_html != 'undefined')
            element.innerHTML = div_html;
        
        // Assign the provided classes to the div
        if(typeof div_classes != 'undefined')
            element.className = div_classes;
        
    },
    
    // Creates the dialog with the specified text and displays it
    CreateDialog: function(dialog_title, dialog_contents, dialog_class) {
        
        // Create the canvas and dialog
        StackPHP.CreateDIV('stackphp_canvas');
        StackPHP.CreateDIV('stackphp_dialog', '<div><div id="stackphp_dialog_close" onclick="StackPHP.DismissDialog();">X</div><h2>' + dialog_title + '</h2>' + dialog_contents + '</div>', dialog_class);
        
        // Position the dialog in the center of the screen
        var dialog_width = document.getElementById('stackphp_dialog').offsetWidth;
        var dialog_height = document.getElementById('stackphp_dialog').offsetHeight;
        
        var leftpos = document.getElementsByTagName('body')[0].clientWidth / 2 - (dialog_width / 2);
        var toppos  = window.innerHeight / 2 - (dialog_height / 2);
        
        document.getElementById('stackphp_dialog').style.left = leftpos + 'px';
        document.getElementById('stackphp_dialog').style.top  = toppos + 'px';
        
    },
    
    // Formats a numbers such that larger ones are more
    // readable.
    FormatNumber: function(number) {
        
        if(number < 10000)
            return number;
        else if(number < 1000000)
            return (parseInt(number / 100) / 10) + 'k';
        else
            return (parseInt(number / 100000) / 10) + 'm';
        
    },
    
    // Retrieves data from the API
    RunAPIMethod: function(site, method, success_callback, error_callback) {
        
        // This is tricky because we need to use JSONP to retrieve the data. Create the callback
        // function we will use to receive the data that comes in.
        var jsonp_callback = 'jsonp_' + StackPHP.GenerateUniqueNumber();
        
        // Create the callback that we will use
        window[jsonp_callback] = function(data) {
            
            if(typeof data['error_id'] == 'undefined')
                success_callback(data);
            else
                error_callback(data['error_message']);
        };
        
        // Create the script element and set its source
        var script_response = document.createElement('script');
        script_response.src = 'http://api.stackexchange.com/2.0' + method + ((method.indexOf('?') != -1)?'&':'?') + 'key=' + StackPHP.APIKey + '&callback=' + jsonp_callback + '&site=' + site;
        document.getElementsByTagName('head')[0].appendChild(script_response);
        
    },
    
    // Finds a user based on their ID
    FindUser: function(site, element_id) {
        
        StackPHP.CreateDialog('Find User ID', 'Enter part of your username:<br /><br /><input type="button" id="stackphp_find_button" value="Find" /><div id="stackphp_find_searchcontainer"><input type="text" id="stackphp_find_search" /></div><br /><div id="stackphp_find_results"></div>', 'stackphp_find_dialog');
        
        // Focus the input element and set an event handler to activate
        // the search when enter is pushed.
        document.getElementById('stackphp_find_search').focus();
        document.getElementById('stackphp_find_search').onkeydown = function(event) {
        
            if(event.keyCode == 13)
                document.getElementById('stackphp_find_button').onclick();
        
        }
        
        // When the find button is clicked, this is the event handler:
        document.getElementById('stackphp_find_button').onclick = function() {
            
            // Show the loading message
            document.getElementById('stackphp_find_results').innerHTML = '<div id="stackphp_find_loading">Loading</div>';
            
            // Create a timer to run the loading message
            var timer_id;
            var loading_offset = 0;
            var UpdateLoadingMessage = function() {
                
                document.getElementById('stackphp_find_loading').innerHTML = 'Loading';
                
                var num_periods = (loading_offset++) % 5;
                for(var i=0; i<num_periods; ++i)
                    document.getElementById('stackphp_find_loading').innerHTML += '.';
                
                // Run the function again shortly
                timer_id = window.setTimeout(UpdateLoadingMessage, 300);
                
            };
            UpdateLoadingMessage();
                
            // Now retrieve the data
            var searchtext = document.getElementById('stackphp_find_search').value;
            StackPHP.RunAPIMethod(site, '/users?inname=' + encodeURIComponent(searchtext),
                                  function(data) {
                                      
                                      // Stop the timer and clear the results
                                      window.clearTimeout(timer_id);
                                      
                                      if(data['items'].length)
                                      {
                                          document.getElementById('stackphp_find_results').innerHTML = '';
                                      
                                          for(var i=0;i<data['items'].length;++i)
                                              document.getElementById('stackphp_find_results').innerHTML += '<div class="stackphp_find_username" onclick="StackPHP.FindUserSelect(' + data['items'][i]['user_id'] + ',\'' + element_id + '\');">&diam; ' + data['items'][i]['display_name'] + ' <span class="stackphp_find_reputation">[' + StackPHP.FormatNumber(data['items'][i]['reputation']) + ']</span></div>';
                                      }
                                      else
                                          document.getElementById('stackphp_find_results').innerHTML = '<div id="stackphp_dialog_loading">No results</div>';
                                      
                                  },
                                  function(message) {
                                      
                                      // Stop the timer
                                      window.clearTimeout(timer_id);
                                      
                                      // Display the error
                                      document.getElementById('stackphp_find_results').innerHTML = '<div id="stackphp_find_loading">' + message + '</div>';
                                      
                                  });
                
        };
    },
    
    // Closes and dismisses the dialog
    DismissDialog: function() {
    
        document.getElementsByTagName('body')[0].removeChild(document.getElementById('stackphp_dialog'));
        document.getElementsByTagName('body')[0].removeChild(document.getElementById('stackphp_canvas'));
    
    },
    
    // Selects a user with the given ID
    FindUserSelect: function(user_id, element_id) {
        
        // Hide everything
        StackPHP.DismissDialog();
        
        document.getElementById(element_id).value = user_id;
        
    },
    
    // Begins the implicit authentication flow
    BeginImplicitFlow: function(redirect_uri, scope, success_callback, error_callback) {
        
        // Generate the storage location of the callbacks so that we
        // can pass it as the state parameter.
        var callback = 'callback_' + StackPHP.GenerateUniqueNumber();
        
        // Create the new window
        var window_url = 'https://stackexchange.com/oauth/dialog?client_id=' + StackPHP.ClientID + '&scope=' + scope + '&redirect_uri=' + encodeURIComponent(redirect_uri) + '&state=' + callback;
        window.open(window_url, 'auth_window', 'width=640,height=400,menubar=no,toolbar=no,location=no,status=no');
        
        // Store the callbacks under a unique name
        window[callback] = { success: success_callback,
                             error: error_callback };
    },
    
    // Completes the implicit authentication flow (this is called from the
    // context of the opened window)
    CompleteImplicitFlow: function(hash) {
        
        // Trim the '#' and split against '&'
        if(hash.indexOf('#') === 0)
            hash = hash.substr(1);
        
        hash = hash.split('&');
        
        // Convert to an array
        var hash_map = {};
        for(var i=0;i<hash.length;++i)
            if(hash[i] != '' && hash[i].indexOf('=') !== -1)
                hash_map[hash[i].split('=')[0]] = decodeURIComponent(hash[i].split('=')[1]).replace(/\+/g, ' ');
        
        // Retrieve the success and error callbacks
        if(typeof hash_map['state'] == 'undefined' || typeof window[hash_map['state']] == 'undefined')
            alert("An internal error has occurred. Please reload the page and try again.");
        else {
            
            var success_callback = window[hash_map['state']]['success'];
            var error_callback   = window[hash_map['state']]['error'];
            
            if(typeof hash_map['error_description'] != 'undefined')
                error_callback('Authentication error: ' + hash_map['error_description']);
            else if(typeof hash_map['access_token'] == 'undefined')
                error_callback('Access token missing from server response.');
            else
                success_callback(hash_map['access_token']);
        }
    }
    
};

// In case we are participating in an implicit OAuth transaction,
// check to see if we need to complete the process.
if(typeof window.opener != 'undefined' && window.opener !== null && typeof window.opener.StackPHP != 'undefined') {
    
    window.opener.StackPHP.CompleteImplicitFlow(location.hash);
    window.close();
    
}