<?php

define('CW_LOG_EMERG', 0);    /* System is unusable */
define('CW_LOG_ALERT', 1);    /* Immediate action required */
define('CW_LOG_CRIT', 2);     /* Critical error conditions */
define('CW_LOG_WARN', 4);     /* Warning conditions */
define('CW_LOG_ERROR', 3);    /* Error conditions */
define('CW_LOG_NOTICE', 5);   /* Notice - not significant */
define('CW_LOG_INFO', 6);     /* Informational */
define('CW_LOG_DEBUG', 7);    /* Debug messages */

define('CW_LOG_ALL', 0xffffffff);     /* All messages mask */
define('CW_LOG_NONE', 0x00000000);    /* No messages mask */

/* Currently implemented log types and handler */
define('CW_LOG_HANDLER_FILE', 'file');    /* Append log messages to a file */
define('CW_LOG_HANDLER_CONSOLE', 'console'); /* Write to stdin or similar streams */

/**
 * Class CWLog
 *
 * This class provides a simple logging interface inspired by
 * the deprecated PEAR CWLog class.
 * This implementation runs standalone and offers a similar API
 * as the PEAR CWLog implementation.
 *
 * NOTE: CWLog observer/listener are not available.
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2014-09-22
 * @version 0.1
 * @project CWLogger
 */
abstract class CWLog {

    /**
     * Contains available/implemented CWLog handlers/subclasses
     *
     * @const array
     */
    const _AVAILABLE_HANDLERS = [
        CW_LOG_HANDLER_FILE,
        CW_LOG_HANDLER_CONSOLE
    ];

    /**
     * Indicates whether or not the log can been opened / connected.
     *
     * @var bool
     */
    protected $_opened = false;

    /**
     * Unique id of the instance
     *
     * @var int
     */
    protected $_id = 0;

    /**
     * Label to identify the context of this log instance
     *
     * @var string
     */
    protected $_ident = '';

    /**
     * Default logging priority
     *
     * @var int
     */
    protected $_priority = CW_LOG_INFO;

    /**
     * Bitmask for allowed log levels
     *
     * @var int
     */
    protected $_mask = CW_LOG_ALL;

    /**
     * Starting depth to use when walking a backtrace in search of the
     * function that invoked the log system.
     *
     * @var int
     */
    protected $_backtrace_depth = 0;

    /**
     * Static array containing unique instances
     * of concrete CWLog implementations.
     *
     * @var array
     */
    private static $_instances = [];

    /**
     * Maps canonical format keys to position arguments for use in building
     * "line format" strings.
     *
     * @var array
     */
    protected $_formatMap = [
        '%{timestamp}' => '%1$s',
        '%{ident}' => '%2$s',
        '%{priority}' => '%3$s',
        '%{message}' => '%4$s',
        '%{file}' => '%5$s',
        '%{line}' => '%6$s',
        '%{function}' => '%7$s',
        '%{class}' => '%8$s',
        '%\{' => '%%{'
    ];

    /**
     * CWLog constructor.
     * Disabled due to Singleton design pattern
     */
    private function __construct() {
        //DISABLED
    }

    /**
     * Attempts to return a concrete CWLog instance of type $handler.
     * Internal use only for now.
     *
     * @param string $handler       Type of the concrete CWLog class to return.
     *                              Valid/Implemented subclasses are issued by
     *                              a CW_LOG_HANDLER_* constant.
     *                              Available handlers:
     *                              - CW_LOG_HANDLER_FILE
     * @param string $name          Name of the logfile, database table,...
     *                              Depends on the subclass implementation.
     * @param string $ident         Label to identify the context of the log entries
     *                              (e.g. project name).
     * @param array  $conf          An array containing additional values/configuration
     *                              information some subclasses might need.
     * @param int    $level         CWLog messages up to and including this level.
     *                              CWLog levels are issued by the CW_LOG_* constants.
     *                              Available log levels:
     *                              - CW_LOG_EMERG
     *                              - CW_LOG_ALERT
     *                              - CW_LOG_CRIT
     *                              - CW_LOG_ERROR
     *                              - CW_LOG_WARN
     *                              - CW_LOG_NOTICE
     *                              - CW_LOG_INFO
     *                              - CW_LOG_DEBUG
     * @return CWLog|null         New CWLog instance or null on error
     */
    private static function factory($handler, $name = '', $ident = '', $conf = [], $level = CW_LOG_INFO) {
        $handler = strtolower($handler);
        if (!in_array($handler, CWLog::_AVAILABLE_HANDLERS)) return null;

        $class = 'CWLog_'.$handler;
        $classfile = 'CWLog/'.$handler.'.php';

        /*
         * Try to include the concrete handler class
         */
        if (!class_exists($class, false)) {
            include_once $classfile;
        }

        /**
         * If the class exists, create a new instance and return it.
         */
        if (class_exists($class, false)) {
            return new $class($name, $ident, $conf, $level);
        }

        return null;
    }

    /**
     * Attempts to return a concrete CWLog instance of type $handler.
     * Only creates a new instance, if no CWLog instance with the same
     * parameters exists.
     *
     * @param string $handler       Type of the concrete CWLog class to return.
     *                              Valid/Implemented subclasses are issued by
     *                              a CW_LOG_HANDLER_* constant.
     *                              Available handlers:
     *                              - CW_LOG_HANDLER_FILE
     * @param string $name          Name of the logfile, database table,...
     *                              Depends on the subclass implementation.
     * @param string $ident         Label to identify the context of the log entries
     *                              (e.g. project name).
     * @param array  $conf          An array containing additional values/configuration
     *                              information some subclasses might need.
     * @param int    $level         CWLog messages up to and including this level.
     *                              CWLog levels are issued by the CW_LOG_* constants.
     *                              Available log levels:
     *                              - CW_LOG_EMERG
     *                              - CW_LOG_ALERT
     *                              - CW_LOG_CRIT
     *                              - CW_LOG_ERROR
     *                              - CW_LOG_WARN
     *                              - CW_LOG_NOTICE
     *                              - CW_LOG_INFO
     *                              - CW_LOG_DEBUG
     * @return CWLog|null         New CWLog instance or null on error
     */
    public static function singleton($handler, $name = '', $ident = '', $conf = [], $level = CW_LOG_INFO) {
        if (!isset(CWLog::$_instances)) CWLog::$_instances = [];

        $sig = serialize([
            $handler,
            $name,
            $ident,
            $conf,
            $level
        ]);
        if (!isset(CWLog::$_instances[$sig])) {
            CWLog::$_instances[$sig] = CWLog::factory($handler, $name, $ident, $conf, $level);
        }

        return CWLog::$_instances[$sig];
    }

    /**
     * @return bool
     */
    abstract function open();

    /**
     * @return bool
     */
    abstract function close();

    /**
     * @return bool
     */
    abstract function flush();

    /**
     * @param string $message
     * @param int    $priority
     * @return boolean  True if message has been logged
     */
    abstract function log($message, $priority = null);

    /**
     * CWLog emergency message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function emerg($message) {
        return $this->log($message, CW_LOG_EMERG);
    }

    /**
     * CWLog alert message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function alert($message) {
        return $this->log($message, CW_LOG_ALERT);
    }

    /**
     * CWLog critical message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function crit($message) {
        return $this->log($message, CW_LOG_CRIT);
    }

    /**
     * CWLog error message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function error($message) {
        return $this->log($message, CW_LOG_ERROR);
    }

    /**
     * CWLog warning message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function warning($message) {
        return $this->log($message, CW_LOG_WARN);
    }

    /**
     * CWLog emergency message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function notice($message) {
        return $this->log($message, CW_LOG_NOTICE);
    }

    /**
     * CWLog info message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function info($message) {
        return $this->log($message, CW_LOG_INFO);
    }

    /**
     * CWLog debug message.
     *
     * @param   mixed $message String or object containing the error message
     * @return  boolean             True if message has been logged
     */
    public function debug($message) {
        return $this->log($message, CW_LOG_DEBUG);
    }

    /**
     * Return the string representation of the message object.
     *
     * Strings will be returned unchanged.
     * For objects, this method will either return the result
     * of present toString() or getMessage() functions
     * or a serialized representation of the object.
     *
     * @param   mixed $message Original message. Might be any Object or a string.
     * @return string           String representation of the message.
     */
    protected function _extractMessage($message) {
        if (is_string($message)) return $message;

        if (is_object($message)) {
            if (method_exists($message, 'getmessage')) return $message->getMessage();
            if (method_exists($message, 'tostring')) return $message->toString();
            if (method_exists($message, '__tostring')) return (string) $message;
        }
        return var_export($message);
    }

    /**
     * Using debug_backtrace(), returns the file, line, and enclosing function
     * name of the source code context from which log() was invoked.
     *
     * @param   int $depth      Initial number of frames we step back in the backtrace.
     * @return  array           Array containing four strings: the filename, the line,
     *                          the function name, and the class name from which log()
     *                          was called.
     */
    private function _getBacktraceVars($depth) {

        /* Start by generating a backtrace from the current call (here). */
        $bt = debug_backtrace();

        /* Store some handy shortcuts to our previous frames. */
        $bt0 = isset($bt[$depth]) ? $bt[$depth] : null;
        $bt1 = isset($bt[$depth + 1]) ? $bt[$depth + 1] : null;

        /*
         * If we were ultimately invoked by the composite handler, we need to
         * increase our depth one additional level to compensate.
         */
        $class = isset($bt1['class']) ? $bt1['class'] : null;
        if ($class !== null) {
            $depth++;
            $bt0 = isset($bt[$depth]) ? $bt[$depth] : null;
            $bt1 = isset($bt[$depth + 1]) ? $bt[$depth + 1] : null;
            $class = isset($bt1['class']) ? $bt1['class'] : null;
        }

        /*
         * We're interested in the frame which invoked the log() function, so
         * we need to walk back some number of frames into the backtrace.  The
         * $depth parameter tells us where to start looking.   We go one step
         * further back to find the name of the encapsulating function from
         * which log() was called.
         */
        $file = isset($bt0) ? $bt0['file'] : null;
        $line = isset($bt0) ? $bt0['line'] : 0;
        $func = isset($bt1) ? $bt1['function'] : null;

        /*
         * However, if log() was called from one of our "shortcut" functions,
         * we're going to need to go back an additional step.
         */
        if (in_array($func, [
            'emerg',
            'alert',
            'crit',
            'error',
            'warning',
            'notice',
            'info',
            'debug'
        ])) {
            $bt2 = isset($bt[$depth + 2]) ? $bt[$depth + 2] : null;

            $file = is_array($bt1) ? $bt1['file'] : null;
            $line = is_array($bt1) ? $bt1['line'] : 0;
            $func = is_array($bt2) ? $bt2['function'] : null;
            $class = isset($bt2['class']) ? $bt2['class'] : null;
        }

        /*
         * If we couldn't extract a function name (perhaps because we were
         * executed from the "main" context), provide a default value.
         */
        if ($func === null) {
            $func = '(none)';
        }

        /* Return a 4-tuple containing (file, line, function, class). */
        return [
            $file,
            $line,
            $func,
            $class
        ];
    }

    /**
     * @param int $depth
     */
    public function setBacktraceDepth($depth) {
        $this->_backtrace_depth = $depth;
    }

    /**
     * Produces a formatted log line based on a format string and a set of
     * variables representing the current log record and state.
     *
     * @param string $format
     * @param string $timestamp
     * @param int    $priority
     * @param string $message
     * @return string
     */
    protected function _format($format, $timestamp, $priority, $message) {
        /*
         * If the format string references any of the backtrace-driven
         * variables (%5 %6,%7,%8), generate the backtrace and fetch them.
         */
        if (preg_match('/%[5678]/', $format)) {
            /* Plus 2 to account for our internal function calls. */
            list($file, $line, $func, $class) = $this->_getBacktraceVars(
                (int) $this->_backtrace_depth + 2
            );
        }

        /*
         * Build the formatted string.  We use the sprintf() function's
         * "argument swapping" capability to dynamically select and position
         * the variables which will ultimately appear in the log string.
         */
        return sprintf($format,
            $timestamp,
            $this->_ident,
            $this->priorityToString($priority),
            $message,
            isset($file) ? $file : '',
            isset($line) ? $line : '',
            isset($func) ? $func : '',
            isset($class) ? $class : '');
    }

    /**
     * Returns the string representation of a CW_LOG_* integer constant.
     *
     * @param int $priority A CW_LOG_* integer constant.     *
     * @return string           The string representation of $level.     *
     */
    function priorityToString($priority) {
        $levels = [
            CW_LOG_EMERG => 'emergency',
            CW_LOG_ALERT => 'alert',
            CW_LOG_CRIT => 'critical',
            CW_LOG_ERROR => 'error',
            CW_LOG_WARN => 'warning',
            CW_LOG_NOTICE => 'notice',
            CW_LOG_INFO => 'info',
            CW_LOG_DEBUG => 'debug'
        ];

        return $levels[$priority];
    }

    /**
     * Calculate the log mask for the given priority.
     *
     * @param int $priority
     * @return int log mask
     */
    public static function MASK($priority) {
        return (1 << $priority);
    }

    /**
     * Calculate the log mask for all priorities up to the given priority.
     *
     * @param int $priority
     * @return int
     */
    public static function UPTO($priority) {
        return CWLog::MAX($priority);
    }

    /**
     * Calculate the log mask for all priorities greater than or equal to the
     * given priority.  In other words, $priority will be the lowest priority
     * matched by the resulting mask.
     *
     * @param int $priority The minimum priority covered by this mask.
     * @return int The resulting log mask.
     */
    public static function MIN($priority) {
        return CW_LOG_ALL ^ ((1 << $priority) - 1);
    }

    /**
     * Calculate the log mask for all priorities less than or equal to the
     * given priority.  In other words, $priority will be the highests priority
     * matched by the resulting mask.
     *
     * @param int $priority The maximum priority covered by this mask.
     * @return int The resulting log mask.
     */
    public static function MAX($priority) {
        return ((1 << ($priority + 1)) - 1);
    }

    /**
     * @return int
     */
    public function getMask() {
        return $this->_mask;
    }

    /**
     * @param int $mask
     */
    public function setMask($mask) {
        $this->_mask = $mask;
    }

    /**
     * Check if the given priority is included in the current level mask.
     *
     * @param $priority
     * @return int      True if the given priority is included
     */
    protected function _isMasked($priority) {
        return (CWLog::MASK($priority) & $this->_mask);
    }

    /**
     * @return int
     */
    public function getPriority() {
        return $this->_priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority) {
        $this->_priority = $priority;
    }

    /**
     * @return string
     */
    public function getIdent() {
        return $this->_ident;
    }

    /**
     * @param string $ident
     */
    public function setIdent($ident) {
        $this->_ident = $ident;
    }
}

?>
