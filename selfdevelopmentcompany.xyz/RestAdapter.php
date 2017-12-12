<?php
require_once 'YandexDictionaryApi.php';
class RestrAdapter{
    private $requestMethod;
    private $yandexDictionaryApi;
    
    
    
    public function __construct(){
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

        if (strnatcasecmp ( $this->requestMethod , "POST")!=0 || !isset($_POST)){
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