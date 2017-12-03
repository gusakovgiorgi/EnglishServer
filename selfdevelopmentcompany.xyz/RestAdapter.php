<?php
require_once 'YandexDictionaryApi.php';
class RestrAdapter{
    private $requestMethod;
    private $yandexDictionaryApi;
    
    
    
    public function __construct(){
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        if (strnatcasecmp ( $this->requestMethod , "GET")!=0 || !isset($_GET)){
            throw new Exception("invalid request method. dump=".var_dump($this->requestMethod));
        }
        
        $this->initYandexApi();
       
     }
     
     private function initYandexApi(){
         $this->yandexDictionaryApi=new YandexDictionaryApi();
     }
     
     public function getTranslation(){
         $this->yandexDictionaryApi->getTranslation();
     }
    
    
}