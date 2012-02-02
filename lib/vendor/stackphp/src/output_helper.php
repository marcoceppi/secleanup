<?php

require_once 'api.php';
require_once 'auth.php';
require_once 'combobox_output.php';
require_once 'table_output.php';

/// Provides some helpful output methods for HTML scripts.
/**
  * These methods provide some tools that simplify outputting HTML content
  * using data retrieved by Stack.PHP. For example, you can convert the data
  * returned by a PagedResponse object to a combobox, a table (with sorting),
  * etc.
  */
class OutputHelper
{
    /// Retrieves the contents of the helper JavaScript file as a string.
    /**
      * \return the string contents of the file
      *
      * This method also provides the script with the client ID and API keys as they
      * were provided to StackPHP.
      *
      * This method should not be used in a production quality application. Instead, you
      * should minify the JS and link to it directly. If so, you will need to provide the
      * script with the API and client ID values yourself.
      */
    public static function GetHelperJS()
    {
        return "<script type='text/javascript'>\n" . file_get_contents(dirname(__FILE__) .
               '/stackphp_helper.js') . "\n\nStackPHP.APIKey = '" . API::$key . "';\nStackPHP.ClientID = " .
               Auth::$client_id . ";\n</script>";
    }
    
    /// Retrieves the contents of the helper CSS file as a string.
    /**
      * \return the string contents of the file
      *
      * This method should not be used in a production quality application. Instead, you
      * should link to the CSS directly.
      */
    public static function GetHelperCSS()
    {
        return "<style type='text/css'>\n" . file_get_contents(dirname(__FILE__) .
               '/stackphp_helper.css') . "\n</style>";
    }
    
    /// Creates a combobox output instance from the provided response.
    /**
      * \param $response an instance of the PagedResponse class
      * \param $id_name the value to use for the id and name attribute of the element
      * \return a ComboboxOutput object
      */
    public static function CreateCombobox($response, $id_name=null)
    {
        return new ComboboxOutput($response, $id_name);
    }
    
    /// Creates a table for displaying data from the provided request.
    /**
      * \param $response an instance of the PagedResponse class
      * \param $id_name the value to use for the id and name attribute of the element
      * \return a TableOutput object
      */
    public static function CreateTable($response, $id_name=null)
    {
        return new TableOutput($response, $id_name);
    }
    
    /// Displays a textbox with tools or information enabling the user to find their user ID.
    /**
      * \param $id the value to use for the ID and NAME attribute
      * \param $site the site in which to search for the user's ID
      * \param $current_selection the currently selected value (or an empty string if none)
      * \return a string containing the HTML for the textbox
      */
    public static function DisplayUserSelector($id, $site, $current_selection='')
    {
        $textbox_value  = "<input type='text' id='$id' name='$id' value='$current_selection' /> ";
        $textbox_value .= "<input type='button' value='Find' onclick='StackPHP.FindUser(\"$site\", \"$id\")' />";
        $textbox_value .= '<noscript><a href="http://meta.stackoverflow.com/q/111130">Instructions for finding your user ID.</a></noscript>';
        return $textbox_value;
    }
}

?>