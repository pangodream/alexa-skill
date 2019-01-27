<?php
/**
 * Created by Pangodream.
 * Date: 26/01/2019
 * Time: 10:10
 */

namespace Alexa;

use Alexa\Intent;
use Alexa\Response;

class Request
{
    /** @var Intent $intent */
    public $intent;
    /** @var string $type */
    public $type;
    /** @var string $timestamp */
    public $timestamp;
    /** @var string $locale */
    public $locale;
    /** @var $dialogState $dialogState */
    public $dialogState;

    public function __construct()
    {
        $this->intent = new Intent();
        $this->response = new Response();
    }

    /**
     * @return \App\Alexa\Response
     */
    public function routeRequest(){
        switch($this->type){
            case "LaunchRequest":
                $response = $this->mngLaunchRequest();
                break;
            case "IntentRequest":
                $response = $this->mngIntentRequest();
                break;
            case "SessionEndedRequest":
                $response = $this->mngSessionEndedRequest();
                break;
            case "CanFulfillIntentReqeuest":
                $response = $this->mngCanFulfillIntentReqeuest();
                break;
        }
        return $response;
    }

    /**
     * @return \App\Alexa\Response
     */
    private function mngLaunchRequest(){
        $response = new Response();
        $response->setOutputSpeech("OK");
        return $response;
    }
    /**
     * @return \App\Alexa\Response
     */
    private function mngIntentRequest(){
        $response = new Response();
        $response->setOutputSpeech("OK");
        return $response;
    }
    /**
     * @return \App\Alexa\Response
     */
    private function mngSessionEndedRequest(){
        $response = new Response();
        //TODO: ...
        return $response;
    }
    /**
     * @return \App\Alexa\Response
     */
    private function mngCanFulfillIntentReqeuest(){
        $response = new Response();
        //TODO: ...
        return $response;
    }

}