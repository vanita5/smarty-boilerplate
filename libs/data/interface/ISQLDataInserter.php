<?php

/**
 * Interface SQLDataInserter
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2016-02-09
 * @project
 */
interface ISQLDataInserter {

    /**
     * Get raw SQL Statement
     *
     * @return string
     */
    function getSQL();

    /**
     * Build and return the prepared statement.
     * Values should be bind to the PDOStatement in this function.
     *
     * @return PDOStatement
     */
    function getPreparedStatement();

    /**
     * Main function which inserts the data
     *
     * @return bool
     */
    function insert();

}

?>
