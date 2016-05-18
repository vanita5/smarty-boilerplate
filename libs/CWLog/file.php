<?php

/**
 * Class CWLog_file
 *
 * Concrete CW CWLog implementation for appending
 * CWLog messages to a file.
 *
 * Please instantiate Log_file as follows:
 *
 *      CWLog::singleton(CW_LOG_HANDLER_FILE, 'out.log', 'EXAMPLE');
 *
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2014-09-23
 * @project CWLogger
 */
class CWLog_file extends CWLog {

    /**
     * @var string
     */
    private $_filename = 'out.log';

    /**
     * File handle
     *
     * @var resource
     */
    private $_fp = false;

    /**
     * Indicates if messages should append
     * existing files or create a new file.
     *
     * @var bool
     */
    private $_append = true;

    /**
     * Advisory file logging?
     *
     * @var bool
     */
    private $_locking = false;

    /**
     * Logfile permission
     *
     * @var int
     */
    private $_mode = 0644;

    /**
     * Permission used for creating folders
     *
     * @var int
     */
    private $_dirmode = 0755;

    /**
     * CWLog message line format.
     *
     * @var string
     */
    private $_lineFormat = '%1$s %2$s [%3$s] %4$s';

    /**
     * Time format.
     * Gets passed to strftime()
     *
     * @var string
     */
    private $_timeFormat = '%b %d %H:%M:%S';

    /**
     * EOL character
     *
     * @var string
     */
    private $_eol = PHP_EOL;

    /**
     * Log_file constructor.
     *
     * @param string $name          Filename
     * @param string $ident         Identity string.
     * @param array  $conf          Configuration array.
     *                              Available options:
     *                              [
     *                              'append'        =>  false,
     *                              'locking'       =>  false,
     *                              'mode'          =>  0644,
     *                              'dirmode'       =>  0755,
     *                              'lineFormat'    =>  '%{timestamp} %{ident} [%{priority}] %{message}',
     *                              'timeFormat'    =>  '%b %d %H:%M:%S'
     *                              'eol'           =>  '\n'
     *                              ]
     * @param int    $level         CWLog messages up to and including this level.
     */
    public function __construct($name, $ident = '', $conf = [], $level = CW_LOG_DEBUG) {
        $this->_id = md5(microtime());
        $this->_filename = $name;
        $this->_ident = $ident;
        $this->_mask = CWLog::UPTO($level);

        if (isset($conf['append'])) {
            $this->_append = $conf['append'];
        }

        if (isset($conf['locking'])) {
            $this->_locking = $conf['locking'];
        }

        if (!empty($conf['mode'])) {
            $this->_mode = is_string($conf['mode'])
                ? octdec($conf['mode'])
                : $conf['mode'];
        }

        if (!empty($conf['dirmode'])) {
            $this->_dirmode = is_string($conf['dirmode'])
                ? octdec($conf['dirmode'])
                : $conf['dirmode'];
        }

        if (!empty($conf['lineFormat'])) {
            $this->_lineFormat = str_replace(array_keys($this->_formatMap),
                array_values($this->_formatMap),
                $conf['lineFormat']);
        }

        if (!empty($conf['timeFormat'])) {
            $this->_timeFormat = $conf['timeFormat'];
        }

        if (!empty($conf['eol'])) {
            $this->_eol = $conf['eol'];
        }
    }

    /**
     * Log_file destructor.
     *
     * Closes the file if still open.
     */
    public function __destruct() {
        if ($this->_opened) $this->close();
    }

    /**
     * Creates the given directory path.  If the parent directories don't
     * already exist, they will be created, too.
     *
     * This implementation is inspired by Python's os.makedirs function.
     *
     * @param   string  $path       The full directory path to create.
     * @param   integer $mode       The permissions mode with which the
     *                              directories will be created.
     *
     * @return  True if the full path is successfully created or already
     *          exists.
     */
    private function _mkpath($path, $mode = 0700) {
        $head = dirname($path);
        $tail = basename($path);

        /* Make sure we've split the path into two complete components. */
        if (empty($tail)) {
            $head = dirname($path);
            $tail = basename($path);
        }

        /* Recurse up the path if our current segment does not exist. */
        if (!empty($head) && !empty($tail) && !is_dir($head)) {
            $this->_mkpath($head, $mode);
        }

        /* Create this segment of the path. */
        return file_exists($head) || mkdir($head, $mode);
    }

    /**
     * Opens the log file for output.  If the specified log file does not
     * already exist, it will be created.  By default, new log entries are
     * appended to the end of the log file.
     *
     * This is implicitly called by log(), if necessary.
     *
     * @return bool
     */
    public function open() {
        if (!$this->_opened) {
            /* If the log file's directory doesn't exist, create it. */
            if (!is_dir(dirname($this->_filename))) {
                $this->_mkpath($this->_filename, $this->_dirmode);
            }

            /* Determine whether the log file needs to be created. */
            $creating = !file_exists($this->_filename);

            /* Obtain a handle to the log file. */
            $this->_fp = fopen($this->_filename, ($this->_append) ? 'a' : 'w');

            /* We consider the file "opened" if we have a valid file pointer. */
            $this->_opened = ($this->_fp !== false);

            /* Attempt to set the file's permissions if we just created it. */
            if ($creating && $this->_opened) {
                chmod($this->_filename, $this->_mode);
            }
        }
        return $this->_opened;
    }

    /**
     * Closes the log file if open.
     *
     * @return bool True if file has been closed
     */
    public function close() {
        if ($this->_opened) {
            $this->_opened = !fclose($this->_fp);
        }
        return $this->_opened === false;
    }

    /**
     * Flushes all pending data to the file handle.
     *
     * @return bool True on success
     */
    public function flush() {
        return is_resource($this->_fp) ? fflush($this->_fp) : false;
    }

    /**
     * @param string $message
     * @param int    $priority
     * @return boolean  True if message has been logged
     */
    public function log($message, $priority = null) {
        if ($priority == null) $priority = $this->_priority;

        /* Check priority */
        if (!$this->_isMasked($priority)) return false;

        /* Check/open file */
        if (!$this->_opened && !$this->open()) return false;

        $message = $this->_extractMessage($message);

        $line = $this->_format(
                $this->_lineFormat,
                strftime($this->_timeFormat),
                $priority,
                $message
            ).$this->_eol;

        if ($this->_locking) {
            flock($this->_fp, LOCK_EX);
        }

        $success = fwrite($this->_fp, $line) !== false;

        if ($this->_locking) {
            flock($this->_fp, LOCK_UN);
        }

        return $success;
    }
}

?>
