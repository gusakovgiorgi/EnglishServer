<?php
require_once 'Database.php';
require_once 'LogUtil.php';

class YandexDictionaryApi
{
    const API_URL = "https://dictionary.yandex.net/api/v1/dicservice.json/lookup";
    const INVALID_API_KEY = "INVALID_API_KEY";
    const GET_BEST_API_SQL = "SELECT ak_api FROM  api_key WHERE ak_index = (SELECT cj_api_key_index FROM `current_journal`  
ORDER BY `current_journal`.`cj_request_number` ASC LIMIT 1);";
    const WRITE_TO_HISTORY_JOUTNAL_SQL = "INSERT INTO requests_history_journal (rhj_api_key_index,rhj_request_number,rhj_date)
SELECT cj_api_key_index,cj_request_number, {DATEPLACEHOLDER} FROM current_journal;";
    const TRUNCATE_CURRENT_JOURNAL="TRUNCATE current_journal;";
    const SET_TO_ZERO_CURRENT_JOURNAL_SQL="INSERT IGNORE INTO current_journal (cj_api_key_index,cj_request_number)
SELECT ak_index,0 FROM api_key;";

    private $authKey;
    private $languageDirection;
    private $text;
    private $secondAttempt = false;
    private static $bIsInvalidating=false;


    function __construct()
    {
        //if invalidating than return and invalidate current journal in database
        if (self::$bIsInvalidating) {
            return false;
        }

        //get params and if they are not initialized throw exception
        $gparamsArray = $_POST;
        if (!isset($gparamsArray['lang']) || !isset($gparamsArray['text'])) {
            throw new Exception("lang and text params not initialized");
        }

        $this->languageDirection = $gparamsArray['lang'];
        $this->text = $gparamsArray['text'];
        $yandexApi = $gparamsArray['key'];

        //if we don't get yandex dictionary api (for example first time) we should take the optimal api for current request
        if (isset($yandexApi)) {
            $this->authKey = $yandexApi;
        } else {
            $this->authKey = $this->getNewApiString();
        }
    }

    public static function invalidate()
    {
        self::$bIsInvalidating = true;
        $instance = new self();
        $instance->invalidateAndSaveInJournal();

    }

    private function invalidateAndSaveInJournal()
    {
        $conn = Database::getExistingDatabaseConnection();
        if ($conn->errno) {
            throw new Exception("problem with sql connection. var_damp=" . var_dump($conn));
        }

        //save current journal to history, truncate current journal and set all apis to 0;
        $invalidateCurrentJournalSql=$this->getSaveToJournalHistorySqlWithDate()." ".self::TRUNCATE_CURRENT_JOURNAL." ".self::SET_TO_ZERO_CURRENT_JOURNAL_SQL;
        $result = $conn->multi_query($invalidateCurrentJournalSql);
        if ($conn->affected_rows <= 0) {
            LogUtil::sendErrorToServerLog("cannot write result to db. Dump=" + var_dump($result));
        }

    }

    private function getNewApiString()
    {
        $conn = Database::getExistingDatabaseConnection();
        if ($conn->errno) {
            throw new Exception("problem with sql connection. var_damp=" . var_dump($conn));
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
        //get http responce code
        $returnCodeInfo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

//        echo "resultcode=$returnCodeInfo <br>";

        //200 means everything is ok, we can save request to db and return result, in header we return api_key which devices can save
        //for future requests
        if ($returnCodeInfo == 200) {
            $this->increaseApiKeyRequestNumber($this->authKey);
            header("api_key: $this->authKey");
            echo $result;
            //if code is 403 it means that api key limit has exceed and we should get new api key
        } elseif ($returnCodeInfo == 403) {
            //if second attemp to get api key than return error code
            if (!$this->secondAttempt) {
                $this->secondAttempt = true;
                $this->authKey = $this->getNewApiString();
                $this->getTranslation();
            } else {
                http_response_code(406);
                echo $result;
            }
            LogUtil::sendErrorToServerLog("api key $this->authKey is not valid =$result");
        } else {
            LogUtil::sendErrorToServerLog("unknown error =$result");
            http_response_code(406);
            echo $result;
        }

    }

    private function increaseApiKeyRequestNumber($apiKey)
    {
        $conn = Database::getExistingDatabaseConnection();
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

                if ($conn->affected_rows<1){
                    LogUtil::sendErrorToServerLog("error when insert into db. dump ".var_dump($stmt));
                }

                /* закрываем запрос */
                $stmt->close();
            }
        } else {
            LogUtil::sendErrorToServerLog("get sql connection error. dump=" . var_dump($conn));
        }
    }

    private function getSaveToJournalHistorySqlWithDate()
    {
        //replace placeholder {DATEPLACEHOLDER} in sql string with current time in timezone Europe/Kiev
        $date = new DateTime("now", new DateTimeZone('Europe/Kiev'));
        $returnStr = str_replace("{DATEPLACEHOLDER}", $date->format('Y-m-d'), self::WRITE_TO_HISTORY_JOUTNAL_SQL);
        return $returnStr;
    }

}