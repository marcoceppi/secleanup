<?php

require_once 'url.php';

/// Represents a filter for data returned in the response.
/**
  * A filter allows you to decide what content is provided in the response
  * that the API returns. You can choose specific data that you would like
  * returned and specific data you would like omitted.
  *
  * By default, Stack.PHP uses a filter that is similar to the default filter
  * with the addition of the remaining <kbd>.wrapper</kbd> properties. Stack.PHP
  * may run into trouble stepping through paged data if you enable a filter
  * that does not provide this data - however, if you do not need paged data,
  * you may see a speed increase by omitting this data.
  *
  * You can change the default filter by setting API::$filter.
  */
class Filter
{
    //====================
    //  Public settings
    //====================
    
    /// The filter used by default when pagination data is not needed.
    public static $default_filter = 'default';
    
    /// The filter used by default when pagination data is required.
    public static $default_pagination_filter = '!DnBO_';
    
    //==========================
    // Private member variables
    //==========================
    
    // The ID for this filter (will be assigned null if not used)
    private $filter_id;
    
    // The data retrieved from the API for this filter (will be assigned null if not used)
    private $filter_data;
    
    // A list of items to include / exclude from the filter
    private $include_items = array();
    private $exclude_items = array();
    
    // Whether or not to return unsafe data in responses (FALSE by default)
    private $unsafe = FALSE;
    
    /// Constructor for a Filter object.
    /**
      * \param $filter_id the ID of an existing filter
      */
    function __construct($filter_id=null)
    {
        $this->SetID($filter_id);
    }
    
    // Retrieves the data associated with the current filter ID
    private function GetFilterData()
    {
        // If we have the data alread, just return it
        if($this->filter_data !== null)
            return $this->filter_data;
        
        // Otherwise look it up
        $url = new URL();
        $url->SetCategory('filters');
        $url->AddID($this->filter_id);
        
        $this->filter_data = API::GetJSON($url);
        return $this->filter_data;
    }
    
    /// Returns an ID for the filter, retrieving one if it does not exist.
    /**
      * \return the ID for the filter
      */
    public function GetID()
    {
        if($this->filter_id === null)
        {
            // Construct the request
            $url = new URL();
            $url->SetCategory('filters')->SetMethod('create');
            $url->SetQueryStringParameter('unsafe', ($this->unsafe)?'true':'false');
            $url->SetPOSTData(array('include' => implode(';', $this->include_items),
                                    'exclude' => implode(';', $this->exclude_items)));
            
            // Issue the request
            $this->filter_data = API::GetJSON($url);
            
            // Store the resulting ID
            $this->filter_id = $this->filter_data['items'][0]['filter'];
        }
        
        return $this->filter_id;
    }
    
    /// Sets the filter ID.
    /**
      * \param $filter_id the ID of the filter
      * \return the current instance
      *
      * Note: if this filter already possesses an ID, its stored data will
      * be discarded since it will not be valid for the current filter.
      */
    public function SetID($filter_id)
    {
        $this->filter_id = $filter_id;
        $this->filter_data = null;
        
        return $this;
    }
    
    /// Retrieves the items that are to be included in the filter.
    /**
      * \return an array of items to be included
      */
    public function GetIncludeItems()
    {
        // If the filter does not have an ID, return our array
        if($this->filter_id === null)
            return $this->include_items;
        else
        {
            $filter_data = $this->GetFilterData();
            return $filter_data['items'][0]['included_fields'];
        }
    }
    
    /// Includes the specified items in the filter.
    /**
      * \param $items either a single item or an array of items
      * \return the current instance
      *
      * Note: if this filter already possesses an ID, it will be discarded because
      * it will no longer be valid after the new items are added.
      */
    public function SetIncludeItems($items)
    {
        if(is_array($items))
            $this->include_items = array_merge($this->include_items, $items);
        else
            $this->include_items[] = $items;
        
        // Destroy the current ID
        $this->filter_id = null;
        
        return $this;
    }
    
    /// Excludes the specified items from the filter.
    /**
      * \param $items either a single item or an array of items
      * \return the current instance
      *
      * Note: if this filter already possesses an ID, it will be discarded because
      * it will no longer be valid after the new items are added.
      */
    public function SetExcludeItems($items)
    {
        if(is_array($items))
            $this->exclude_items = array_merge($this->exclude_items, $items);
        else
            $this->exclude_items[] = $items;
        
        // Destroy the current ID
        $this->filter_id = null;
        
        return $this;
    }
    
    /// Returns whether this filter should return HTML-safe content.
    /**
      * \return TRUE if this filter returns unsafe data
      */
    public function GetUnsafe()
    {
        return $this->unsafe;
    }
    
    /// Indicates whether the responses returned should be HTML-safe.
    /**
      * \param $unsafe TRUE to return unsafe responses
      * \return the current instance
      *
      * Note: if this filter already possesses an ID, it will be discarded because
      * it will no longer be valid after this parameter is set.
      */
    public function SetUnsafe($unsafe=TRUE)
    {
        $this->unsafe = $unsafe;
        
        // Destroy the current ID
        $this->filter_id = null;
        
        return $this;
    }
}

?>