<?php
/**
 * Created by Pangodream.
 * Date: 26/01/2019
 * Time: 11:53
 */

namespace Alexa;
use Alexa\Response;
use Alexa\Request;
use Log;
class ApiResponse
{

    /** @var array $sessionAttributes */
    private $sessionAttributes = array();

    /**
     * @param Response $response
     * @param string $version
     */
    private function compose(Response $response, string $version = null){
        if($version == null){
            $version = "1.0";
        }
        $apiResponse = new \stdClass();
        $apiResponse->version = $version;
        if(sizeof($this->sessionAttributes) > 0){
            $apiResponse->sessionAttributes = $this->sessionAttributes;
        }
        $apiResponse->response = new \stdClass();
        foreach ($response->items as $itemType=>$item){
            $apiResponse->response->$itemType = $item;
        }
        foreach ($response->directives as $directiveType=>$directive){
            $apiResponse->response->$directiveType = $directive;
        }
        $apiResponse->response->shouldEndSession = $response->shouldEndSession;
        return $apiResponse;
    }
    public function doReply(Response $response){
        $apiResponse = $this->compose($response);
        return response()->json($apiResponse);
    }
}