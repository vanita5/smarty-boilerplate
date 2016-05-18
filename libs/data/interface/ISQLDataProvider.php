<?php

/**
 * Interface SQLDataProvider
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2016-02-08
 * @project
 */
interface ISQLDataProvider {

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
     * Main function which returns the prepared result.
     *
     * @return array|mixed
     */
    function getData();

}

?>
