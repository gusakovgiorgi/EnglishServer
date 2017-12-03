<?php

/**
 * Created by PhpStorm.
 * User: gusakov giorgi
 * Date: 12/3/2017
 * Time: 6:38 PM
 */
class LogUtil
{
    static function sendErrorToServerLog($sMessage){
        error_log(base64_encode($sMessage),0);
    }
}