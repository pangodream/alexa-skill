<?php
/**
 * Created by Pangodream.
 * Date: 27/01/2019
 * Time: 9:40
 */

namespace Alexa;

use Alexa\Application;
use Alexa\User;

class Session
{
    /** @var bool $new */
    public $new;
    /** @var string $sessionId */
    public $sessionId;

    /** @var Application $application */
    public $application;
    /** @var User $user */
    public $user;

    public function __construct()
    {
        $this->application = new Application();
        $this->user = new User();
    }
}