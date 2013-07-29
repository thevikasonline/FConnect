<?php
namespace Vikas;

use Vikas\Exception as VikasException;

class Vikas {

    public static function dump (&$var, $info = false) {
        $scope = false;
        $prefix = 'unique';
        $suffix = 'value';

        if ($scope) {
            $vals = $scope;
        } else {
            $vals = $GLOBALS;
        }
        $old = $var;
        $var = $new = $prefix . rand() . $suffix;
        $vname = false;
        foreach ($vals as $key => $val) {
            if ($val === $new) {
                $vname = $key;
            }
        }
        $var = $old;

        echo self::prestart();
        if ($info)
            echo "<b style='color: red;'>{$info}:</b><br>";
        self::do_dump($var, '$' . $vname);
        echo self::preend();
    }

    /**
     * Better GI than print_r or var_dump -- but, unlike var_dump, you can only dump one variable.  
     * Added htmlentities on the var content before echo, so you see what is really there, and not the mark-up.
     * 
     * Also, now the output is encased within a div block that sets the background color, font style, and left-justifies it
     * so it is not at the mercy of ambient styles.
     *
     * Inspired from:     PHP.net Contributions
     * Stolen from:       [highstrike at gmail dot com]
     * Modified by:       stlawson *AT* JoyfulEarthTech *DOT* com 
     *
     * @param mixed $var  -- variable to dump
     * @param string $var_name  -- name of variable (optional) -- displayed in printout making it easier to sort out what variable is what in a complex output
     * @param string $indent -- used by internal recursive call (no known external value)
     * @param unknown_type $reference -- used by internal recursive call (no known external value)
     */
    function do_dump (&$var, $var_name = NULL, $indent = NULL, $reference = NULL) {
        $do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
        $reference = $reference . $var_name;
        $keyvar = 'the_do_dump_recursion_protection_scheme';
        $keyname = 'referenced_object_name';

        // So this is always visible and always left justified and readable
        echo "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";

        if (is_array($var) && isset($var[$keyvar])) {
            $real_var = &$var[$keyvar];
            $real_name = &$var[$keyname];
            $type = ucfirst(gettype($real_var));
            echo "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
        } else {
            $var = array($keyvar => $var, $keyname => $reference);
            $avar = &$var[$keyvar];

            $type = ucfirst(gettype($avar));
            if ($type == "String")
                $type_color = "<span style='color:green'>";
            elseif ($type == "Integer")
                $type_color = "<span style='color:red'>";
            elseif ($type == "Double") {
                $type_color = "<span style='color:#0099c5'>";
                $type = "Float";
            } elseif ($type == "Boolean")
                $type_color = "<span style='color:#92008d'>";
            elseif ($type == "NULL")
                $type_color = "<span style='color:black'>";

            if (is_array($avar)) {
                $count = count($avar);
                echo "$indent" . ($var_name ? "$var_name => " : "") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
                $keys = array_keys($avar);
                foreach ($keys as $name) {
                    $value = &$avar[$name];
                    self::do_dump($value, "['$name']", $indent . $do_dump_indent, $reference);
                }
                echo "$indent)<br>";
            } elseif (is_object($avar)) {
                echo "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
                foreach ($avar as $name => $value)
                    self::do_dump($value, "$name", $indent . $do_dump_indent, $reference);
                echo "$indent)<br>";
            } elseif (is_int($avar))
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> $type_color" . htmlentities($avar) . "</span><br>";
            elseif (is_string($avar))
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> $type_color\"" . htmlentities($avar) . "\"</span><br>";
            elseif (is_float($avar))
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> $type_color" . htmlentities($avar) . "</span><br>";
            elseif (is_bool($avar))
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> $type_color" . ($avar == 1 ? "TRUE" : "FALSE") . "</span><br>";
            elseif (is_null($avar))
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> {$type_color}NULL</span><br>";
            else
                echo "$indent$var_name = <span style='color:#666666'>$type(" . strlen($avar) . ")</span> " . htmlentities($avar) . "<br>";

            $var = $var[$keyvar];
        }

        echo "</div>";
    }

    public static function dump_array (&$var, $i = false, $exit = false) {
        echo self::prestart();
        /**
         * print variable
         */
        print_r($var);
        /**
         * Make dump of the variable with info
         */
        if ($i) {
            self::dump($var, $i);
        }
        echo self::preend();
        if ($exit != false) {
            exit;
        }
    }

    public static function html_dump () {
        ob_start();
        $var = func_get_args();
        call_user_func_array('var_dump', $var);

        echo self::var_log(htmlentities(ob_get_clean()));
    }

    /**
     * 
     * @staticvar type $output
     * @staticvar int $depth
     * @param type $varInput
     * @param type $var_name
     * @param type $reference
     * @param type $method
     * @param type $sub
     * @return string
     */
    public static function var_log (&$varInput, $var_name = '', $reference = '', $method = '=', $sub = false) {

        static $output;
        static $depth;

        if ($sub == false) {
            $output = '';
            $depth = 0;
            $reference = $var_name;
            $var = serialize($varInput);
            $var = unserialize($var);
        } else {
            ++$depth;
            $var = & $varInput;
        }

        // constants
        $nl = "\n";
        $block = 'a_big_recursion_protection_block';

        $c = $depth;
        $indent = '';
        while ($c-- > 0) {
            $indent .= '|  ';
        }

        // if this has been parsed before
        if (is_array($var) && isset($var[$block])) {

            $real = & $var[$block];
            $name = & $var['name'];
            $type = gettype($real);
            $output .= $indent . $var_name . ' ' . $method . '& ' . ($type == 'array' ? 'Array' : get_class($real)) . ' ' . $name . $nl;

            // havent parsed this before
        } else {

            // insert recursion blocker
            $var = Array($block => $var, 'name' => $reference);
            $theVar = & $var[$block];

            // print it out
            $type = gettype($theVar);
            switch ($type) {

                case 'array' :
                    $output .= $indent . $var_name . ' ' . $method . ' Array (' . $nl;
                    $keys = array_keys($theVar);
                    foreach ($keys as $name) {
                        $value = &$theVar[$name];
                        self::var_log($value, $name, $reference . '["' . $name . '"]', '=', true);
                    }
                    $output .= $indent . ')' . $nl;
                    break;

                case 'object' :
                    $output .= $indent . $var_name . ' = ' . get_class($theVar) . ' {' . $nl;
                    foreach ($theVar as $name => $value) {
                        self::var_log($value, $name, $reference . '->' . $name, '->', true);
                    }
                    $output .= $indent . '}' . $nl;
                    break;

                case 'string' :
                    $output .= $indent . $var_name . ' ' . $method . ' "' . $theVar . '"' . $nl;
                    break;

                default :
                    $output .= $indent . $var_name . ' ' . $method . ' (' . $type . ') ' . $theVar . $nl;
                    break;
            }

            // $var=$var[$block];
        }
        --$depth;

        if ($sub == false) {
            echo self::prestart();
            echo $output;
            echo self::preend();
        }
    }

    public static function export_var ($var, $return = false) {
        if ($return) {
            return strval(var_export($var), true);
        }
        return var_export($var, true);
    }

    public static function export_var_opt ($var, $is_str = false) {
        $rtn = preg_replace(
                array('/Array\s+\(/', '/\[(\d+)\] => (.*)\n/', '/\[([^\d].*)\] => (.*)\n/'), array('array (', '\1 => \'\2\'' . "\n", '\'\1\' => \'\2\'' . "\n"), substr(print_r($var, true), 0, -1)
        );
        $rtn = strtr($rtn, array("=> 'array ('" => '=> array ('));
        $rtn = strtr($rtn, array(")\n\n" => ")\n"));
        $rtn = strtr($rtn, array("'\n" => "',\n", ")\n" => "),\n"));
        $rtn = preg_replace(array('/\n +/e'), array('strtr(\'\0\', array(\'    \'=>\'  \'))'), $rtn);
        $rtn = strtr($rtn, array(" Object'," => " Object'<-"));
        if ($is_str) {
            return $rtn;
        } else {
            echo $rtn;
        }
    }

    public static function prestart () {
        return "<pre style='margin: 0px 0px 10px 0px; " .
                " display: block; " .
                " background: white; " .
                " color: black; " .
                " font-family: Verdana; " .
                " border: 1px solid #cccccc; " .
                " padding: 5px; font-size: 15px; " .
                " line-height: 15px;' " .
                " >";
    }

    public static function preend () {
        return "</pre>";
    }

}

?>
