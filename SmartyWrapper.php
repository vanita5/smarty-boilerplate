<?php

define('MY_SMARTY_DIR', dirname(__FILE__).'/'); //the trailing slash '/' is required!

//require 'vendor/autoload.php'; NOTE: Does not work on PHP <= 5.2
require(MY_SMARTY_DIR.'vendor/smarty/smarty/libs/Smarty.class.php');

/**
 * Wrapper class for Smarty
 * Instantiate this class instead of the main Smarty class,
 * as it is already preconfigured with the class directory variables.
 *
 * This includes template_dir, compile_dir, config_dir,
 * plugin_dir, register_modifier, debug_tpl, debugging.
 *
 * More Smarty class variables can be found here:
 * <http://www.smarty.net/docsv2/de/api.variables.tpl>
 *
 * If you use this in a new project, make sure to setup
 * the directories correctly or reconfigure the paths in this class.
 *
 * @author  vanita5 <mail@vanita5.de>
 * @date    2014-03-12
 * @extends Smarty
 */
class SmartyWrapper extends Smarty {

    /**
     * {@inheritDoc}
     */
    public function __construct() {

        //Instantiate the parent Smarty class by calling its constructor
        parent::__construct();

        /**
         * Smarty relevant directories with
         * a relative path from this file
         */
        $this->template_dir = MY_SMARTY_DIR.'smarty/templates';
        $this->compile_dir = MY_SMARTY_DIR.'smarty/templates_c';
        $this->config_dir = MY_SMARTY_DIR.'smarty/config';

        $this->plugin_dir = SMARTY_DIR.'plugins';
        $this->debug_tpl = SMARTY_DIR.'debug.tpl';

        //register custom modifiers
//        $this->register_modifier("number_format", "number_format");

        //enable/disable debugging for whole project.
        //if you want to debug only a specific file, you have to
        //override this variable there after instantiating this class.
        $this->debugging = false;
    }
}

?>
