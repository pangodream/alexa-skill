# alexa-skill
Alexa Skill to Self hosted webservice implementation for PHP

This component allows you to parse and validate the incoming requests from any Alexa skill you create.
The parsing process covers the basic funcionality to know when an Intent has been invocated and how to reply with basic instructions to Alexa.
The request validation checks all these Amazon requirements (except the last one):
* Request timestamp: 150 secs margin
* CertChain URL (protocol, port, host and URI)
* CertChain validation (Subject Altern Name and FromTime-ToTime)
* Signature decryption with Cert PK to match SHA1 hash of request body
* CertChain Root Certification Authority valid to Amazon (still pending)

Though last requisite is still pending, **Functional testing** under **Certification** tab obtains **"Zero errors found."**.

Functional testing, at least in my case, has made 7 requests and the component resolved succesfully all of them:
* Request well formed --> Response OK
* Request well formed --> Response OK
* Request well formed --> Response OK
* CertChain not available in specified URL --> Response 400 bad request
* No signature --> Response 400 bad request
* CertChain not available in specified URL --> Response 400 bad request
* Signature doesn't match calculated hash SHA1 --> Response 400 bad request

All these requirements are described at
[Host a Custom Skill as a Web Service](https://developer.amazon.com/es/docs/custom-skills/host-a-custom-skill-as-a-web-service.html#verifying-that-the-request-was-sent-by-alexa)

# How to
How to use this component

## Composer Installation
```bash
composer require pangodream/alexa-skill
```

## Usage example in Laravel
This is an example made in Laravel, though the component has no external dependencies (appart from PHP 7.0)

Let's create an API entry point in api.php file. 

**api.php**
```php
<?php

use Illuminate\Http\Request;
use App\EntradaLista;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Alexa
Route::post('/alexa', 'AlexaController@request');
```
This entry will define the endpoint URL for Alexa skill endpoint configuration.
In my case:
```bash
https://iot.mydomain.net/api/alexa
```

Now, create the controller and the method to receive the http request and process it
**AlexaController.php**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Alexa\Skill;
use Alexa\Response;

class AlexaController extends Controller
{
    public function request(Request $httpRequest){
        $skill = new Skill();
        $skill->apiRequest->processHttpRequest($httpRequest);
        $request = $skill->apiRequest->request;
        if ($request->type == "IntentRequest"){
            // Aply here the logic you want to perform based on the Alexa request intent
            // $applicationId = $skill->apiRequest->session->application->applicationId;
            // $sessionId = $skill->apiRequest->session->sessionId;
            // $userId = $skill->apiRequest->session->user->userId;
            // $intentId = $skill->apiRequest->request->intent->name;
            // $slotsName = $skill->apiRequest->request->intent->slotsName;
            // $mySlotValue = $skill->apiRequest->request->intent->slots['elemento']->value;
            // ...
            // ...
            // ...
            // And then compose the response you want to send back
            $response = new Response();
            $response->setOutputSpeech("You asked me to switch off ".$request->intent->slots['elemento']->value);
        }else{
            $response = $request->routeRequest();
        }
        return $skill->apiResponse->doReply($response);
    }
}
```

And thats all!

Here is the Alexa Skill JSON definition of the skill I have been playing with

**Skill JSON**
```json
{
    "interactionModel": {
        "languageModel": {
            "invocationName": "sebastian",
            "intents": [
                {
                    "name": "AMAZON.CancelIntent",
                    "samples": []
                },
                {
                    "name": "AMAZON.HelpIntent",
                    "samples": []
                },
                {
                    "name": "AMAZON.StopIntent",
                    "samples": [
                        "Adios"
                    ]
                },
                {
                    "name": "AMAZON.NavigateHomeIntent",
                    "samples": []
                },
                {
                    "name": "Apagar",
                    "slots": [
                        {
                            "name": "elemento",
                            "type": "AMAZON.Room"
                        }
                    ],
                    "samples": [
                        "to switch off {elemento}",
                        "to turn off {elemento}",
                        "to disable {elemento}"
                    ]
                }
            ],
            "types": []
        },
        "dialog": {
            "intents": [
                {
                    "name": "Apagar",
                    "confirmationRequired": false,
                    "prompts": {},
                    "slots": [
                        {
                            "name": "elemento",
                            "type": "AMAZON.Room",
                            "confirmationRequired": false,
                            "elicitationRequired": true,
                            "prompts": {
                                "elicitation": "Elicit.Slot.777962330825.371179603765"
                            }
                        }
                    ]
                }
            ],
            "delegationStrategy": "ALWAYS"
        },
        "prompts": [
            {
                "id": "Elicit.Slot.777962330825.371179603765",
                "variations": [
                    {
                        "type": "PlainText",
                        "value": "What?"
                    }
                ]
            }
        ]
    }
}
```

Once the skill is created and compiled it can be tested:

**You:** "Alexa, ask sebastian to switch off the livingroom fan"

And if everything goes fine, Alexa will reply:

**Alexa:** "You asked me to switch off  the livingroom fan"

Any suggestion or question is always welcome.

Alberto Iriberri

Email: <development@pangodream.com>
Pangodream

## License
This component is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
