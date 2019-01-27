<?php
/**
 * Created by Pangodream.
 * Date: 26/01/2019
 * Time: 10:11
 */

namespace Alexa;

use Alexa\Session;
use Alexa\Request;
use Alexa\Response;
use Monolog\Logger;

class ApiRequest
{
    /** @var array $original */
    public $original;

    /** @var boolean $validated */
    public $validated;

    /** @var boolean $isValid */
    public $isValid;

    /** @var boolean $allowInvalidRequests */
    public $allowInvalidRequests;

    /** @var string $version */
    public $version;

    /** @var Session $session */
    public $session;

    /** @var Request $Request */
    public $request;

    public function __construct( bool $allowNotValidRequest = null)
    {
        if($allowNotValidRequest == true){
            $this->allowInvalidRequests = true;
        }else{
            $this->allowInvalidRequests = false;
        }
        $this->validated = false;
        $this->isValid = false;
        $this->session = new Session();
        $this->request = new Request();
        $validationResult = $this->validateHttpRequest();
        $this->validated = true;
        if($validationResult == 0){
            $this->isValid = true;
        }else{
            if($this->allowInvalidRequests !== true){
                die();
            }
        }
    }

    /**
     * @param array $httpRequest
     */
    public function processHttpRequest($httpRequest){
        //Original
        $this->original = $httpRequest;
        //Version
        if(isset($httpRequest['version'])){
            $this->version = $httpRequest['version'];
        }
        //Session
        if(isset($httpRequest['session']['new'])){
            $this->session->new = $httpRequest['session']['new'];
        }
        if(isset($httpRequest['session']['sessionId'])){
            $this->session->sessionId = $httpRequest['session']['sessionId'];
        }
        if(isset($httpRequest['session']['application']['applicationId'])){
            $this->session->application->applicationId = $httpRequest['session']['application']['applicationId'];
        }
        if(isset($httpRequest['session']['user']['userId'])){
            $this->session->application->applicationId = $httpRequest['session']['user']['userId'];
        }

        //Request
        if(isset($httpRequest['request']['type'])){
            $this->request->type = $httpRequest['request']['type'];
        }else{
            $this->request->type = null;
        }
        if(isset($httpRequest['request']['timestamp'])){
            $this->request->timestamp = $httpRequest['request']['timestamp'];
            $requestUnixTime = strtotime($this->request->timestamp);
            if(abs(time() - $requestUnixTime) > 150 && $this->allowInvalidRequests !== true){
                Logger::warning("Request timestamp dif > 150 secs");
                die();
            }

        }else{
            $this->request->timestamp = null;
            if($this->allowInvalidRequests !== true){
                Logger::warning("No request timestamp");
                die();
            }
        }
        if(isset($httpRequest['request']['locale'])){
            $this->request->locale = $httpRequest['request']['locale'];
        }else{
            $this->request->locale = null;
        }
        if(isset($httpRequest['request']['dialogState'])){
            $this->request->dialogState = $httpRequest['request']['dialogState'];
        }else{
            $this->request->dialogState = null;
        }
        //Request Intent
        if(isset($httpRequest['request']['intent'])){
            $this->request->intent->name = $httpRequest['request']['intent']['name'];
            $this->request->intent->confirmationStatus = $httpRequest['request']['intent']['confirmationStatus'];
            //Intent Slots
            if(isset($httpRequest['request']['intent']['slots'])){
                $this->request->intent->slotsCount = sizeof($httpRequest['request']['intent']['slots']);
                foreach($httpRequest['request']['intent']['slots'] as $slotName=>$slotData){
                    $this->request->intent->slotsName[] = $slotName;
                    $this->request->intent->slots[$slotName] = new Slot();
                    $this->request->intent->slots[$slotName]->name = $slotName;
                    $this->request->intent->slots[$slotName]->value = $slotData['value'];
                    $this->request->intent->slots[$slotName]->source = $slotData['source'];
                    $this->request->intent->slots[$slotName]->confirmationStatus = $slotData['confirmationStatus'];
                }
            }else{
                $this->request->intent->slots = null;
                $this->request->intent->slotsCount = 0;
                $this->request->intent->slotsName = null;
            }
        }else{
            $this->request->intent = null;
        }
    }
    public function validateHttpRequest(){
        //https://developer.amazon.com/es/docs/custom-skills/host-a-custom-skill-as-a-web-service.html#verifying-that-the-request-was-sent-by-alexa
        $result = 0;

        if(isset($_SERVER['HTTP_SIGNATURECERTCHAINURL'])){
            $signatureCertChainUrl = $_SERVER['HTTP_SIGNATURECERTCHAINURL'];
        }else{
            $signatureCertChainUrl = null;
            $result = 90; //No CertChain URL
        }
        if($signatureCertChainUrl != null) {
            $protocol = parse_url($signatureCertChainUrl, PHP_URL_SCHEME);
            if ($protocol != "https") {
                $result = 91; //Protocol is not HTTPS
            }
            $hostname = parse_url($signatureCertChainUrl, PHP_URL_HOST);
            if ($hostname != "s3.amazonaws.com") {
                $result = 92; //Host is not Amazon
            }
            $port = parse_url($signatureCertChainUrl, PHP_URL_PORT);
            if ($port != "" && $port != "443") {
                $result = 93; //Port is not 443
            }
            $path = parse_url($signatureCertChainUrl, PHP_URL_PATH);
            if (substr($path, 0, 10) != "/echo.api/") {
                $result = 94; //URI path is not correct
            }
            if (isset($_SERVER['HTTP_SIGNATURE'])) {
                $signature = $_SERVER['HTTP_SIGNATURE'];
            } else {
                $signature = null;
                $result = 95; //No request signature header
            }
            $certChain = $this->valCertChainAndRetrieve($signatureCertChainUrl);
            if($certChain == false){
                $result = 96; //Certificate Chain is not valid
            }
            if($signature != null && $certChain !== false){
                //Log::debug("Signature: ".$signature);
                $decodedSignature = base64_decode($signature);
                //Log::debug("Decoded signature: ".$decodedSignature);
                $decrypted = "";

                $publicKey = openssl_pkey_get_public($certChain);
                openssl_public_decrypt ($decodedSignature, $decrypted, $publicKey);

                //Trace::out("Decrypted signature: ".$decrypted);
                //Trace::out("Decrypted signature b64: ".base64_encode($decrypted));
                //sha1
                $entityBody = file_get_contents('php://input');
                $sha1Hash = sha1($entityBody, true);
                if($sha1Hash != substr($decrypted, -strlen($sha1Hash))){
                    $result = 97; //Hash SHA1 doesn't match signature
                }
                //Trace::out("sha1Hash: ".$sha1Hash);
                //Trace::out("sha1Hash b64: ".base64_encode($sha1Hash));
            }
        }
        if($result != 0){
            $resTxt = array(
                0 => "OK",
                90 => "No CertChain URL",
                91 => "Protocol is not HTTPS",
                92 => "Host is not Amazon",
                93 => "Port is not 443",
                94 => "URI path is not correct",
                95 => "No request signature header",
                96 => "Certificate Chain is not valid",
                97 => "Hash SHA1 doesn't match signature",
            );
            Trace::out("Request validation result: ".$result." - ".$resTxt[$result]);
        }
        return $result;
    }
    private function valCertChainAndRetrieve($signatureCertChainUrl){
        $certChain = false;
        //Get the temp folder
        $tmpPath = sys_get_temp_dir();

        //Calculate MD5 with the url to name the .pem file
        $filename = md5($signatureCertChainUrl).".pem";

        if(file_exists($tmpPath."/".$filename)){
            $certChainValidationCached = file_get_contents($tmpPath."/".$filename);
            if($certChainValidationCached !== "Not valid"){
                //Trace::out("CertChain valid retrieved from cache");
                $certChain = $certChainValidationCached;
            }else{
                //Trace::out("CertChain not valid retrieved from cache");
            }
        }else{
            $isValid = true;
            $certChain = file_get_contents($signatureCertChainUrl);
            $dataCert = openssl_x509_parse($certChain);
            //Validate fromTime and toTime
            $fromTime = $dataCert['validFrom_time_t'];
            $toTime = $dataCert['validTo_time_t'];
            $currentTime = time();
            if($currentTime < $fromTime || $currentTime > $toTime){
                $isValid = false;
            }
            $subjectAltName = $dataCert['extensions']['subjectAltName'];
            if($subjectAltName != "DNS:echo-api.amazon.com"){
                $isValid = false;
            }
            //TODO: Validate the complete chain to check that the root is an authority trusted by Amazon
            if($isValid == true){
                file_put_contents($tmpPath."/".$filename, $certChain);
                //Trace::out("CertChain retrieved from source and valid");
            }else{
                //Trace::out("CertChain retrieved from source and not valid");
                file_put_contents($tmpPath."/".$filename, "Not valid");
            }
        }
        return $certChain;
    }
}