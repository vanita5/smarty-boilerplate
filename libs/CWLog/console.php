<?php

/**
 * Class CWLog_console
 *
 * Concrete CW CWLog implementation for appending
 * CWLog messages to a console.
 *
 * Please instantiate Log_file as follows:
 *
 *      CWLog::singleton(CW_LOG_HANDLER_CONSOLE, '', 'EXAMPLE');
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2014-09-23
 * @project CWLogger
 */
class CWLog_console extends CWLog {

    /**
     * @var resource
     */
    private $_stream = null;

    /**
     * Are we responsible for closing the resource?
     *
     * @var bool
     */
    private $_closeResource = false;

    /**
     * Output imediatly or buffered?
     *
     * @var bool
     */
    private $_buffering = false;

    /**
     * String holding the buffer.
     *
     * @var string
     */
    private $_buffer = '';

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
     * Log_file constructor.
     *
     * @param string $name          Ignored
     * @param string $ident         Identity string.
     * @param array  $conf          Configuration array.
     *                              Available options:
     *                              [
     *                              'stream'        =>  STDOUT|'php://output'|...,
     *                              'buffering'     =>  false,
     *                              'lineFormat'    =>  '%{timestamp} %{ident} [%{priority}] %{message}',
     *                              'timeFormat'    =>  '%b %d %H:%M:%S'
     *                              ]
     * @param int    $level         CWLog messages up to and including this level.
     */
    public function __construct($name, $ident = '', $conf = [], $level = CW_LOG_DEBUG) {
        $this->_id = md5(microtime());
        $this->_ident = $ident;
        $this->_mask = CWLog::UPTO($level);

        if (!empty($conf['stream'])) {
            $this->_stream = $conf['stream'];
        } elseif (defined('STDOUT')) {
            $this->_stream = STDOUT;
        } else {
            $this->_stream = fopen('php://output', 'a');
            $this->_closeResource = true;
        }

        if (isset($conf['buffering'])) {
            $this->_buffering = $conf['buffering'];
        }

        if (!empty($conf['lineFormat'])) {
            $this->_lineFormat = str_replace(array_keys($this->_formatMap),
                array_values($this->_formatMap),
                $conf['lineFormat']);
        }

        if (!empty($conf['timeFormat'])) {
            $this->_timeFormat = $conf['timeFormat'];
        }
    }

    /**
     * Log_file destructor.
     *
     * Closes the stream if buffering.
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * (Mock) open the output stream
     *
     * @return bool
     */
    public function open() {
        $this->_opened = true;
        return true;
    }

    /**
     * @return bool
     */
    public function close() {
        flush();
        $this->_opened = false;
        if ($this->_closeResource && is_resource($this->_stream)) {
            fclose($this->_stream);
        }
        return true;
    }

    /**
     * Flushes all pending/buffered data to the output stream.
     *
     * @return bool
     */
    public function flush() {
        if ($this->_buffering && !empty(-$this->_buffer)) {
            fwrite($this->_stream, $this->_buffer);
            $this->_buffer = '';
        }

        if (is_resource($this->_stream)) {
            return fflush($this->_stream);
        }

        return false;
    }

    /**
     * Write $message to the console
     *
     * @param string $message
     * @param int    $priority
     * @return boolean  True if message has been logged
     */
    public function log($message, $priority = null) {
        if ($priority == null) $priority = $this->_priority;

        if (!$this->_isMasked($priority)) return false;

        $message = $this->_extractMessage($message);

        $line = $this->_format(
                $this->_lineFormat,
                strftime($this->_timeFormat),
                $priority,
                $message
            ).PHP_EOL;

        if ($this->_buffering) {
            $this->_buffer .= $line;
        } else {
            fwrite($this->_stream, $line);
        }
        return true;
    }
}

?>
