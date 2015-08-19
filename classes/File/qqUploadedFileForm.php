<?php
namespace IslandFuture\Sfw\File;

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    public $sFileName = 'myfile';

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path)
    {
        if(empty($_FILES[$this->sFileName]) || empty($_FILES[$this->sFileName]['tmp_name'])) {
            throw new \Exception('File ['.$this->sFileName.'] not upload');
        }

        if(!move_uploaded_file($_FILES[$this->sFileName]['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    
    function getName()
    {
        if(empty($_FILES[$this->sFileName]) || empty($_FILES[$this->sFileName]['name'])) {
            throw new \Exception('File ['.$this->sFileName.'] not upload');
        }
        return $_FILES[$this->sFileName]['name'];
    }
    
    function getSize() {
        if(empty($_FILES[$this->sFileName]) || empty($_FILES[$this->sFileName]['size'])) {
            throw new \Exception('File ['.$this->sFileName.'] not upload');
        }
        return $_FILES[$this->sFileName]['size'];
    }
}
