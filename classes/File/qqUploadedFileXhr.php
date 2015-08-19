<?php
namespace IslandFuture\Sfw\File;

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    public $sFileName = 'myfile';
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path)
    {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    
    function getName()
    {
        if(empty($_FILES[$this->sFileName]) || empty($_FILES[$this->sFileName]['name'])) {
            throw new \Exception('File ['.$this->sFileName.'] not upload');
        }
        return $_GET[$this->sFileName];
    }

    function getSize()
    {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new \Exception('Getting content length is not supported.');
        }      
    }   
}
