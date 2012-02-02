<?php

//===================================
// Forms the basis of all tests that
//          are performed.
//===================================

require_once '../src/api_exception.php';

abstract class Test
{
    protected $name        = '[untitled]';  // the name of the test (shouldn't have 'test' in the name)
    protected $description = '[none]';      // description of the test
    
    private $runtime   = 0;             // the duration of time that the test took to run (in seconds)
    private $completed = FALSE;         // whether or not the test completed
    private $output    = '';            // the output of the test
    private $exception = null;          // any exception that occurred during execution
    
    // This method needs to be overriden by each derived class
    abstract protected function PerformTest();
    
    // Runs the test and records the time it took to perform the test
    public function Run()
    {
        // Record the time that we start the test
        $start = microtime(TRUE);
        
        // Begin output buffering and assume the test failed 
        ob_start();
        $result = FALSE;
        
        // Capture any exceptions that are thrown
        try
        {
            $this->PerformTest();
            $this->completed = TRUE;
        }
        catch(Exception $e)
        {
            $this->exception = $e;
        }
        
        // Capture the output
        $this->output = ob_get_contents();
        ob_end_clean();
        
        // Record the time when the test finished and store the difference
        $finish = microtime(TRUE);
        $this->runtime = sprintf('%.2f', $finish - $start);
    }
    
    // Returns the name of the test
    public function GetName()
    {
        return $this->name;
    }
    
    // Determines if the test passed or not
    public function Passed()
    {
        return $this->completed;
    }
    
    // Returns the HTML for the test results
    public function GetHTML()
    {
        $style  = $this->completed?'passed':'failed';
        $status = $this->completed?'Passed':'Failed';
        
        $escaped_output = ($this->output == '')?'[no output]':htmlentities($this->output);
        $escaped_output = preg_replace('/\'(http:\/\/.+)\'/', '<a href="$1" target="_blank">$1</a>', $escaped_output);
        
        $html = <<<EOD
<div class="test">
  <span class="name">{$this->name}</span>
  <span class="description">{$this->description}</span>
  <span class="duration">Duration: {$this->runtime} seconds</span>
  <span class="status">Status: <span class="$style">$status</span></span>
</div>
<div class="output">
  <span class="label">Output:</span>
  <pre>$escaped_output</pre>
</div>
EOD;
        
        // If there was an exception, then display it
        if($this->exception !== null)
        {
            $exception_msg = htmlentities($this->exception->getMessage());
            
            $html .= <<<EOD
<div class="exception">
    <span class="label">Exceptions:</span>
    <pre>$exception_msg</pre>
</div>
EOD;
        }
        
        return $html;
    }
    
    // Compares the output of the two inputs for discrepancies and raises
    // an exception if they differ.
    protected function CompareOutput($actual, $expected)
    {
        if($actual != $expected)
            throw new Exception("Expected '$expected' but received '$actual'.");
    }
}

?>