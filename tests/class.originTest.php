<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once( dirname( __FILE__ ) .  '/defines.php' );
require_once( dirname( __FILE__ ) .  '/../class.origin.php' );


/*
final class EmailTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            Email::class,
            Email::fromString('user@example.com')
        );
    }

    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Email::fromString('user@example.com')
        );
    }
}
*/

class originTest extends TestCase
{
 	protected $shared_var;
        protected $shared_val;
        protected $pref_tablename;
        function __construct()
        {
                parent::__construct();
                $this->shared_var =  'pub_unittestvar';
                $this->shared_val = '1';
                $this->pref_tablename = 'test';

        }
        public function testInstanceOf(): origin
        {
                $o = new origin( null, null, null );
                $this->assertInstanceOf( origin::class, $o );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorConfigValues( $o ): origin
        {
                $this->assertIsArray( $o->get( 'config_values' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorTabs( $o ): origin
        {
                $this->assertIsArray( $o->get( 'tabs' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorHelpContext( $o ): origin
        {
                $this->assertIsString( $o->get( 'help_context' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorTBPrefs( $o ): origin
        {
		//Set by fa_specific_init
                $this->assertIsString( $o->get( 'tb_prefs' ) );
		$this->assertSame( "0_", $o->get( "tb_prefs" ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorLogLevel( $o ): origin
        {
				//PEAR_LOG_DEBUG
                $this->assertSame( 7 , $o->get( 'loglevel' ) );
                return $o;
        }

        /**
         * @depends testInstanceOf
         */
        public function testConstructorErrors( $o ): origin
        {
                $this->assertIsArray( $o->get( 'errors' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorLog( $o ): origin
        {
                $this->assertIsArray( $o->get( 'log' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorFields( $o ): origin
        {
                $this->assertIsArray( $o->get( 'fields' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorData( $o ): origin
        {
                $this->assertIsArray( $o->get( 'data' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorTestvar( $o ): origin
        {
                $this->assertIsArray( $o->get( 'testvar' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorObjectFields( $o ): origin
        {
                $this->assertIsArray( $o->get( 'object_fields' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorApplication( $o ): origin
        {
                $this->assertIsString( $o->get( 'application' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorModule( $o ): origin
        {
                $this->assertIsString( $o->get( 'module' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorContainerArrray( $o ): origin
        {
                $this->assertIsArray( $o->get( 'container_array' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorEventloop( $o ): origin
        {
                //eventloop is a var, not protected/private
                $this->assertInstanceOf( eventloop::class, $o->eventloop );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorClient( $o ): origin
        {
		//Default is NULL
		//Do we get a NULL back, or an Exception?
                $this->assertIsNull( $o->get( 'client' ) );
                //$this->assertIsObject( $o->get( 'client' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorInterestedIn( $o ): origin
        {
                $this->assertIsArray( $o->get( 'interestedin' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorObjVarNameArr( $o ): origin
        {
                $this->assertIsArray( $o->get( 'obj_var_name_arr' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorDestVarNameArr( $o ): origin
        {
                $this->assertIsArray( $o->get( 'dest_var_name_arr' ) );
                return $o;
        }
        /**
         * @depends testInstanceOf
         */
        public function testConstructorNameValueList( $o ): origin
        {
                $this->assertIsArray( $o->get( 'name_value_list' ) );
                return $o;
        }

}
