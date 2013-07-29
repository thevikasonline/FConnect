<?php
namespace Vikas;

use Vikas\Registry;
use Vikas\Exception as RequestException;

class Request {

    const FILE_EXT = ".php";

    public static function get_request ($key = null) {
        if ($key) {
            return isset($_REQUEST['key']) ? $_REQUEST['key'] : null;
        }
        return $_REQUEST;
    }

    public function get_modules () {
        $modules = false;

        if (Registry::isRegistered("config")) {
            $config = Registry::get("config");
            $modules = isset($config['application']['modules']) ? $config['application']['modules'] : false;
        }

        if (!$modules) {
            throw new RequestException("Modeuls are not defined.");
        }

        /**
         * Get Default Module
         */
        $all_modules = [
            'default_module' => null,
            'modules' => [],
        ];

        foreach ($modules as $key => $value) {
            if (isset($value['default']) && $value['default'] == true) {
                $all_modules['default_module'] = $value['name'];
            }
            $all_modules['modules'][$key] = $value['name'];
        }

        /**
         * Get Current Module
         */
        $module = self::get_request("module");

        if (!is_null($module) && in_array($module, $all_modules['modules'])) {
            return $module;
        }
        /**
         * Check for default module
         */
        $default_module = $all_modules['default_module'];
        if (is_null($default_module) && is_null($module)) {
            if (!$modules) {
                throw new RequestException("NO default modeul is defined.");
            }
        }
        return $default_module;
    }

    public static function _require () {
        if (func_num_args() > 0) {
            $module = func_get_arg(0);
        } else {
            $module = self::get_modules();
        }

        if (Registry::isRegistered("config")) {
            $config = Registry::get("config");
        }

        $module_path = $config['application']['module_dir'];
        $baseDir = getcwd();
        $module_file = $baseDir . $module_path . DIRECTORY_SEPARATOR . $module . self::FILE_EXT;
        if (file_exists($module_file)) {
            return $module_file;
        }
    }
    
    
    public static function baseUrl () {
        return self::_self();
    }

    public static function url ($params) {
        $module = isset($params[0]) && !empty($params[0]) ? $params[0] : self::get_modules();
        $method = isset($params[1]) && !empty($params[1]) ? $params[1] : 'login';
        return self::_serverUri() . "/{$module}/{$method}";
    }

    protected static function _serverUri () {
        $host = $_SERVER['HTTP_HOST'];
        $uri = 'http://' . $host . self::_self();
        return $uri;
    }

    protected static function _self () {
        $php_self = $_SERVER['PHP_SELF'];
        $self_array = array_reverse(
                explode(DIRECTORY_SEPARATOR, $php_self)
        );
        unset($self_array[0]);
        $self_array = array_reverse(array_values($self_array));
        $_self = implode(DIRECTORY_SEPARATOR, $self_array);
        return $_self;
    }

}

?>
