<?php

/// Represents an exception that occurs inside Stack.PHP code.
/**
  * This class exists to extend the information that is available when
  * an exception occurs. Additional information, such as URL and API error
  * code / message are able to be captured and stored with the exception.
  */
class APIException extends Exception
{
    // Additional details regarding the exception such as the
    // error code and URL.
    private $details;
    private $url;
    
    /// Constructor for an API exception.
    /**
      * \param $message a brief message describing the exception
      * \param $url the URL that raised the exception
      * \param $details more detailed information about the exception
      * \param $error_code an error code associated with the exception
      */
    function __construct($message, $url=null, $details='', $error_code=0)
    {
        $this->message = $message;
        $this->url     = $url;
        $this->details = $details;
        $this->code    = $error_code;
    }
    
    /// Returns a string representation of the exception.
    /**
      * \return a string containing exception information
      */
    public function __toString()
    {
        if($this->code)
            return "Error {$this->code}: {$this->message}";
        else
            return "Error: {$this->message}";
    }
    
    /// Returns a brief description of the error.
    /**
      * \return the error description
      */
    public function Message()
    {
        return $this->message;
    }
    
    /// Returns the URL that generated the exception.
    /**
      * \return the URL object
      */
    public function URL()
    {
        return $this->url;
    }
    
    /// Returns an error code associated with the exception.
    /**
      * \return the error code
      */
    public function ErrorCode()
    {
        return $this->code;
    }
    
    /// Returns additional details about the exception.
    /**
      * \return details about the exception
      */
    public function Details()
    {
        return $this->details;
    }
}

?>