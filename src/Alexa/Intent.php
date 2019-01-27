<?php
/**
 * Created by Pangodream.
 * Date: 26/01/2019
 * Time: 10:09
 */

namespace Alexa;

use Alexa\Slot;

class Intent
{
    /** @var string $name */
    public $name;
    /** @var string $confirmationStatus */
    public $confirmationStatus;
    /** @var Slot[] $slots */
    public $slots;
    /** @var array $slotsName */
    public $slotsName;
    /** @var integer $slotsCount */
    public $slotsCount;

    public function __construct()
    {
        $this->slots = array();
        $this->slotsName = array();
    }

}