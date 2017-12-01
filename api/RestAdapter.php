<?php
require_once 'YandexDictionaryApi.php';
class RestrAdapter{
    private $requestMethod;
    private $authKey;
    private $languageDirection;
    private $text;
    private $yandexDictionaryApi;
    
    
    
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
         $this->yandexDictionaryApi=new YandexDictionaryApi($getArray["key"]);
         echo "debug yandexapi=";
         echo $this->yandexDictionaryApi->getApiKeyString();
     }
    
    
}
?>