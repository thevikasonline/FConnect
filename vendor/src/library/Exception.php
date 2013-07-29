<?php
namespace Vikas;

use Exception as PHPException;

class Exception
        extends PHPException {

    protected $code;
    protected $message;
    protected $previous;

    public function __construct ($message = null, $code = null, $previous = null) {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
        parent::__construct($this->message, $this->code, $this->previous);
    }

}

?>
