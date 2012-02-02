<?php

require_once 'format.php';
require_once 'output_element.php';
require_once 'table_column.php';

/// Helper class for outputting HTML tables from Response objects.
/**
  * This class displays an HTML table containing the data retrieved. You
  * specify the desired columns by providing an index into the returned
  * items and specifying display options for the column.
  */
class TableOutput extends OutputElement
{
    // Stores information about the columns
    private $columns = array();
    
    // The images to use when displaying a sorting method
    private $sort_asc_image  = null;
    private $sort_desc_image = null;
    
    /// Adds a column to the table.
    /**
      * \param $column a TableColumn instance
      * \return the current instance
      */
    public function AddColumn($column)
    {
        $this->columns[] = $column;
        return $this;
    }
    
    /// Sets the images to use for indicating sort methods.
    /**
      * \param $sort_asc_image the image to use for ascending sort
      * \param $sort_desc_image the image to use for descending sort
      * \return the current instance
      */
    public function SetSortImages($sort_asc_image, $sort_desc_image)
    {
        $this->sort_asc_image =  $sort_asc_image;
        $this->sort_desc_image = $sort_desc_image;
        
        return $this;
    }
    
    /// Generates the table and returns the HTML for the table.
    /**
      * \param $current_sort the current sort method (null if none)
      * \param $current_order the current ordering (null if none)
      * \return the HTML for the table
      */
    public function GetHTML($current_sort=null, $current_order=null)
    {
        $table_data = '<table><tr>';
        
        // Loop through the columns, generating the table header
        foreach($this->columns as $column)
        {
            // Determine whether we need to display a sort image
            $sort_html = '';
            
            if($current_sort !== null &&
               $current_sort === $column->GetSort() &&
               $this->sort_asc_image !== null &&
               $this->sort_desc_image !== null)
            {
                if($current_order !== null && $current_order == 'asc')
                    $sort_html = "<img src='{$this->sort_asc_image}' />";
                else
                    $sort_html = "<img src='{$this->sort_desc_image}' />";
            }
            
            $table_data .= $column->GetHeaderHTML($sort_html);
        }
        
        // End the header row
        $table_data .= '</tr>';
        
        // Call Fetch() on the response object, retrieving the items
        while($item = $this->response->Fetch($this->fetch_pages))
        {
            $table_data .= '<tr>';
            
            // For alternating row colors
            if(isset($odd)) { $class = 'odd';  unset($odd); }
            else            { $class = 'even'; $odd = 1;   }
            
            foreach($this->columns as $column)
                $table_data .= $column->GetCellHTML($item, $class);
            
            $table_data .= '</tr>';
        }
        
        $table_data .= '</table>';
        return $table_data;
    }
    
    /// Generates a <kbd>&lt;select&gt;</kbd> element containing the sorting methods available.
    /**
      * \param $id the value to be used for the ID and NAME attribute
      * \param $current_selection the currently selected value (or an empty string if none)
      * \return the HTML for the <kbd>&lt;select&gt;</kbd>
      *
      * Note: the list of available sorting methods is pulled from the data provided
      * to AddColumn.
      */
    public function GetSortHTML($id, $current_selection='')
    {
        $select_data = "<select id='$id' name='$id'>";
        
        foreach($this->columns as $column)
        {
            $sort      = $column->GetSort();
            $sort_name = $column->GetTitle();
            
            if($sort !== null)
            {
                $selected = ($current_selection == $sort)?' selected':'';
                $select_data .= "<option value='{$sort}'$selected>$sort_name</option>";
            }
        }
        
        $select_data .= '</select>';
        return $select_data;
    }
    
    /// Generates a <kbd>&lt;select&gt;</kbd> element containing the available sort orders.
    /**
      * \param $id the value to be used for the ID and NAME attribute
      * \param $current_selection the currently selected value (or an empty string if none)
      * \return the HTML for the <kbd>&lt;select&gt;</kbd>
      *
      * Note: the list of currently accepted sorting orders is ascending and descending.
      */
    public function GetOrderHTML($id, $current_selection='')
    {
        $select_data = "<select id='$id' name='$id'>";
        
        if($current_selection == 'asc')
            $select_data .= '<option value="asc" selected>Ascending</option><option value="desc">Descending</option>';
        else
            $select_data .= '<option value="asc">Ascending</option><option value="desc" selected>Descending</option>';
        
        $select_data .= '</select>';
        return $select_data;
    }
}

?>