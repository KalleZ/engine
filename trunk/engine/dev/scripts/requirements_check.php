<?php
	class Test
	{
		const OPT_REQUIRED	= 1;
		const OPT_OPTIONAL	= 2;
		const OPT_EXTENSION	= 4;
		const OPT_VERSION	= 8;


		protected $options	= 0;
		protected $argv		= Array();


		public function __construct($options, Array $argv = Array())
		{
			$this->options 	= (integer) $options;
			$this->argv 	= $argv;
		}

		function isRequired()
		{
			return(($this->options & self::OPT_REQUIRED) > 0);
		}

		public function isOptional()
		{
			return(($this->options & self::OPT_OPTIONAL) > 0);
		}

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

	$tests = Array(
			'PHP Version'	=> new Test(Test::OPT_VERSION | Test::OPT_REQUIRED, Array('5.1.0', PHP_VERSION)), 
			'SPL'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('spl')), 
			'mysql'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('mysql')), 
			'mysqli'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('mysqli')), 
			'pdo'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('pdo')), 
			);

	echo('<h1>Requirements check</h1>');
	echo('<table border="1">');
	echo('<tr style="background-color: #D2D2D2;">');
	echo('<td><strong>Component</strong></td>');
	echo('<td><strong>Required?</strong></td>');
	echo('<td><strong>Availability</strong></td>');
	echo('</tr>');

	foreach($tests as $component => $test)
	{
		echo('<tr>');
		echo('<td>' . $component . '</td>');
		echo('<td>' . ($test->isRequired() ? 'Yes' : 'No') . '</td>');
		echo('<td style="background-color: ' . (($test = $test->test()) !== false ? 'green' : 'red') . ';">' . ($test ? 'Yes' : 'No') . '</td>');
		echo('</tr>');
	}

	echo('</table>');
?>