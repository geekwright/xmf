<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         Xmf
 * @since           0.1
 * @author          trabis <lusopoemas@gmail.com>
 * @author          Joomla!
 * @version         $Id: Request.php 8065 2011-11-06 02:02:32Z beckmi $
 */

defined('XMF_EXEC') or die('Xmf was not detected');

/**
 * Set the available masks for cleaning variables
 */
define('XMF_REQUEST_NOTRIM', 1);
define('XMF_REQUEST_ALLOWRAW', 2);
define('XMF_REQUEST_ALLOWHTML', 4);

/**
 * Xmf_Request Class
 *
 * This class serves to provide a common interface to access
 * request variables.  This includes $_POST, $_GET, and naturally $_REQUEST.  Variables
 * can be passed through an input filter to avoid injection or returned raw.
 */
class Xmf_Request
{
    /**
     * Gets the request method
     *
     * @return string
     */
    static public function getMethod()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        return $method;
    }

    /**
     * Fetches and returns a given variable.
     *
     * The default behaviour is fetching variables depending on the
     * current request method: GET and HEAD will result in returning
     * an entry from $_GET, POST and PUT will result in returning an
     * entry from $_POST.
     *
     * You can force the source by setting the $hash parameter:
     *
     *   post       $_POST
     *   get        $_GET
     *   files      $_FILES
     *   cookie     $_COOKIE
     *   env        $_ENV
     *   server     $_SERVER
     *   method     via current $_SERVER['REQUEST_METHOD']
     *   default    $_REQUEST
     *
     * @static
     * @param   string  $name       Variable name
     * @param   string  $default    Default value if the variable does not exist
     * @param   string  $hash       Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @param   string  $type       Return type for the variable, for valid values see {@link JFilterInput::clean()}
     * @param   int     $mask       Filter mask for the variable
     * @return  mixed               Requested variable
     */
    static public function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
    {
        // Ensure hash and type are uppercase
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        $type = strtoupper($type);
        $sig = $hash . $type . $mask;

        // Get the input hash
        switch ($hash) {
            case 'GET' :
                $input = &$_GET;
                break;
            case 'POST' :
                $input = &$_POST;
                break;
            case 'FILES' :
                $input = &$_FILES;
                break;
            case 'COOKIE' :
                $input = &$_COOKIE;
                break;
            case 'ENV'    :
                $input = &$_ENV;
                break;
            case 'SERVER'    :
                $input = &$_SERVER;
                break;
            default:
                $input = &$_REQUEST;
                $hash = 'REQUEST';
                break;
        }

        if (isset($input[$name]) && $input[$name] !== null) {
            // Get the variable from the input hash and clean it
            $var = Xmf_Request::_cleanVar($input[$name], $mask, $type);

            // Handle magic quotes compatability
            if (get_magic_quotes_gpc() && ($var != $default) && ($hash != 'FILES')) {
                $var = Xmf_Request::_stripSlashesRecursive($var);
            }
        } else {
            if ($default !== null) {
                // Clean the default value
                $var = Xmf_Request::_cleanVar($default, $mask, $type);
            } else {
                $var = $default;
            }
        }

        return $var;
    }

    /**
     * Fetches and returns a given filtered variable. The integer
     * filter will allow only digits to be returned. This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param   string  $name       Variable name
     * @param   int     $default    Default value if the variable does not exist
     * @param   string  $hash       Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return  int                 Requested variable
     */
    static public function getInt($name, $default = 0, $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'int');
    }

    /**
     * Fetches and returns a given filtered variable.  The float
     * filter only allows digits and periods.  This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param    string    $name        Variable name
     * @param    float     $default     Default value if the variable does not exist
     * @param    string    $hash        Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return   float                  Requested variable
     */
    static public function getFloat($name, $default = 0.0, $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'float');
    }

    /**
     * Fetches and returns a given filtered variable. The bool
     * filter will only return true/false bool values. This is
     * currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param    string    $name        Variable name
     * @param    bool      $default     Default value if the variable does not exist
     * @param    string    $hash        Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return   bool                   Requested variable
     */
    static function getBool($name, $default = false, $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'bool');
    }

    /**
     * Fetches and returns a given filtered variable. The word
     * filter only allows the characters [A-Za-z_]. This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param    string    $name        Variable name
     * @param    string    $default     Default value if the variable does not exist
     * @param    string    $hash        Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return   string                 Requested variable
     */
    static public function getWord($name, $default = '', $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'word');
    }

    /**
     * Fetches and returns a given filtered variable. The cmd
     * filter only allows the characters [A-Za-z0-9.-_]. This is
     * currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param    string    $name        Variable name
     * @param    string    $default     Default value if the variable does not exist
     * @param    string    $hash        Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return   string                 Requested variable
     */
    static public function getCmd($name, $default = '', $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'cmd');
    }

    /**
     * Fetches and returns a given filtered variable. The string
     * filter deletes 'bad' HTML code, if not overridden by the mask.
     * This is currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param    string    $name        Variable name
     * @param    string    $default    Default value if the variable does not exist
     * @param    string    $hash        Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @param    int        $mask        Filter mask for the variable
     * @return    string    Requested variable
     */
    static function getString($name, $default = '', $hash = 'default', $mask = 0)
    {
        // Cast to string, in case JREQUEST_ALLOWRAW was specified for mask
        return (string)Xmf_Request::getVar($name, $default, $hash, 'string', $mask);
    }

    /**
     * @static
     * @param string $name
     * @param array $default
     * @param string $hash
     * @return array
     */
    static public function getArray($name, $default = array(), $hash = 'default')
    {
        return Xmf_Request::getVar($name, $default, $hash, 'array');
    }

    /**
     * @static
     * @param string $name
     * @param string $default
     * @param string $hash
     * @return string
     */
    static function getText($name, $default = '', $hash = 'default')
    {
        return (string)Xmf_Request::getVar($name, $default, $hash, 'string', XMF_REQUEST_ALLOWRAW);
    }

    /**
     * Set a variable in on of the request variables
     *
     * @access    public
     * @param     string    $name         Name
     * @param     string    $value        Value
     * @param     string    $hash         Hash
     * @param     boolean   $overwrite    Boolean
     * @return    string                  Previous value
     */
    static public function setVar($name, $value = null, $hash = 'method', $overwrite = true)
    {
        //If overwrite is true, makes sure the variable hasn't been set yet
        if (!$overwrite && array_key_exists($name, $_REQUEST)) {
            return $_REQUEST[$name];
        }

        // Get the request hash value
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        $previous = array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;

        switch ($hash) {
            case 'GET' :
                $_GET[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'POST' :
                $_POST[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'COOKIE' :
                $_COOKIE[$name] = $value;
                $_REQUEST[$name] = $value;
                break;
            case 'FILES' :
                $_FILES[$name] = $value;
                break;
            case 'ENV'    :
                $_ENV['name'] = $value;
                break;
            case 'SERVER'    :
                $_SERVER['name'] = $value;
                break;
        }

        return $previous;
    }

    /**
     * Fetches and returns a request array.
     *
     * The default behaviour is fetching variables depending on the
     * current request method: GET and HEAD will result in returning
     * $_GET, POST and PUT will result in returning $_POST.
     *
     * You can force the source by setting the $hash parameter:
     *
     *   post        $_POST
     *   get         $_GET
     *   files       $_FILES
     *   cookie      $_COOKIE
     *   env         $_ENV
     *   server      $_SERVER
     *   method      via current $_SERVER['REQUEST_METHOD']
     *   default     $_REQUEST
     *
     * @static
     * @param    string    $hash    to get (POST, GET, FILES, METHOD)
     * @param    int       $mask    Filter mask for the variable
     * @return   mixed              Request hash
     */
    static public function get($hash = 'default', $mask = 0)
    {
        $hash = strtoupper($hash);

        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        switch ($hash) {
            case 'GET' :
                $input = $_GET;
                break;

            case 'POST' :
                $input = $_POST;
                break;

            case 'FILES' :
                $input = $_FILES;
                break;

            case 'COOKIE' :
                $input = $_COOKIE;
                break;

            case 'ENV'    :
                $input = &$_ENV;
                break;

            case 'SERVER'    :
                $input = &$_SERVER;
                break;

            default:
                $input = $_REQUEST;
                break;
        }

        $result = Xmf_Request::_cleanVar($input, $mask);

        // Handle magic quotes compatability
        if (get_magic_quotes_gpc() && ($hash != 'FILES')) {
            $result = Xmf_Request::_stripSlashesRecursive($result);
        }

        return $result;
    }

    /**
     * Sets a request variable
     *
     * @param    array   $array       An associative array of key-value pairs
     * @param    string  $hash        The request variable to set (POST, GET, FILES, METHOD)
     * @param    boolean $overwrite   If true and an existing key is found, the value is overwritten, otherwise it is ingored
     */
    static public function set($array, $hash = 'default', $overwrite = true)
    {
        foreach ($array as $key => $value) {
            Xmf_Request::setVar($key, $value, $hash, $overwrite);
        }
    }


    /**
     * Cleans the request from script injection.
     *
     * @static
     * @return    void
     */
    static public function clean()
    {
        Xmf_Request::_cleanArray($_FILES);
        Xmf_Request::_cleanArray($_ENV);
        Xmf_Request::_cleanArray($_GET);
        Xmf_Request::_cleanArray($_POST);
        Xmf_Request::_cleanArray($_COOKIE);
        Xmf_Request::_cleanArray($_SERVER);

        if (isset($_SESSION)) {
            Xmf_Request::_cleanArray($_SESSION);
        }

        $REQUEST = $_REQUEST;
        $GET = $_GET;
        $POST = $_POST;
        $COOKIE = $_COOKIE;
        $FILES = $_FILES;
        $ENV = $_ENV;
        $SERVER = $_SERVER;

        if (isset ($_SESSION)) {
            $SESSION = $_SESSION;
        }

        foreach ($GLOBALS as $key => $value) {
            if ($key != 'GLOBALS') {
                unset($GLOBALS[$key]);
            }
        }
        $_REQUEST = $REQUEST;
        $_GET = $GET;
        $_POST = $POST;
        $_COOKIE = $COOKIE;
        $_FILES = $FILES;
        $_ENV = $ENV;
        $_SERVER = $SERVER;

        if (isset($SESSION)) {
            $_SESSION = $SESSION;
        }
    }

    /**
     * Adds an array to the GLOBALS array and checks that the GLOBALS variable is not being attacked
     *
     * @access    protected
     * @param    array    $array      Array to clean
     * @param    boolean  $globalise  True if the array is to be added to the GLOBALS
     */
    static protected function _cleanArray(&$array, $globalise = false)
    {
        static $banned = array('_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals');

        foreach ($array as $key => $value) {
            // PHP GLOBALS injection bug
            $failed = in_array(strtolower($key), $banned);

            // PHP Zend_Hash_Del_Key_Or_Index bug
            $failed |= is_numeric($key);
            if ($failed) {
                exit('Illegal variable <b>' . implode('</b> or <b>', $banned) . '</b> passed to script.');
            }
            if ($globalise) {
                $GLOBALS[$key] = $value;
            }
        }
    }

    /**
     * Clean up an input variable.
     *
     * @param mixed $var The input variable.
     * @param int $mask Filter bit mask. 1=no trim: If this flag is cleared and the
     * input is a string, the string will have leading and trailing whitespace
     * trimmed. 2=allow_raw: If set, no more filtering is performed, higher bits
     * are ignored. 4=allow_html: HTML is allowed, but passed through a safe
     * HTML filter first. If set, no more filtering is performed. If no bits
     * other than the 1 bit is set, a strict filter is applied.
     * @param string $type The variable type {@see JFilterInput::clean()}.
     *
     * @return string
     */
    static protected function _cleanVar($var, $mask = 0, $type = null)
    {
        // Static input filters for specific settings
        static $noHtmlFilter = null;
        static $safeHtmlFilter = null;

        // If the no trim flag is not set, trim the variable
        if (!($mask & 1) && is_string($var)) {
            $var = trim($var);
        }

        // Now we handle input filtering
        if ($mask & 2) {
            // If the allow raw flag is set, do not modify the variable
        } else {
            if ($mask & 4) {
                // If the allow html flag is set, apply a safe html filter to the variable
                if (is_null($safeHtmlFilter)) {
                    $safeHtmlFilter = Xmf_Filter_Input::getInstance(null, null, 1, 1);
                }
                $var = $safeHtmlFilter->clean($var, $type);
            } else {
                // Since no allow flags were set, we will apply the most strict filter to the variable
                if (is_null($noHtmlFilter)) {
                    $noHtmlFilter = Xmf_Filter_Input::getInstance( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
                }
                $var = $noHtmlFilter->clean($var, $type);
            }
        }
        return $var;
    }

    /**
     * Strips slashes recursively on an array
     *
     * @access    protected
     * @param     array    $value        Array of (nested arrays of) strings
     * @return    array                  The input array with stripshlashes applied to it
     */
    static protected function _stripSlashesRecursive($value)
    {
        $value = is_array($value) ? array_map(array('XMF_REQUEST', '_stripSlashesRecursive'), $value)
            : stripslashes($value);
        return $value;
    }
}