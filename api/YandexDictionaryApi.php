<?php
require_once 'Database.php';
class YandexDictionaryApi{
    const INVALID_API_KEY="INVALID_API_KEY";
    private $apiString;
    private static $getBestApiSql = "SELECT cj_request_number FROM `current_journal`  
ORDER BY `current_journal`.`cj_request_number` ASC LIMIT 1;";
    
    
    function __construct($yandexApi){
        if(isset($yandexApi)){
            $this->apiString=$yandexApi;
        } else {
            $this->apiString=$this->getNewApiString();
        }
    }
    
    
    private function getNewApiString(){
        $conn=Database::getExistingDatabaseConnection();
        $result = $conn->query(self::$getBestApiSql);
        if ($result->num_rows > 0) {
            $this->apiString=$result->fetch_assoc()["cj_request_number"];
        } else {
            $this->apiString=self::INVALID_API_KEY;
        }
    }
    
    public function getApiKeyString(){
        echo "api strign = $this->apiString";
        return $this->apiString;
    }
    
}