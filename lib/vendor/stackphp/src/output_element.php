<?php

/// Represents an HTML element that displays data from a PagedResponse object.
/**
  * This class represents a means of outputting HTML given a response object. The
  * data will be retrieved from the object and displayed using the GetHTML() method,
  * which is implemented by every derived class.
  */
class OutputElement
{
    /// The response object from which data is retrieved.
    protected $response;
    
    /// The id and name attribute for the element.
    protected $id_name;
    
    /// Whether to fetch multiple pages or not.
    protected $fetch_pages = FALSE;
    
    /// Constructor for an OutputElement object.
    /**
      * \param $response an instance of the PagedResponse class
      * \param $id_name the value to use for the id and name attribute of the element
      */
    function __construct($response, $id_name)
    {
        $this->response = $response;
        $this->id_name = $id_name;
    }
    
    /// Determines whether PagedOutput::Fetch should retrieve multiple pages.
    /**
      * \param $fetch_pages whether to fetch multiple pages or not
      * \return the current instance
      */
    public function FetchMultiple($fetch_pages=TRUE)
    {
        $this->fetch_pages = $fetch_pages;
        return $this;
    }
}

?>