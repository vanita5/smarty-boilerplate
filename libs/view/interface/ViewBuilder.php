<?php

/**
 * Interface ViewBuilder
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2015-02-08
 * @project
 */
interface ViewBuilder {

    /**
     * Main function to collect necessary data
     *
     * @return mixed
     */
    function getData();

    /**
     * Main function to build the view.
     * Results can either directly be returned (HTML, JSON,...)
     * or being displayed with Smarty.
     *
     * @return mixed
     */
    function build();

}

?>
