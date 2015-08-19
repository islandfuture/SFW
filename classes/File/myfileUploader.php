<?php
namespace IslandFuture\Sfw\File;

class myfileUploader
{
    public $sFileName = 'myfile';
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    private $arMimeType = array(
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    );
    
    public function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760, $sFileName='')
    {
        if( $sFileName > '') {
            $this->sFileName = $sFileName;
        }
        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET[$this->sFileName])) {
            $this->file = new \IslandFuture\Sfw\File\qqUploadedFileXhr();
        } elseif (isset($_FILES[$this->sFileName])) {
            $this->file = new \IslandFuture\Sfw\File\qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';

            $sizePost = max(1, $postSize / 1024 / 1024) . 'M';
            $sizeUpload = max(1, $uploadSize / 1024 / 1024) . 'M';

            die("{'error':'increase post_max_size[$sizePost] and upload_max_filesize[$sizeUpload] to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Ошибка на сервере. Директория для загрузки закрыта для записи [$uploadDirectory].");
        }
        
        if (!$this->file){
            return array('error' => 'Не указан файл для загрузки.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'Файл пустой');
        }
        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));
        $min_size = min($postSize, $this->sizeLimit);
        $min_size = min($min_size, $uploadSize);

        if ($size > $this->sizeLimit) {
            return array('error' => 'Файл очень большой. Максимальный размер: '.max(1, $min_size/1024/1024 ).'M');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = strtolower($pathinfo['extension']);

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'Неизвестное расширение файла, поддерживаем одно из '. $these . '.');
        }
        
        $newfilename = md5($uploadDirectory . $filename . '.' . $ext);
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $newfilename . '.' . $ext)) {
                $newfilename .= md5($uploadDirectory . $filename . rand(10, 99) . '.' . $ext);
            }
        }
        
        $sSubDir = substr($newfilename,0,3);
        $uploadDirectory = $uploadDirectory . $sSubDir . DIRECTORY_SEPARATOR;
        if (! file_exists($uploadDirectory)) {
            if (! mkdir($uploadDirectory) ) {
                return array('error'=> 'Не могу сохранить загруженный файл.' .
                    'Загрузка был отменена или сервер отказал в обслуживании');
            }
        }
        if ($this->file->save($uploadDirectory . $newfilename . '.' . $ext)){
            return array('success'=>true,'filename' => $filename, 'ext' => $ext, 'newfilename' => $newfilename, 'dir' => $uploadDirectory, 'size' => $size);
        } else {
            return array('error'=> 'Не могу сохранить загруженный файл.' .
                'Загрузка был отменена или сервер отказал в обслуживании');
        }
        
    }
    
    static public function handleFlashUpload($uploadDirectory, $allowedExtensions, $sizeLimit, $replaceOldFile = FALSE)
    {
        if (!is_writable($uploadDirectory)){
            return array('error' => "Ошибка на сервере. Директория для загрузки закрыта для записи [$uploadDirectory].");
        }
    
        //если получен файл
        if (isset($_FILES)) {
            //проверяем размер и тип файла
            $pathinfo = pathinfo($_FILES['Filedata']['name']);
            $filename = $pathinfo['filename'];
                //$filename = md5(uniqid());
            $ext = strtolower($pathinfo['extension']);
        
            if (!in_array($ext, $allowedExtensions)) {
                return array('error' => 'Неизвестное расширение фотографии '.$ext);
            }
            if ($sizeLimit < $_FILES['Filedata']['size']) {
                return array('error' => 'Файл очень большой. Максимальный размер: '.max(1, $min_size/1024/1024 ).'M');
            }
            //if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
        
                
                //$fileName = $uploadDirectory.$_FILES['Filedata']['name'];
                //если файл с таким именем уже существует...
                
                if(!$replaceOldFile){
                    /// don't overwrite previous files that were uploaded
                    while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                        $filename .= rand(10, 99);
                    }
                }

            if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadDirectory . $filename . '.' . $ext)) {
                return array('success' => true, 'filename' => $filename, 'ext' => $ext, 'dir' => $uploadDirectory, 'size' => $_FILES['Filedata']['size']);
            } else {
                return array('error'=> 'Не могу сохранить загруженный файл.' .
                    'Загрузка был отменена или сервер отказал в обслуживании');
            }
            //}
        } else {
            return array('error' => 'Не указан файл для загрузки.');
        }
    }

}