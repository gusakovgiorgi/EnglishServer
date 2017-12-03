<?php
require_once 'Database.php';
require_once 'LogUtil.php';

/* INSERT INTO requests_journal (
rj_api_index,
      rj_request_number
)
SELECT cj_api_key_index,
       cj_request_number
FROM current_journal
on DUPLICATE KEY UPDATE
rj_request_number=cj_request_number+rj_request_number;
*/

class YandexDictionaryApi
{
    const API_URL = "https://dictionary.yandex.net/api/v1/dicservice.json/lookup";
    const INVALID_API_KEY = "INVALID_API_KEY";
    const GET_BEST_API_SQL = "SELECT ak_api FROM  api_key WHERE ak_index = (SELECT cj_api_key_index FROM `current_journal`  
ORDER BY `current_journal`.`cj_request_number` ASC LIMIT 1);";
    const INVALIDATE_SQL="";

    private $authKey;
    private $languageDirection;
    private $text;
    private $secondAttempt=false;
    private  static $bIsInvalidating;


    function _construct()
    {
        if (self::$bIsInvalidating){
            return $this->invalidateAndSaveInJournal();
        }
        $getArray = $_GET;
        if (!isset($getArray['lang']) || !isset($getArray['text'])) {
            throw new Exception("lang and text params not initialized");
        }

        $this->languageDirection = $getArray['lang'];
        $this->text = $getArray['text'];
        $yandexApi = $getArray['key'];

        if (isset($yandexApi)) {
            $this->authKey = $yandexApi;
        } else {
            $this->authKey = $this->getNewApiString();
        }
    }

    public static function invalidate(){
        self::$bIsInvalidating=true;
        $instance=new self();

    }

    private function invalidateAndSaveInJournal(){
        $conn=Database::getExistingDatabaseConnection();
        if ($conn->errno){
            throw new Exception("problem with sql connection. var_damp=".var_dump($conn));
        }
        $result = $conn->query(self::GET_BEST_API_SQL);
        if ($result->num_rows > 0) {
            $apiString = $result->fetch_assoc()["ak_api"];
            return $apiString;
        } else {
            return self::INVALID_API_KEY;

        }

    }

    private function getNewApiString()
    {
        $conn = Database::getExistingDatabaseConnection();
        if ($conn->errno){
            throw new Exception("problem with sql connection. var_damp=".var_dump($conn));
        }
        $result = $conn->query(self::GET_BEST_API_SQL);
        if ($result->num_rows > 0) {
            $apiString = $result->fetch_assoc()["ak_api"];
            return $apiString;
        } else {
            return self::INVALID_API_KEY;

        }
    }


    public function getApiKeyString()
    {
        return $this->authKey;
    }

    public function getTranslation()
    {
        $url = self::API_URL . "?key={$this->authKey}&lang={$this->languageDirection}&text={$this->text}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 4); // times out after 4s
        $result = curl_exec($ch); // run the whole process
        $returnCodeInfo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        echo "code=$returnCodeInfo <br>";

        if ($returnCodeInfo == 200) {
            $this->increaseApiKeyRequestNumber($this->authKey);
            echo $result;
        }elseif ($returnCodeInfo==403){
            if (!$this->secondAttempt){
                $this->secondAttempt=true;
                $this->authKey=$this->getNewApiString();
                $this->getTranslation();
            }else{
                http_response_code(406);
                echo $result;
            }
            LogUtil::sendErrorToServerLog("api key $this->authKey is not valid =$result");
        }else{
            LogUtil::sendErrorToServerLog("unknown error =$result");
        }

    }

    private function increaseApiKeyRequestNumber($apiKey)
    {
        $conn = Database::getExistingDatabaseConnection();
        echo "conn->erno= $conn->errno";
        if (!$conn->errno) {
            /* создаем подготавливаемый запрос */
            $stmt = $conn->stmt_init();
            if ($stmt->prepare("INSERT INTO current_journal (cj_api_key_index,cj_request_number) VALUES((SELECT ak_index FROM `api_key` WHERE ak_api=?),1) 
ON DUPLICATE KEY UPDATE cj_request_number=cj_request_number+1")
            ) {

                /* привязываем переменные к параметрам */
                $stmt->bind_param("s", $apiKey);

                /* выполняем запрос */
                $stmt->execute();

                /* привязываем результаты к переменным */
                $stmt->bind_result($district);

                /* выбираем данные из результата */
                $stmt->fetch();

//                TODO if affected rows <1 then log this
//                echo "dump insert result: {$district} affected=$conn->affected_rows <br>";


                /* закрываем запрос */
                $stmt->close();
            }
        }else{
            LogUtil::sendErrorToServerLog("get sql connection error. dump=".var_dump($conn));
        }
    }

}