<?php

require_once 'output_element.php';

/// Displays the provided data in a combobox.
class ComboboxOutput extends OutputElement
{
    // Indices into the response
    private $name_index = null;
    private $value_index = null;
    
    // The current selection
    private $current_selection = null;
    
    /// Sets the indices in the response to use for displaying the combobox.
    /**
      * \param $name the index to use for the caption
      * \param $value the index to use for the option's value attribute
      * \return the current instance
      */
    public function SetIndices($name, $value=null)
    {
        $this->name_index = $name;
        $this->value_index = $value;
        
        return $this;
    }
    
    /// Sets the current selection.
    /**
      * \param $current_selection the currently selected value
      * \return the current instance
      *
      * If $current_selection is null, then the value is retrieved from $_GET.
      */
    public function SetCurrentSelection($current_selection=null)
    {
        if($current_selection === null && isset($_GET[$this->id_name]))
            $this->current_selection = $_GET[$this->id_name];
        else
            $this->current_selection = $current_selection;
        
        return $this;
    }
    
    /// Returns the HTML markup for this element.
    /**
      * \return the HTML markup
      */
    public function GetHTML()
    {
        $html = '<select' . (($this->id_name !== null)?" id='$this->id_name' name='$this->id_name'":'') . '>';
        while($item = $this->response->Fetch($this->fetch_pages))
        {
            // If the value index was supplied then we include it
            $value    = '';
            $selected = '';
            
            if($this->value_index !== null)
            {
                $value    = ' value="' . htmlspecialchars($item[$this->value_index], ENT_QUOTES) . '"';
                $selected = ($this->current_selection == $item[$this->value_index])?' selected="true"':'';
            }
            
            $html .= "<option{$value}{$selected}>" . (($this->name_index !== null)?$item[$this->name_index]:'[unknown]') . '</option>';
        }
        $html .= '</select>';
        
        return $html;
    }
}