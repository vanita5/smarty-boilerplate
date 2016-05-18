<?php
###############################################
## Project: Boilerplate
## Author:  vanita5 <mail@vanita5.de>
## Date:    Feb 2015
## File:    index.php
## Using:   Smarty
###############################################

require_once 'constants.php';

/**
 * Open a new session
 * Session timeout renewed on every new pageload, so the
 * user will stay logged in
 * as long as he's been active in the last hour.
 *
 * If there are multiple php files, this codeblock has
 * to be copied at the top of every file.
 */
session_name(BOILERPLATE_SESSION);
session_set_cookie_params(BOILERPLATE_SESSION_EXPIRES);
session_start();

require_once('SmartyWrapper.php');


####################################################################

$smarty = new SmartyWrapper();

$language = 'DE';

$smarty->config_load('example.conf', $language);

$smarty->display('index.tpl');

?>
