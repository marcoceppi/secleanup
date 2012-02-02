<?php

require_once 'format.php';

/// Represents a column in a TableOutput.
/**
  * This class provides means of customizing the display and formatting
  * of a column in a table.
  */
class TableColumn
{
    // Storage for properties of this column
    private $field;
    private $title;
    private $sort;
    private $format;
    
    /// Constructor for a table column.
    /**
      * \param $field the index into the data returned by Response::Fetch()
      * \param $title the title to show in the column header (null to use $field)
      * \param $sort the name of the sort method to use for this column (null for no sort)
      * \param $format one of Format or a function that returns the proper formatting
      */
    function __construct($field='', $title=null, $sort=null, $format=Format::None)
    {
        $this->field  = $field;
        $this->title  = $title;
        $this->sort   = $sort;
        $this->format = $format;
    }
    
    /// Generates the HTML for the column header.
    /**
      * \param $additional_data any additional HTML to append to the header
      * \param $class a CSS class to apply to the header
      * \return the HTML for the column header
      */
    public function GetHeaderHTML($additional_data='', $class=null)
    {
        $class_str = ($class !== null)?" class='$class'":'';
        
        return "<th$class_str>" . $this->GetTitle() . '</th>';
    }
    
    /// Generates the HTML for a table cell in the column.
    /**
      * \param $item the item containing the data to display
      * \param $class a CSS class to apply to the cell
      * \return the HTML for the table cell
      */
    public function GetCellHTML($item, $class=null)
    {
        $class_str = ($class !== null)?" class='$class'":'';
        
        // We perform different actions depending on whether
        // we are formatting the data or invoking a custom method.
        $cell_contents = '';
        
        // First, if it is a method invoke it as a function
        if(is_callable($this->format))
            $cell_contents = call_user_func($this->format, $item);
        // Otherwise, have it properly formatted if it exists
        elseif(isset($item[$this->field]))
            $cell_contents = Format::Apply($item[$this->field],
                                           $this->format);
        
        return "<td$class_str>$cell_contents</td>";
    }
    
    /// Returns the title for the column.
    /**
      * \return the title for the column
      */
    public function GetTitle()
    {
        if($this->title !== null)
            return $this->title;
        else
            return $this->field;
    }
    
    /// Returns the sorting parameter for this column.
    /**
      * \return the sorting parameter
      */
    public function GetSort()
    {
        return $this->sort;
    }
}

?>