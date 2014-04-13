<?php
namespace Ratchet\WebSocket\Encoding;

class ToggleableValidator implements ValidatorInterface {
    /**
     * Toggle if checkEncoding checks the encoding or not
     * @var bool
     */
    public $on;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct($on = true) {
        $this->validator = new Validator;
        $this->on        = (boolean)$on;
    }

    /**
     * {@inheritdoc}
     */
    public function checkEncoding($str, $encoding) {
        if (!(boolean)$this->on) {
            return true;
        }

        return $this->validator->checkEncoding($str, $encoding);
    }
}
