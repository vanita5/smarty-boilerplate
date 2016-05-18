<?php

require_once 'interface/ISQLDataProvider.php';

/**
 * Class AbstractSQLDataProvider
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2016-02-08
 * @project
 */
abstract class AbstractSQLDataProvider implements ISQLDataProvider {

    /**
     * PDO instance
     *
     * @var PDOLib
     */
    protected $pdo;

    /**
     * AbstractSQLDataProvider constructor.
     *
     * @param $pdo
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
     * Main function which returns the prepared result.
     *
     * @return array|mixed
     */
    function getData() {
        $this->pdo->queryDatabaseStatement($this->getPreparedStatement(), SQL_ALL, SQL_ASSOC);
        return $this->pdo->record;
    }
}

?>
