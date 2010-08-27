<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @license		Apache License, Version 2.0
	 * @package		DevTools
	 *
	 * =============================================================================
	 */

    defined('TUXXEDO') or exit;


    /**
     * Minor testing class, used for testing availability for 
     * various requirement checks
     *
     * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
     * @version             1.0
     * @package             Engine
     * @subpackage          DevTools
     */
    class Tuxxedo_Test
    {
        /**
         * Option constant - test is required
         *
         * @var         integer
         */
        const OPT_REQUIRED      = 1;

        /**
         * Option constant - test is optional
         *
         * @var         integer
         */
        const OPT_OPTIONAL      = 2;

        /**
         * Option constant - tests a PHP extension
         *
         * @var         integer
         */
        const OPT_EXTENSION     = 4;

        /**
         * Option constant - tests versioning
         *
         * @var         integer
         */
        const OPT_VERSION       = 8;


        /**
         * Option bitfield
         *
         * @var         integer
         */
        protected $options      = 0;

        /**
         * Testing arguments
         *
         * @var         array
         */
        protected $argv         = Array();


        /**
         * Constructs a new test case
         *
         * @param       integer                 The options bitfield, using the OPT_* class constants
         * @param       array                   The arguments that needs to be tested against
         */
        public function __construct($options, Array $argv)
        {
            $this->options  = (integer) $options;
            $this->argv     = $argv;
        }

        /**
         * Check if a test is required
         *
         * @return      boolean                 Returns true if the test is required otherwise false
         */
        public function isRequired()
        {
            return(($this->options & self::OPT_REQUIRED) > 0);
        }

        /**
         * Check if a test is optional
         *
         * @return      boolean                 Returns true if the test is optional otherwise false
         */
        public function isOptional()
        {
            return(($this->options & self::OPT_OPTIONAL) > 0);
        }

        /**
         * Executes the test case
         *
         * @return      boolean                 Returns true if the test passed, otherwise false
         */
        public function test()
        {
            if($this->options & self::OPT_EXTENSION)
            {
                    return(extension_loaded($this->argv[0]));
            }
            elseif($this->options & self::OPT_VERSION)
            {
                    return(version_compare($this->argv[0], $this->argv[1], '>=') == 0);
            }

            return(false);
        }
    }
?>
