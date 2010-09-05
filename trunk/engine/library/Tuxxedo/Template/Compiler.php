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
	 * @package		Engine
	 * @subpackage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Template namespace. This contains special routines for template handling 
	 * and such. It is also the home of the template compiler.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Template;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Template compiler, this compiles raw template source 
	 * code into php executable code with support for 
	 * expressions. It supports recursive expressions with 
	 * else support.
	 *
	 * This class is inspired by the vBulletin template 
	 * compilation model.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Compiler
	{
		/**
		 * The uncompiled raw source code
		 *
		 * @var		string
		 */
		protected $source;

		/**
		 * The compiled source code
		 *
		 * @var		string
		 */
		protected $compiled_source;

		/**
		 * The current number of parsed conditions, this is used for 
		 * making error messages more expressive so its easier to locate 
		 * an error
		 *
		 * @var		integer
		 */
		protected $conditions		= 0;

		/**
		 * The default functions to allow in expressions, note 
		 * that this also contains some predefined special 
		 * keywords for expressions, these must be lowercase if 
		 * manually added
		 *
		 * @var		array
		 */
		protected $functions		= Array(
							'and', 
							'or', 
							'xor', 

							'array', 
							'defined', 
							'empty', 
							'isset', 
							'sizeof', 
							'count'
							);

		/**
		 * The default class instances to allow in expressions
		 *
		 * @var		array
		 */
		protected $classes		= Array(
							'user', 
							'usergroup'
							);

		/**
		 * The default closures to allow in expressions
		 *
		 * @var		array
		 */
		protected $closures		= Array(
							);


		/**
		 * Template compiler constructor
		 *
		 * @param	integer			The current conditions, this is used for recursive code by the compile method and should not be touched
		 */
		public function __construct($conditions = NULL)
		{
			if($conditions !== NULL)
			{
				$this->conditions = $conditions;
			}
		}

		/**
		 * Allows a new function to be used in expressions
		 *
		 * @param	string			A function name to be allowed in expressions, notice that this have to be an existing function and not a method
		 * @return	boolean			Returns true if success, and false if the function doesn't exists or already is loaded
		 */
		public function allowFunction($function)
		{
			if(!\function_exists($function) || \in_array($function, $this->functions))
			{
				return(false);
			}

			$this->functions[] = $function;

			return(true);
		}

		/**
		 * Allows a new class instance to be used in expressions
		 *
		 * @param	string			A class instance name to be allowed in expressions, notice that this is the instance name of the variable, not the class itself
		 * @return	boolean			Returns true if success, and false if already is loaded
		 */
		public function allowClass($class)
		{
			if(\in_array($class, $this->classes))
			{
				return(false);
			}

			$this->classes[] = $class;

			return(true);
		}

		/**
		 * Allows a closure within expressions
		 *
		 * @param	string			The closure expression name (to allow $closure, supply 'closure')
		 * @return	boolean			Returns true if success, and false if already is loaded
		 */
		public function allowClosure($closure)
		{
			if(\in_array($closure, $this->closures))
			{
				return(false);
			}

			$this->closures[] = $closure;

			return(true);
		}

		/**
		 * Sets a new uncompiled source code
		 *
		 * @param	string			The new uncompiled source code
		 * @return	void			No value is returned
		 */
		public function set($source)
		{
			$this->source = (string) $source;
		}

		/**
		 * Gets the compiled template source
		 *
		 * @return	string			Returns the source code of the original template in compiled form and boolean false if template isn't compiled yet
		 */
		public function get()
		{
			if($this->compiled_source === NULL)
			{
				return(false);
			}

			return($this->compiled_source);
		}

		/**
		 * Compiles a template source
		 *
		 * @return	void			No value is returned
		 *
		 * @throws	Tuxxedo\Exception\TemplateCompiler
		 */
		public function compile()
		{
			static $tokens;

			$src = $this->source;

			if(empty($src))
			{
				$this->compiled_source = $src;

				return;
			}

			if(!$tokens)
			{
				$tokens = Array(
						'if_start'	=> '<if expression=', 
						'if_end'	=> '</if>', 
						'else'		=> '<else />'
						);
			}

			$ptr = Array(
					'if_open'		=> -1,
					'if_close'		=> -1, 
					'recursive_if'		=> -1, 
					'else'			=> -1, 
					'else_bytes'		=> -1
					);

			$src = \str_replace('"', '\\"', $src);

			while(1)
			{
				$ptr['if_open'] = \stripos($src, $tokens['if_start'], $ptr['if_close'] + 1);

				if($ptr['if_open'] === false)
				{
					break;
				}

				++$this->conditions;

				$expr_start 		= $ptr['if_open'] + \strlen($tokens['if_start']) + 1;
				$delimiter 		= $src{$expr_start - 1};
				$ptr['else_bytes']	= 2;

				if($delimiter == '\\')
				{
					$delimiter 		= $src{$expr_start};
					$ptr['else_bytes'] 	= 3;
					$expr_start		+= 1;
				}

				if($delimiter != '"' && $delimiter != '\'')
				{
					throw new Exception\TemplateCompiler('Invalid expression delimiter, must be either \' or "', $this->conditions);
				}

				$ptr['if_close'] = \stripos($src, $tokens['if_end'], $expr_start + 3);

				if($ptr['if_close'] === false)
				{
					throw new Excpetion\TemplateCompiler('No closing if found', $this->conditions);
				}

				$expr_end = -1;

				for($c = $expr_start, $bounds = \strlen($src); $c < $bounds; ++$c)
				{
					if($src{$c} == $delimiter && $src{$c - 2} != '\\' && $src{$c + 1} == '>')
					{
						$expr_end = ($delimiter == '"' ? $c - 1 : $c);

						break;
					}
				}

				if($expr_end == -1)
				{
					throw new Exception\TemplateCompiler('No end of expression found or malformed expression', $this->conditions);
				}

				$expr_value = \substr($src, $expr_start, $expr_end - $expr_start);

				if(empty($expr_value) || \is_numeric($expr_value) && $expr_value != 0)
				{
					throw new Exception\TemplateCompiler('Expressions may not be empty', $this->conditions);
				}
				elseif(\strpos($expr_value, '`') !== false)
				{
					throw new Exception\TemplateCompiler('Expressions may not contain backticks', $this->conditions);
				}
				elseif(\preg_match_all('#([a-z0-9_{}$>-]+)(\s|/\*.*\*/|(\#|//)[^\r\n]*(\r|\n))*\(#si', $expr_value, $matches))
				{
					foreach($matches[1] as $function)
					{
						$function = \strtolower(\stripslashes($function));

						if(\in_array($function, $this->functions) || $function{0} == '$' && ($pos = \strpos($function, '->')) !== false && \in_array(\substr($function, 1, $pos - 1), $this->classes) || $function{0} == '$' && \strpos($function, '->') === false && \in_array(substr($function, 1), $this->closures))
						{
							continue;
						}

						throw new Exception\TemplateCompiler('Use of unsafe function: ' . $function . '()', $this->conditions);
					}
				}

				$ptr['recursive_if'] = $ptr['if_open'];

				while(1)
				{
					$ptr['recursive_if'] = \stripos($src, $tokens['if_start'], $ptr['recursive_if'] + 1);

					if($ptr['recursive_if'] === false || $ptr['recursive_if'] >= $ptr['if_close'])
					{
						break;
					}

					$ptr['if_close'] = \stripos($src, $tokens['if_end'], $ptr['if_close'] + 1);

					if($ptr['if_close'] === false)
					{
						throw new Exception\TemplateCompiler('No closing if found', $this->conditions);
					}
				}

				$ptr['else'] = \stripos($src, $tokens['else'], $expr_end + $ptr['else_bytes']);

				while(1)
				{
					if($ptr['else'] === false || $ptr['else'] >= $ptr['if_close'])
					{
						$ptr['else'] = -1;

						break;
					}

					$body = \substr($src, $expr_end + $ptr['else_bytes'], $ptr['else'] - $expr_end + $ptr['else_bytes']);

					if(\substr_count($body, $tokens['if_start']) == \substr_count($body, $tokens['if_end']))
					{
						break;
					}

					$ptr['else'] = \stripos($src, $tokens['else'], $ptr['else'] + 1);
				}

				$true = $false = '';

				if($ptr['else'] == -1)
				{
					$true = \substr($src, $expr_end + $ptr['else_bytes'], $ptr['if_close'] - \strlen($tokens['if_end']) - $expr_end + 2);
				}
				else
				{
					$true 	= \substr($src, $expr_end + $ptr['else_bytes'], $ptr['else'] - $expr_end - $ptr['else_bytes']);
					$false	= \substr($src, $ptr['else'] + \strlen($tokens['else']), $ptr['if_close'] - \strlen($tokens['if_end']) - $ptr['else'] - $ptr['else_bytes']);
				}

				$template = new self($this->conditions);

				if(\stripos($true, $tokens['if_start']))
				{
					$template->set(\str_replace('\\"', '"', $true));
					$template->compile();

					$true = $template->get();
				}

				if(\stripos($false, $tokens['if_start']))
				{
					$template->set(\str_replace('\\"', '"', $false));
					$template->compile();

					$false = $template->get();
				}

				$template 		= NULL;
				$expression 		= '" . ((' . $expr_value . ') ? ("' . $true . '") : ' . ($false ? '("' . $false . '")' : '\'\'') . ') . "';
				$src 			= \substr_replace($src, $expression, $ptr['if_open'], $ptr['if_close'] + \strlen($tokens['if_end']) - $ptr['if_open']);
				$ptr['if_close'] 	= $ptr['if_open'] + \strlen($expression) - 1;
			}

			foreach(Array('\t', '\r', '\n', '\x', '\0', '\\\\', '\\\'', '\v') as $s)
			{
				$ptr = $pos = 0;

				while(($pos = \strpos($src, $s, $pos + $ptr)) !== false)
				{
					if($s == '\x' || $s == '\0')
					{
						if(!\is_numeric($src{$pos + 1}))
						{
							$src = \str_replace($s, '" . (\'' . $s . '\') . "', $src);
							$ptr += 14;
						}
						else
						{
							$x = 1;

							do
							{
								$s .= $src{$pos + $x};
								++$x;
							}
							while(isset($src{$pos + $x}) && \is_numeric($src{$pos + $x}));

							$src = \str_replace($s, '" . (\'' . $s . '\') . "', $src);
							$ptr += (12 + \strlen($s));
						}
					}
					else
					{
						$src = \str_replace($s, '" . (\'' . $s . '\') . "', $src);
						$ptr += 14;
					}
				}
			}

			$this->compiled_source 	= $src;
			$this->conditions	= 0;
		}

		/**
		 * Tests a compiled template for parse errors
		 *
		 * @return	boolean			Returns a boolean value depending on the test, NULL is returned if the source isn't compiled yet
		 */
		public function test()
		{
			if($this->compiled_source === NULL)
			{
				return;
			}

			$er = \error_reporting(\error_reporting() & ~E_NOTICE);

			if(\sizeof($this->classes) || \sizeof($this->closures))
			{
				$elements = \array_merge($this->classes, $this->closures);

				foreach($elements as $name)
				{
					if(!isset(${$name}))
					{
						${$name} = new Compiler\Dummy;
					}
				}

				unset($elements, $name);
			}

			\ob_start();
			eval('$test = "' . $this->compiled_source . '";');

			\error_reporting($er);

			return(\stripos(\ob_get_clean(), 'Parse error') === false);
		}
	}
?>