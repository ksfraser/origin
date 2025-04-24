<?php


/***************************************************************//**
 * @file class.fa_origin.php
 * @brief This file contains the fa_origin class, which is a subclass of the Origin class.
 * @details The fa_origin class is designed to extend Origin with FrontAccounting specific functionality.
 */

require_once('ClassOrigin.php'); // Reference the main Origin class
/***************************************************************//**
 * 
 *
 * Inherits:
 *   public function __construct($loglevel = PEAR_LOG_DEBUG, $client = null, $moduledir = null, $param_arr = null)
 *   private function object_var_names()
 *   public function set($field, $value = null, $enforce_only_native_vars = true)
 *   public function get($field)
 *   public function handleParam($param_arr)
 *   private function fa_specific_init()
 *   public function __destruct()
 *   public function __get($prop)
 *   public function __isset($prop)
 *   public function is_supported_php()
 *   public function set_array($field, $value = null, $index = 0, $enforce_only_native_vars = true, $autoinc_index = false, $replace = false)
 *   public function unset_var($field)
 *   public function set_var($var, $value)
 *   public function get_var($var)
 *   public function var2data()
 *   public function fields2data($fieldlist)
 *   public function LogError($message, $level = PEAR_LOG_ERR)
 *   public function Log($message, $level = PEAR_LOG_INFO)
 *   public function var_dump($var, $level = PEAR_LOG_DEBUG)
 *   public function objectvars2array()
 *   public function attach_eventloop($create_if_not_exist = true)
 *   public function create_eventloop($moddir = null)
 *   public function tell($msg, $method)
 *   public function tell_eventloop($caller, $event, $msg)
 *   public function dummy($obj, $msg)
 *   public function register_with_eventloop()
 *   public function ObserverRegister($class, $event, $value)
 *   public function build_interestedin()
 *   public function app_log_level($caller, $data)
 *   public function optimizeLogging()
 *   public function notified($obj, $event, $msg)
 *   public function error_handler(Exception $e)
 * 
 * Provides:
 *   public function validate() // CURRENTLY DOES NOT DO ANYTHING *
 * *********************************************************************************/

class fa_origin extends origin
{
	protected $id;
	protected $reference;

	function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null, $moduledir = null, $param_arr = null )
	{
		parent::__construct($loglevel, $client, $moduledir, $param_arr );
        $this->fa_specific_init();
	}
	/*@bool@*/function validate()
	{
	}

    /**
     * @var string FrontAccounting table prefix (e.g., 0_).
     */
    public string $tb_pref = null;

    /**
     * @var string|null Help context for screens in FrontAccounting.
     */
    public ?string $help_context = null;

    /**
     * Initialize FA items we need.
     *
     * This class started as a generic base for FrontAccounting modules.
     * This function takes over the FA-specific code from the constructor.
     *
     * @since 20200708
     * @return void
     */
    function fa_specific_init()
    {
        global $db_connections;
        if (isset($_SESSION['wa_current_user'])) {
            $cu = $_SESSION['wa_current_user']; // FrontAccounting specific
            $compnum = $cu->company;            // FrontAccounting specific
        } else {
            $compnum = 0;
        }

        if (isset($db_connections[$compnum]['tbpref'])) {
            $this->tb_pref = $db_connections[$compnum]['tbpref']; // FrontAccounting specific
        } else {
            $this->set('tb_pref', $compnum . "_", false); // FrontAccounting specific
        }

        $this->set("help_context", "Default HELP");
    }
}

