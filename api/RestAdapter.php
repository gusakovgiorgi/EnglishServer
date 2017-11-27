<?php
class RestrAdapter{
    private $requestMethod;
    private $authKey;
    private $languageDirection;
    private $text;
    
    
    
    public function __construct(){
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        if (strnatcasecmp ( $this->requestMethod , "GET")!=0 || !isset($_GET)){
            throw new Exception();
        }
        
        $this->setParams($_GET);
       
     }
     
     private function setParams($getArray){
         if (!isset($getArray['lang']) || !isset($getArray['text'])) {
             throw new Exception();
         }
         $this->authKey=$getArray['key'];
         $this->languageDirection=$getArray['lang'];
         $this->text=$getArray['text'];
     }
    
    
}
?>