<?php

namespace ksfraser;


//!< WARNING this class has some FrontAccounting specific code

/****************************************************
* 20230423 working on making into composer package
*****************************************************/

require_once( 'origin.inc.php' );
//include_once( 'Log.php' );	//PEAR Logging - included in defines.inc

/*
	# 0 PEAR_LOG_EMERG emerg() System is unusable
	# 1 PEAR_LOG_ALERT alert() Immediate action required
	# 2 PEAR_LOG_CRIT crit() Critical conditions
	# 3 PEAR_LOG_ERR err() Error conditions
	# 4 PEAR_LOG_WARNING warning() Warning conditions
	# 5 PEAR_LOG_NOTICE notice() Normal but significant
	# 6 PEAR_LOG_INFO info() Informational
	# 7 PEAR_LOG_DEBUG debug() Debug-level messages 
*/
/***************************************************************//**
 * Base class for ksf common...  throws EXCEPTIONS for try/catch loops
 *
 * Provides:
   	function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
	/*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
        /*@NULL@* /function Log( $message, $level = PEAR_LOG_EMERG )
        /*@NULL@* /function var_dump( $var, $level = PEAR_LOG_DEBUG )
 *
 *
 * *********************************************************************************/
class origin
{
	/*refactor*/protected $config_values = array();   //!< What fields to be put on config screen.  Probably doesn't belong in this class :(
	/*refactor*/protected $tabs = array();
        /*refactor*/var $help_context;		//!< help context for screens in FA
	/*refactor*/var $tb_pref;			//!< FrontAccounting Table Prefix (i.e. 0_) 
	var $loglevel;			//!< PEAR_LOG level that must be specified to be added to log/errors
	var $errors;			//!< array of error messages
	var $log;			//!< array of log messages
	var $fields;			//!< array of fields in the class
	var $data;			//!< array of data from the fields
	private $testvar;
	var $object_fields;		//!< array of the variables in this object, under '___SOURCE_KEYS_'
	protected $application;		//!< string which application is the child object holding data for
	protected $module;		//!< string which module is the child object holding data for
	protected $container_arr;	//__get/__isset uses this
	//static $eventloop;		//!< object	//https://www.php.net/manual/en/language.variables.scope.php
	protected $eventloop;		//!< object
	protected $client;		//!< object what object instantiated this object
	protected $interestedin;	//!< array
	public $moduledir;		//!< string the path to the modules dir in our app.  Used by eventloop
  	protected $obj_var_name_arr;    //Array of field names in this object that need to be translated in the NVL array
        protected $dest_var_name_arr;   //Array of field names in the DEST Object for translating.
        protected $name_value_list;
	protected $registered_interestedin;	//!<bool have we registered with eventloop yet?


	/************************************************************************//**
	 *constructor
	 *
	 * @startuml
	 * partition Origin {
	 * title Origin Constructor
	 * (*) --> "Set variables"
	 * --> "set variables from SESSION"
	 * --> "call object_var_names()"
	 * -->(*)
	 * }
	 * @enduml
	 *
	 * Should we be calling build_interestedin and register_with_eventloop?
	 * Should attach_eventloop be creating an eventloop if one doesn't exist?
	 *
	 * @param $loglevel int PEAR log levels
	 * @param client Object that uses us.
	 * @param string ModuleDir
	 * @param array ParamArray
	 * @returns null
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null, $moduledir = null, $param_arr = null  )
	{
		$this->registered_interestedin = FALSE;
		
		if( isset( $client ) )
	       	{
			if( is_object( $client ) )
		                $this->set( "client", $client );
		        else
		        if( is_array( $client ) )
		        {
		                //Other version of the class had params as 2nd var
		                $this->handleParam( $client );
		        }
		}
		if( null != $moduledir )
		{
			//A different version of origin has param 3 being the param_array and no moduledir
			if( is_array( $moduledir ) )
                        {
                                $this->handleParam( $moduledir );
                        }
			else
			{
				$this->moduledir = $moduledir;
			}
		}
		else
		{
			if( isset( $this->client->moduledir ) )
			{
				$this->moduledir = $this->client->moduledir;
			}
		}
		if( isset( $param_arr ) )
                {
                        $this->handleParam( $param_arr );
                }
		$this->fa_specific_init();
		$this->loglevel = $loglevel;
		$this->errors = array();
		$this->log = array();
		$this->application = "";
                $this->module = "";
		//Set, with end of constructor values noted
		$this->object_var_names();
		$this->build_interestedin();
		//$this->register_with_eventloop();
                $this->object_var_names();              //Sets object_fields
                $this->container_arr = array();
		$this->obj_var_name_arr = array();
                $this->dest_var_name_arr = array();
                $this->name_value_list = array();
	}
        /**//***************************************************************************
        * Take an array of initialization paramaters and handle
        *
	* @since 20200708
        * @param array paramaters.
        * @return bool
        ********************************************************************************/
        function handleParam( $param_arr )
        {
                if( is_array( $param_arr ) )
                {
                        foreach( $param_arr as $key=>$val)
                        {
                                //Set those values.  But only do native ones
                                $this->set( $key, $val, true );
                        }
                }
                else
                {
                        throw new Exception( "Expecting an array of parameters!  Not an array", KSF_INVALID_DATA_TYPE );
                }
                return true;
        }
        /**//***************************************************************************
        * Initialize FA items we need
        *
        * This class started as a generic base for FrontAccounting modules I was writing.
        * This function takes over the FA specific code from the constructor.
	*
	* @since 20200708
        * @param none uses globals.  Sets tb_pref
        *
        ********************************************************************************/
        function fa_specific_init()
        {
                global $db_connections;
                if( isset( $_SESSION['wa_current_user'] ) )
                {
                        $cu = $_SESSION['wa_current_user'];                     //FrontAccounting specific
                        $compnum = $cu->company;                                //FrontAccounting specific
                }
                else
                {
                        $compnum = 0;
                        //$this->set( 'company_prefix', $compnum );     //db_base trying to set in test cases.
                }
                if( isset( $db_connections[$compnum]['tbpref'] ) )
                        $this->tb_pref = $db_connections[$compnum]['tbpref'];   //FrontAccounting specific
                else
                        $this->set( 'tb_pref', $compnum . "_", false ); //FrontAccounting specific
                $this->set( "help_context", "Default HELP" );
        }
	/***************************************************//**
	*
	* @since 20200708
	* @param none
	* @returns none
	*********************************************************/
	function __destruct()
	{
		//adding because child class called us and error'd out.
	}
	/*********************************************************//**
	 * Magic call method example from http://php.net/manual/en/language.types.object.php
	 *
	 * @since 20200708
	 * @param string function name
	 * @param array array of arguments to pass to function
	 * ************************************************************/
/*
	public function __call($method, $arguments) 
	{
		$arguments = array_merge(array("stdObject" => $this), $arguments); // Note: method argument 0 will always referred to the main class ($this).
	        if (isset($this->{$method}) && is_callable($this->{$method})) {
	            return call_user_func_array($this->{$method}, $arguments);
	        } else {
	            throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
	        }
	    }
 */
	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @since 20200708
	 * @param $prop
	 * @return mixed
	 */
	function __get( $prop ) {
		if( ! is_array( $this->container_arr ) )
			return NULL;
		if ( array_key_exists( $prop, $this->container_arr ) ) {
		    return $this->container_arr[ $prop ];
		}

		if( isset( $this->$prop ) )
		{
			return $this->{$prop};
		}
		throw new Exception( __FILE__ . "::" . __METHOD__ . "Property doesn't exist.", KSF_VALUE_NOT_SET );
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @since 20200708
	 * @param $prop
	 *
	 * @return mixed
	 */
	function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container_arr[ $prop ] );
	}

	/**
	 * Check if the PHP version is supported
	 *
	 * @since 20200708
	 * @return bool
	 */
	function is_supported_php() {
                if( ! isset( $this->min_php ) )
                {
                        throw new Exception( "Can't compare against min_php as it isn't set", KSF_FIELD_NOT_SET );
                }
		if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
		    return false;
		}

		return true;
	}
        /**//***********************************************************************
        * Convert the object variables into an array
        *
	* @since 20200708
        * @param none
        * @returns none sets internal
        ***************************************************************************/
	function object_var_names()
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		$clone = (array) $this;	    		
		$rtn = array ();
		//private prefixed by class name, protected by *
    		$rtn['___SOURCE_KEYS_'] = $clone;
    		//while ( list ($key, $value) = each ($clone) ) {
    		foreach( $clone as $key => $value ) {
        		$aux = explode ("\0", $key);
        		$newkey = $aux[count($aux)-1];
        		$rtn[$newkey] = $rtn['___SOURCE_KEYS_'][$key];
    		}
		$this->object_fields = $rtn;
	}
	//STUB until I can code module and data access...
        /**//*****************************************************************************************
        * User Access Control function (STUB)
        * 
	* @since 20200708
        * @param integer the access level
        * @return bool
        ***********************************************************************************************/
	function user_access( $action )
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		switch( $action )
		{
			case KSF_DATA_ACCESS_READ:
			case KSF_DATA_ACCESS_WRITE:
			case KSF_DATA_ACCESS_READWRITE:
			case KSF_MODULE_ACCESS_READ:
			case KSF_MODULE_ACCESS_WRITE:
			case KSF_MODULE_ACCESS_READWRITE:
				break;
			case KSF_DATA_ACCESS_DENIED:
			case KSF_MODULE_ACCESS_DENIED:
			default:
				throw new Exception( "User doesn't have access to the field", KSF_DATA_ACCESS_DENIED );
		}
		return TRUE;
	}
	/*********************************************//**
	 * Set a variable.  Throws exceptions on sanity checks
	 *
	 * The throwing of exceptions is probably going to break a bunch of code!
	 *
	 * @since 20200708
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
	 * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
         * @returns bool | exception
	 * **********************************************/
	function set( $field, $value = null, $enforce_only_native_vars = true )
	{
		//$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', get_class( $this ) . "::" . __FUNCTION__ . "::" . __LINE__ );
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		if( !isset( $field )  )
			throw new Exception( "Fields not set", KSF_FIELD_NOT_SET );
		try{
			$this->user_access( KSF_DATA_ACCESS_WRITE );
		} 
		catch (Exception $e )
		{
			throw new Exception( $e->getMessage, $e->getCode );
		}

		if( $enforce_only_native_vars )
		{
			if( ! isset( $this->object_fields ) )
			{
				//debug_print_backtrace();
			}
			else if( ! in_array( $field, $this->object_fields ) AND ! array_key_exists( $field, $this->object_fields ) )
                               throw new Exception( "Variable to set ::" . $field . ":: is not a member of the class \n" . print_r( $this->object_fields, true ), KSF_FIELD_NOT_CLASS_VAR );

		}
                if( isset( $value ) )
                {
                        if( is_array( $this->$field ) )
                        {
                                //echo "**********Setting an array \r\n";
                                $this->$field[] = $value;
				return TRUE;
                        }
                        else
                        {
                                //echo "**********Setting field $field \r\n";
                                $this->$field = $value;
				return TRUE;
                        }
                }
                else
                        throw new Exception( "Value to be set not passed in", KSF_VALUE_NOT_SET );

	}
        /*********************************************//**
         * Set an array variable.  Throws exceptions on sanity checks
         *
         * The throwing of exceptions is probably going to break a bunch of code!
	 * @since 20200708
         * @param field string Variable to be set
         * @param value ... value for variable to be set
         * @param int index array index to set
         * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
         * @param bool autoinc_index automatically increment the index if the array value is already set
         * @param bool replace replace the value in the array if the index is already set.  Only one of autoinc and replace should be TRUE
         *
         * **********************************************/
        function set_array( $field, $value = null, $index = 0, $enforce_only_native_vars = true, $autoinc_index = false, $replace = false )
        {
                if( !isset( $field )  )
                        throw new Exception( "Fields not set", KSF_FIELD_NOT_SET );
                try{
                        $this->user_access( KSF_DATA_ACCESS_WRITE );
                }
                catch (Exception $e )
                {
                        throw new Exception( $e->getMessage, $e->getCode );
                }
                if( $enforce_only_native_vars )
                {
                        if( ! isset( $this->object_fields ) )
                        {
                                //debug_print_backtrace();
                                throw new Exception( "object_fields not set so can't check to enforce only_native_vars", KSF_FIELD_NOT_SET );
                        }
                        else if( ! in_array( $field, $this->object_fields ) AND ! array_key_exists( $field, $this->object_fields ) )
                                throw new Exception( "Variable to set ::" . $field . ":: is not a member of the class \n" . print_r( $this->object_fields, true ), KSF_FIELD_NOT_CLASS_VAR );
                }
                if( isset( $value ) )
                {
                        if( ! is_array( $this->field ) )
                        {
                                //Wrong func called.  We can either throw an exception, or call ->set instead.
                                $this->set( $field, $value, $enforce_only_native_vars );
                        }
                        else
                        {
                                if( isset( $this->$field[$index] ) )
                                {
                                        if( $autoinc_index )
                                        {
                                                $index++;
                                                $this->set_array( $field, $value, $index, $enforce_only_native_vars, $autoinc_index );
                                        }
                                        else if( $replace )
                                        {
                                                $this->$field[$index] = $value;
                                        }
                                        else
                                        {
                                                throw new Exception( "Field ::" . $field . ":: is already set but we weren't told to replace!", KSF_VALUE_SET_NO_REPLACE );
                                        }
                                }
                                else
                                {
                                        $this->$field[$index] = $value;
                                }
                        }
                }
                else
                        throw new Exception( "Value to be set not passed in", KSF_VALUE_NOT_SET );
        }
        /**//*******************************************
         * Nullify a field
         *
	 * @since 20200708
         * @param field string variable to nullify
         */
        function unset_var( $field )
        {
                $this->$field = null;
                unset( $this->$field );
        }
	/***************************************************//**
	 * Most of our existing code does not use TRY/CATCH so we will trap here
	 *
	 * Eat any exceptions thrown by ->set
	 * @since 20200708
	 * @param string variable name
	 * @param mixed value to set
	 * @returns bool from ->set
	 * *****************************************************/
	/*@NULL@*/function set_var( $var, $value )
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		try {
			$this->set( $var, $value );
		} catch( Exception $e )
		{
		}
/*
		if(!empty($value) && is_string($value)) {
        		$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    		}
		else
		{
			$this->$var = $value ;
		}
 */
		return;
	}
        /**//************************************************************************
        * Getter function.  Return the value of the field.
        *
	* @since 20200708
        * @param string field name to return
        * @returns mixed value of the field
        *****************************************************************************/
	function get( $field )
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );
		/*
		try{
			$this->user_access( KSF_DATA_ACCESS_READ );
		} 
		catch (Exception $e )
		{
			throw new Exception( $e->getMessage, $e->getCode );
		}
		*/

		if( isset( $this->$field ) )
			return $this->$field;
		else
			throw new Exception( __METHOD__ . "  Field not set.  Can't GET " . $field, KSF_FIELD_NOT_SET );
	}
        /**//********************************************************************************
        * Wrapper to ->get
        *
	* @since 20200708
        * @param string field name
        * @returns mixed value of the field
        ************************************************************************************/
	function get_var( $var )
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		return $this->get( $var );
	}
	/**********************************
	 * php serialize calls __sleep/__serialize for cleanup prior to serialization
	 * php unserialize calls __wakeup/__unserialize for cleanup prior to serialization
	 * BINARY STRING!! Store in Blob in DB, not char/text.  CAN include NULL bytes
	 * @since 20200708
	 * *********************************
	function __sleep()
	{
	}
	funtion __serialize()
	{
	}
	function __unserialize()
	{
	}
	function __wakeup()
	{
	}
	 * *********************************/
	/**//***********************************************************************************************
        * Take a list of fields (->fields) and create an array (->data) of their values
        *
	 * @since 20200708
        * @param none uses ->fields
        * @return array ->data
	***************************************************************************************************/
	/*@array@*/function var2data()
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );
                if( ! is_array( $this->fields ) )
                {
                        throw new Exception( "Field we are dependant on (->fields) is not set", KSF_FIELD_NOT_SET );
                }
                if( ! isset( $this->data ) )
                {
                        $this->data = array();
                }
		foreach( $this->fields as $f )
		{
			$this->data[$f] = $this->get_var( $f );
		}
		return $this->data;
	}
	/**//***********************************************************************************************
        * Take a list of fields and create an indexed array (->data) of their values
        *
	 * @since 20200708
        * @param array fieldlist - list of fields to place into the array
        * @return array ->data
	***************************************************************************************************/
	/*@array@*/function fields2data( $fieldlist )
        {
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

                foreach( $fieldlist as $field )
                {
                        $this->data[$field] = $this->get_var( $field );
                }
                return $this->data;
	}
	
	/**//***********************************************************************************************
	* Log an ERROR (or greater) message 
	*
	* @since 20200708
	* @params string the message to log  
	* @param int Logging Level
	* @returns null 
	***************************************************************************************************/
	/*@NULL@*/function LogError( $message, $level = PEAR_LOG_ERR )
	{
return;
                if( ! isset( $this->errors ) )
                {
                        $this->errors = array();
                }
		if( $level <= $this->loglevel )
			$this->errors[] = $message;
		return;
	}
	/**//***********************************************************************************************
        * Append a message onto our internal ->log and/or ->errors array
	*
	* @since 20200708
	* @params string| the message to log  
	* @param int Logging Level
	* @returns null 
	***************************************************************************************************/
       /*@NULL@*/function Log( $message, $level = PEAR_LOG_EMERG )
        {
                switch( $level )
                {
                        case PEAR_LOG_EMERG:
                        case PEAR_LOG_ALERT:
                        case PEAR_LOG_CRIT:
                        case PEAR_LOG_ERR:
                                $this->LogError( $message, $level );
                        case PEAR_LOG_WARNING:
                        case PEAR_LOG_NOTICE:
                        case PEAR_LOG_INFO:
                        case PEAR_LOG_DEBUG:
                                $this->var_dump( $message, 0 );
                        default:
                                $this->LogMsg( $message, $level );
                                break;
                }
        }

	/**//***********************************************************************************************
	* Log a message 
	*
	* @since 20200708
	* @params string| the message to log  
	* @param int Logging Level
	* @returns null 
	***************************************************************************************************/
	/*@NULL@*/function LogMsg( $message, $level = PEAR_LOG_INFO )
	{
return;
                if( ! isset( $this->loglevel ) )
                {
                        $this->loglevel = array();
                }
		if( $level <= $this->loglevel )
			$this->log[] = $message;
		return;
	}
	/**//***********************************************************************************************
	* Log to the Screen (var_dump) 
	*
	* @params string|array the variable to dump
	* @param int Logging Level
	* @returns null 
	***************************************************************************************************/
        /*@NULL@*/function var_dump( $var, $level = PEAR_LOG_DEBUG )
        {
                if( $level <= $this->loglevel )
                        if( is_array( $var ) )
                        {
                                var_dump( get_class( $this ) );
                                var_dump(  $var );
                        }
                        else
                        {
                                var_dump( get_class( $this ) . "::" . $var );
                        }
                return;
        }
        /***************************************************************//**
        * Create a Name-Value pair as part of an array.  Can replace KEYS
        *
        ******************************************************************/
        /*@array@*/function objectvars2array()
        {
                $val = array();
                foreach( get_object_vars( $this ) as $key => $value )
                {
                        if( count( $this->dest_var_name_arr ) > 0 )
                        {
                                //No point trying to convert key names if we don't have destination names to convert to.
                                $key = str_replace( $this->obj_var_name_arr, $this->dest_var_name_arr, $key );
                        }
                        //if( "id" != $key )    //Not used for CREATE but needed for UPDATE.
                                if( isset( $this->$key ) )
                                        $val[] = array( "name" => $key, "value" => $this->$key );
                }
                $this->name_value_list = $val;
                return $val;
        }
	/******SPL EventLoop Funcs ********************************************/
	/****************//**
	*	Ensure we are attached to an eventloop object
	*
	* @param create_if_not_exist bool should we create the global if it doesn't exist
	* @return bool Are we attached
	********************/
	function attach_eventloop( $create_if_not_exist = true )
	{
		if( ! isset( $this->eventloop ) )
		{
			global $eventloop;
			//var_dump( __LINE__ ); var_dump( $GLOBALS['eventloop'] ); var_dump( $eventloop ); var_dump( $this->eventloop );
			if( isset( $eventloop ) )
			{
				if( is_object( $eventloop ) AND get_class( $eventloop ) == "eventloop" )
				{
					$this->eventloop = $eventloop;
					$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );
					return TRUE;
				}
				else
				{
					//Not a valid eventloop so replace
					return $this->create_eventloop();
				}
			}
			else
			{
				if( isset( $this->client ) )
				{
					if( isset( $this->client->eventloop ) )
					{
						//IF we got this far, there should be a global eventloop!!
						$this->eventloop = $this->client->eventloop;
						if( $create_if_not_exist )
							$eventloop = $this->eventloop;
						return TRUE;
					}
					else
					{
						if( $create_if_not_exist )
						{
							return $this->create_eventloop();
						}
						else
						{
							return FALSE;
						}

					}
				}
				else
				{
					//This should be the very 1st time through, and ONLY 1st.
					if( $create_if_not_exist )
					{
						return $this->create_eventloop( $this->moduledir );
					}
					else
					{
						return FALSE;
					}
				}
			}
		}
		else
		{
			//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );
			return TRUE;
		}
	}
	/******************************************************//**
	 * Create an eventloop if it doesn't exist globally and attach to it.
	 *
	 * We grab from the GLOBALS array if it exists.
	 * We add to the GLOBALS array at the end.
	 * This could probably have been accomplished using the global keyword.
	 *
	 * @param string directory name of where we are putting add-on modules.
	 * @return true
	 * *******************************************************/
	function create_eventloop( $moddir = null )
	{
		if( null == $moddir )
		{
			global $moduledir;
			//var_dump( __LINE__ ); var_dump( $moduledir ); var_dump( $GLOBALS['moduledir'] );
			$this->moduledir = $moduledir;
		}
		else
		{
			$this->moduledir = $moddir;
		}
		require_once( 'class.eventloop.php' );
		$eventloop = $GLOBALS['eventloop'];
		if( is_object( $eventloop ) AND get_class( $eventloop ) == "eventloop" )
		{
			//eventloop already exists so we don't want to do anything
			//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ . "::--EVENTLOOP already exists!" . "\n\r";
		}
		else
		{
			if( isset( $this->moduledir ) )
				$eventloop = new eventloop( $this->moduledir );
			else
				$eventloop = new eventloop();
		}
		$this->eventloop = $eventloop;
		$GLOBALS['eventloop'] = $eventloop;
		return TRUE;
	}
 	/************************************************************//**
         *
         *      tell.  Function to tell the using routine that we took
         *      an action.  That will let the client pass that data to
         *      any other plugin routines that are interested in that
         *      fact.
         *
         * @param msg what event message to pass
         * @param method Who triggered that event so that we don't pass back to them into an endless loop
         * **************************************************************/
        function tell( $msg, $method )
        {
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		if( ! isset( $msg ) )
			throw new Exception( "MSG to tell not set", KSF_VAR_NOT_SET );

                if( isset( $this->client ) )    //if not set nobody to tell
		{
                        if( is_callable( $this->client->eventloop( $msg, $method ) ) )
			{
				//20230112 This should probably fail.  eventloop should be an object and therefore we can't pass these variables to it.
				//  Can it be that I've replaced all uses of TELL and therefore we haven't hit an issue?  Really we should be using either
				//  ObserverNotify, or tell_eventloop below which uses ObserverNotify...
                                $this->client->eventloop( $msg, $method );
			}
		}
                else
                {
                        $this->tell_eventloop( $this, $msg, $method );
                }
        }
        function tell_eventloop( $caller, $event, $msg )
        {
		if( $this->attach_eventloop() )
                        $this->eventloop->ObserverNotify( $caller, $event, $msg );
        }
        /***************************************************************//**
         *dummy
         *
         *      Dummy function so that build_interestedin has something to
         *      put in as an example.
         *
         * @returns FALSE
         * ******************************************************************/
        function dummy( $obj, $msg )
        {
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ . "\n\r";
                $this->tell_eventloop( $this, NOTIFY_LOG_DEBUG, __METHOD__ . ":" . __LINE__ . " Entering " );
                $this->tell_eventloop( $this, NOTIFY_LOG_DEBUG, __METHOD__ . ":" . __LINE__ . " Exiting " );
                return FALSE;
        }
   	function register_with_eventloop()
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ . "\n\r";

		if( $this->attach_eventloop() )
                {
                        foreach( $this->interestedin as $key => $val )
                        {
				if( $key <> KSF_DUMMY_EVENT )
				{
					$this->eventloop->ObserverRegister( $this, $key );
					
				}
                        }
			$this->registered_interestedin = true;
                }
        }
	/********************************************************************************//**
	 * Register event observers to be notified
	 *
	 * @since 20230115
	 * Added so we can replace base.php
	 * 
	 * @param object the class.  SHOULD be US!!
	 * @param string event
	 * @param string function (callback) name
 	 * @return int KSF_ status code
	 ************************************************************************************/
       function ObserverRegister( $class, $event, $value )
       {
		$bRegisterIndependent = FALSE;

		if( $class !== $this )
		{
			$bRegisterIndependent = TRUE;
		}
		else 
		{
			if( array_key_exists( $event, $this->interestedin ) )
			{
				if( $this>registered_interestedin )
				{
					//register_with_eventloop will have register this for us
					return KSF_VALUE_SET_NO_REPLACE;
				}
				else
				{
					//Ideally we should never hit this case as register_with_eventloop should have already been called
					//so we should hit the else below (key not in array).
					$bRegisterIndependent = TRUE;
				}
			}
			else
			{
				//add this to interestedin
				if( is_callable( array( $this, $value ) ) )
				{
					$this->interestedin[$event] = $value;
				}
				$bRegisterIndependent = TRUE;
			}
		}
		if( $bRegisterIndependent )
		{
                	if( isset( $this->controller ) AND null != $this->controller )
                	{
                	        //var_dump( $this->controller );
				//Really class should be _this_ but I can't remember why i had base
				//accepting a class.  There is a possiblity I wanted to allow one class
				//to register on behalf of another.  Since I'm adding the event into
				//my interested in I should probably also register myself in that case...
                	        $this->controller->eventloop->ObserverRegister( $class, $event );
                	}
			return KSF_VALUE_SET;
		}
        }
     
        /***************************************************************//**
         *build_interestedin
         *
         *      DEMO function that needs to be overridden
         *      This function builds the table of events that we
         *      want to react to and what handlers we are passing the
         *      data to so we can react.
         * ******************************************************************/
        function build_interestedin()
        {
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );
                if( ! isset( $this->interestedin ) OR ! is_array( $this->interestedin ) )
                {
                        $this->interestedin = array();
		}
                //This NEEDS to be overridden
                $this->interestedin[KSF_DUMMY_EVENT]['function'] = "dummy";
                $this->interestedin["SETTINGS_APP_LOG_LEVEL"]['function'] = "app_log_level";
	//	throw new Exception( "You MUST override this function, even if it is empty!", KSF_FCN_NOT_OVERRIDDEN );
	}
	function app_log_level( $caller, $data )
	{
		$this->set( 'loglevel', $data );
	}
        /***************************************************************//**
         *notified
         *
         *      When we are notified that an event happened, check to see
         *      what we want to do about it
         *
         * @param $obj Object of who triggered the event
         * @param $event what event was triggered
         * @param $msg what message (data) was passed to us because of the event
         * ******************************************************************/
        function notified( $obj, $event, $msg )
        {
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

                if( isset( $this->interestedin[$event] ) )
                {
			$tocall = $this->interestedin[$event]['function'];
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", "Calling " . $tocall . " for event " . $event );
                        $this->$tocall( $obj, $msg );
                }
	}
	/*************************************************//**
	 *
	 * @since 20200712
	 * @TODO - write Unit Test
	 * @param Exception object
	 * @returns null
	 * **************************************************/
	function error_handler( Exception $e )
	{
		//$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG", get_class( $this ) . "::" . __METHOD__ );

		$code = $e->getCode();
		$msg = $e->getMessage();
		switch( $code )
		{
			default:
				$this->tell_eventloop( $this, "NOTIFY_LOG_ERROR", $msg );
		}
	}

}

/***************DYNAMIC create setter and getter**********************
// Create dynamic method. Here i'm generating getter and setter dynimically
// Beware: Method name are case sensitive.
foreach ($obj as $func_name => $value) {
    if (!$value instanceOf Closure) {

        $obj->{"set" . ucfirst($func_name)} = function($stdObject, $value) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
            $stdObject->{$func_name} = $value;
        };

        $obj->{"get" . ucfirst($func_name)} = function($stdObject) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
            return $stdObject->{$func_name};
        };

    }
}


*************************************************************************/ 

/***********************TESTING******************************
class origin_child extends origin
{
	var $only_in_child;
}
$test = new origin_child();
//var_dump( $test );
try {
	$test->set( 'only_in_child', true, true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child', true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child' );
} catch( Exception $e )
{
	var_dump( $e );
}
//var_dump( $test );
/************!TESTING**********************/

?>
