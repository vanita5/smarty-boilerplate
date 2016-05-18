<?php

require_once('pdo.lib.php');

require_once('CWLog.php');

/**
 * Class PDO_ExampleDB
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2015-01-01
 * @extends PDOLib
 */
class PDO_ExampleDB extends PDOLib {

    /**
     * @var CWLog
     */
    private $logger;

    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;

    /**
     * {@inheritDoc}
     */
    public function __construct() {
        $this->engine = 'mysql';
        $this->host = '127.0.0.1';
        $this->database = 'example';
        $this->user = 'root';
        $this->pass = '';
        $dsn = $this->engine.':dbname='.$this->database.";host=".$this->host.";charset=utf8";

        try {
            parent::__construct($dsn, $this->user, $this->pass);
        } catch (Exception $e) {
            $this->logger = CWLog::singleton(CW_LOG_HANDLER_FILE, dirname(__FILE__).'/../log/main.log', 'DB_EXAMPLE_CONNECT', [
                'mode' => 0777,
                'timeFormat' => '%Y-%m-%d %H:%M:%S'
            ]);
            $this->logger->alert("Could not connect to database: ".$e->getMessage());
            print "Could not connect to database: ".$e->getMessage();
        }

        //UTF-8
        $this->query("SET CHARACTER SET utf8");
        $this->query('SET NAMES utf8');
    }
}

?>
