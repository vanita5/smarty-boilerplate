<?php

require_once 'interface/ViewBuilder.php';

/**
 * Class SmartyViewBuilder
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2015-02-08
 * @project
 */
abstract class SmartyViewBuilder implements ViewBuilder {

    /**
     * @var Smarty
     */
    private $smarty;

    /**
     * Template filename
     *
     * @var string
     */
    private $template;

    /**
     * SmartyViewBuilder constructor.
     *
     * @param Smarty $smarty
     * @param string $template
     */
    public function __construct($smarty, $template) {
        if ($smarty == null || empty($template)) {
            throw new InvalidArgumentException("Parameter must not be null.");
        }
        $this->smarty = $smarty;
        $this->template = $template;
    }

    /**
     * Main function to collect necessary data
     *
     * @return mixed
     */
    abstract function getData();

    /**
     * Main function to build the view.
     * Results can either directly be returned (HTML, JSON,...)
     * or being displayed with Smarty.
     *
     * @return mixed
     */
    function build() {
        $this->smarty->display($this->template);
    }

    /**
     * Add data to Smarty
     *
     * @param string $resourceName
     * @param mixed  $data
     */
    protected function addData($resourceName, $data) {
        $this->smarty->assign($resourceName, $data);
    }
}

?>
