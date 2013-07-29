<?php
namespace Vikas\Db;

use mysqli;
use Vikas\Registry;
use Vikas\Db\Exception as DbException;

class Mysql
        extends mysqli {

    const CONFIG_KEY = "config";

    public function __construct ($config = array()) {
        if (empty($config)) {
            if (Registry::isRegistered(self::CONFIG_KEY)) {
                $config = Registry::get(self::CONFIG_KEY);
            }
        }

        if (sizeof($config) == 0) {
            throw new DbException("Applciation Config not found");
        }

        if (isset($config['application']['db'])) {
            $db = $config['application']['db'];
            
            parent::connect($db['dbhost'], $db['dbuser'], $db['dbpass'], $db['dbname']);
        }
    }

}

?>
