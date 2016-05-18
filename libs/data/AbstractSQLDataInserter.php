<?php

require_once 'interface/ISQLDataInserter.php';

/**
 * Class AbstractSQLDataInserter
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2016-02-09
 * @project
 */
abstract class AbstractSQLDataInserter implements ISQLDataInserter {

    /**
     * PDO instance
     *
     * @var PDOLib
     */
    protected $pdo;

    /**
     * AbstractSQLDataInserter constructor.
     *
     * @param PDOLib $pdo
     */
    public function __construct($pdo) {
        if (!$pdo) throw new InvalidArgumentException("Parameter must not be null.");
        $this->pdo = $pdo;
    }

    /**
     * Get raw SQL Statement
     *
     * @return string
     */
    abstract function getSQL();

    /**
     * Build and return the prepared statement.
     * Values should be bind to the PDOStatement in this function.
     *
     * @return PDOStatement
     */
    function getPreparedStatement() {
        return $this->pdo->prepare($this->getSQL());
    }

    /**
     * Main function which inserts the data
     *
     * @return bool
     * @throws ImportException
     */
    function insert() {
        if (!$this->pdo->queryDatabaseStatement($this->getPreparedStatement(), SQL_NONE)) {
            if ($this->pdo->error == '23000' || $this->pdo->error == '00000') {
                throw new ImportException("Duplicate entry. \n MySQL Error: ".$this->pdo->errorCode());
            } else if (isset($this->pdo->error)) {
                throw new ImportException("MySQL Error: ".$this->pdo->errorCode());
            }
        }
    }
}

?>
