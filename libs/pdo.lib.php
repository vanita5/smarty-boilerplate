<?php

// define the query types
define('SQL_NONE', 1);
define('SQL_ALL', 2);
define('SQL_INIT', 3);

// define the query formats
define('SQL_ASSOC', 1);
define('SQL_INDEX', 2);

/**
 * Class PDOLib
 *
 * Wrapper for PDO.
 * Works like the old sql.lib.php.
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2015-01-01
 * @version 1.0
 */
class PDOLib extends PDO {

    /**
     * Result of the query
     *
     * @var mixed
     */
    public $record;

    /**
     * Error
     *
     * @var string
     */
    public $error;

    /**
     * @var PDOStatement
     */
    private $result;

    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $user, $pass) {
        parent::__construct($dsn, $user, $pass);
    }

    /**
     * Query the database via SQL statement string
     *
     * @param string $sql    the SQL query
     * @param int    $type   type of the query (SQL_NONE|SQL_ALL|SQL_INIT)
     * @param int    $format format of the query (SQL_ASSOC|SQL_INDEX)
     * @return array|bool|null
     */
    public function queryDatabase($sql, $type = SQL_NONE, $format = SQL_INDEX) {
        $stmt = $this->prepare($sql);
        return $this->queryDatabaseStatement($stmt, $type, $format);
    }

    /**
     * Query the database via a prepared statement.
     * Should be used if working with user input to
     * prevent SQL Injections.
     *
     * @param PDOStatement $stmt   a prepared statement
     * @param int          $type   type of the query (SQL_NONE|SQL_ALL|SQL_INIT)
     * @param int          $format format of the query (SQL_ASSOC|SQL_INDEX)
     * @return array|bool|null
     */
    public function queryDatabaseStatement($stmt, $type = SQL_NONE, $format = SQL_INDEX) {
        $this->record = [];

        //determine fetchmode
        $_fetchmode = ($format == SQL_ASSOC) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;

        if (!($stmt->execute())) {
            $this->error = $this->errorCode();
            return false;
        }

        switch ($type) {
            case SQL_ALL: //Get all rows
                $this->result = false;
                $this->record = $stmt->fetchAll($_fetchmode);
                break;
            case SQL_INIT: //Get the first row
                $this->result = $stmt;
                $this->record = $stmt->fetch($_fetchmode);
                break;
            case SQL_NONE: //Results will be looped over with next()
            default:
                $this->result = $stmt;
                break;
        }
        return true;
    }

    /**
     * Get next row of query result if available
     *
     * @param int $format format of the query (SQL_ASSOC|SQL_INDEX)
     * @return bool success
     */
    public function next($format = SQL_INDEX) {
        //determine fetchmode
        $_fetchmode = ($format == SQL_ASSOC) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;

        /** @noinspection PhpAssignmentInConditionInspection */
        if ($this->record = $this->result->fetch($_fetchmode)) {
            return true;
        } else {
            $this->record = false;
            return false;
        }
    }
}

?>
