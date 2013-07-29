<?php
namespace Vikas;

use ArrayObject;
use Vikas\Exception as VikasException;

class Registry
        extends ArrayObject {

    private static $_storageClassName = '\\Vikas\\Registry';
    private static $_registry = null;

    public static function getInstance () {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    public static function setInstance (Registry $registry) {
        if (self::$_registry !== null) {
            throw new VikasException('Registry is already initialized');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    protected static function init () {
        self::setInstance(new self::$_storageClassName());
    }

    public static function setClassName ($storageClassName = '\\Vikas\\Registry') {
        if (self::$_registry !== null) {
            throw new Zend_VikasException('Registry is already initialized');
        }

        if (!is_string($storageClassName)) {
            throw new VikasException("Argument is not a class name");
        }

        self::$_storageClassName = $storageClassName;
    }

    public static function _unsetInstance () {
        self::$_registry = null;
    }

    public static function get ($index) {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new VikasException("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    public static function set ($index, $value) {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    public static function isRegistered ($index) {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    public function __construct ($array = array(), $flags = parent::ARRAY_AS_PROPS) {
        parent::__construct($array, $flags);
    }

    public function offsetExists ($index) {
        return array_key_exists($index, $this);
    }

}

?>
