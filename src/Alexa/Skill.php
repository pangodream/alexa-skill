<?php
/**
 * Created by Pangodream.
 * Date: 25/01/2019
 * Time: 19:29
 */

namespace Alexa;
use Alexa\ApiRequest;
use Alexa\ApiResponse;
use Alexa\Slot;
class Skill
{
    /** @var ApiRequest $apiRequest */
    public $apiRequest;
    /** @var ApiResponse $apiResponse */
    public $apiResponse;

    public function __construct()
    {
        $this->apiRequest = new ApiRequest();
        $this->apiResponse = new ApiResponse();
    }
}