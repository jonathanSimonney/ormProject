<?php

namespace Library;


class LogManager
{
    public function writeToLog($sqlRequest, $sqlParams, $otherData){
        $when = new \DateTime();

        if (\is_float($otherData)){
            $file = fopen(__DIR__.'\\logs\\request.log', 'ab');

            $newMessage = $when->format('Y-m-d h:i:s').' '.$sqlRequest.' with params : '.json_encode($sqlParams).' in '.$otherData.' microseconds';
        }else{
            $errorMessage = $otherData->getMessage();

            $newMessage = $when->format('Y-m-d h:i:s').' '.$sqlRequest.' with params : '.json_encode($sqlParams).', generated error : '.$errorMessage;
            $file = fopen(__DIR__.'\\logs\\error.log', 'ab');
        }
        fwrite($file, $newMessage."\n");
        fclose($file);
    }
}