<?php
/**
 * Created by Pangodream.
 * Date: 27/01/2019
 * Time: 11:38
 */

namespace Alexa;


class Trace
{
    public static function out($text){
        $traceFile ="";
        if(defined('TRACE_FILE') == true){
            $traceFile = TRACE_FILE;
        }
        file_put_contents("AlexaTrace.log", date("Y-m-d H:i:s  ").$text."\n");
    }
}