<?php
require_once 'YandexDictionaryApi.php';
class RestrAdapter{
    private $requestMethod;
    private $yandexDictionaryApi;
    
    
    
    public function __construct(){
        echo "start restadapter init";
//        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
//        if (strnatcasecmp ( $this->requestMethod , "GET")!=0 || !isset($_GET)){
//            throw new Exception("invalid request method. dump=".var_dump($this->requestMethod));
//        }


        $this->initYandexApi();
       
     }
     
     private function initYandexApi(){
         echo "<br>init yandex api<br>";
         $this->yandexDictionaryApi=new YandexDictionaryApi();
     }
     
     public function getTranslation(){
         echo "<br>getTranslation fump=".var_dump($this->yandexDictionaryApi);
         $this->yandexDictionaryApi->getTranslation();
     }
    
    
}