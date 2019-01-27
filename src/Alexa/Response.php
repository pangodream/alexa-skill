<?php
/**
 * Created by Pangodream.
 * Date: 26/01/2019
 * Time: 11:55
 */

namespace Alexa;


class Response
{
    /** @var bool $shouldEndSession */
    public $shouldEndSession = false;
    /** @var array $directives */
    public $directives = array();
    /** @var array $items */
    public $items = array();

    public function __construct()
    {
        //Set a default output????
        $this->setOutputSpeech("OK");
    }

    /**
     * @param string $text
     * @param string $type
     */
    public function setOutputSpeech(string $text, string $type = null){
        if($type == null){
            $type = "PlainText";
        }
        $this->items['outputSpeech'] = ["type" => $type, "text" => $text];
    }

    /**
     * @param string $content
     * @param string $title
     * @param string $type
     */
    public function setCard(string $content, string $title, string $type = null){
        if($type == null){
            $type = "PlainText";
        }
        $this->items['card'] = ["type" =>  $type, "title" => $title, "content" => $content];
    }

    /**
     * @param string $text
     * @param string $type
     */
    public function setRepromptOutputSpeech(string $text, string $type = null){
        if($type == null){
            $type = "PlainText";
        }
        $this->items['reprompt'] = ["type" => $type, "text" => $text];
    }

    /**
     * @param bool $endSession
     */
    public function setSessionEnding(bool $endSession = null){
        if($endSession == null){
            $endSession = false;
        }
        $this->shouldEndSession = $endSession;
    }
}