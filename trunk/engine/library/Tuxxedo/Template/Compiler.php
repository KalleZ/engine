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
	use Tuxxedo\Template\Compiler\Dummy;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


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
		 * Compiler option - Disable function call check
		 *
		 * @var		integer
		 */
		const OPT_NO_FUNCTION_CALL_LIMIT	= 1;

		/**
		 * Compiler option - Disable class call check
		 *
		 * @var		integer
		 */
		const OPT_NO_CLASS_CALL_LIMIT		= 2;

		/**
		 * Compiler option - Disable closure call check
		 *
		 * @var		integer
		 */
		const OPT_NO_CLOSURE_CALL_LIMIT		= 4;

		/**
		 * Compiler option - Disable interpolated call protection
		 *
		 * @var		integer
		 */
		const OPT_NO_INTERPOLATED_CALLS		= 8;


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
		 * Error reporting buffer
		 *
		 * @var		integer
		 */
		protected $error_reporting;

		/**
		 * Compiler options bitmask
		 *
		 * @var		integer
		 */
		protected $options			= -1;

		/**
		 * The current number of parsed conditions, this is used for 
		 * making error messages more expressive so its easier to locate 
		 * an error
		 *
		 * @var		integer
		 */
		protected $conditions			= 0;

		/**
		 * The default functions to allow in expressions, note 
		 * that this also contains some predefined special 
		 * keywords for expressions, these must be lowercase if 
		 * manually added
		 *
		 * @var		array
		 */
		protected $functions			= Array(
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
		protected $classes			= Array(
								'user'		=> true, 
								'usergroup'	=> true
								);

		/**
		 * The default closures to allow in expressions
		 *
		 * @var		array
		 */
		protected $closures			= Array(
								);


		/**
		 * Template compiler constructor
		 *
		 * @param	integer			The compiler options, this is used for recursive code by the compiler, or by setting the default
		 * @param	integer			The current conditions, this is used for recursive code by the compile method and should not be touched
		 */
		public function __construct($options = -1, $conditions = NULL)
		{
			if($options !== -1)
			{
				$this->options = (integer) $options;
			}

			if($conditions !== NULL)
			{
				$this->conditions = $conditions;
			}
		}

		/**
		 * Set a new compiler option
		 *
		 * @param	integer			The new compiler bitmask
		 * @param	boolean			Whether to add it the bitmask to the current bitmask or reset it before
		 * @return	void			No value is returned
		 */
		public function setOptions($bitmask, $reset = false)
		{
			if($reset || $this->options === -1)
			{
				$this->options = (integer) $bitmask;
			}
			else
			{
				$this->options |= (integer) $bitmask;
			}
		}

		/**
		 * Gets the current compiler options
		 *
		 * @return	integer			The current compiler options
		 */
		public function getOptions()
		{
			return($this->options);
		}

		/**
		 * Allows a new function to be used in expressions
		 *
		 * @param	string			A function name to be allowed in expressions, notice that this have to be an existing function and not a method
		 * @return	boolean			Returns true if success, and false if the function doesn't exists or already is loaded
		 */
		public function allowFunction($function)
		{
			if(!\function_exists($function) || isset($this->functions[$function]))
			{
				return(false);
			}

			$this->functions[$function] = true;

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
			if(isset($this->classes[$class]))
			{
				return(false);
			}

			$this->classes[$class] = true;

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
			if(isset($this->closures[$closure]))
			{
				return(false);
			}

			$this->closures[$closure] = true;

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
		 * @throws	\Tuxxedo\Exception\TemplateCompiler
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

			if(\strpos($src, '"') !== false)
			{
				$src = \str_replace('"', '\\"', $src);
			}

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

				if($delimiter == '\\' && isset($src{$expr_start}))
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

				if(empty($expr_value) || ((string)(integer) $expr_value !== $expr_value) && $expr_value != 0)
				{
					throw new Exception\TemplateCompiler('Expressions may not be empty', $this->conditions);
				}
				elseif(\strpos($expr_value, '`') !== false)
				{
					throw new Exception\TemplateCompiler('Expressions may not contain backticks', $this->conditions);
				}
				elseif(\preg_match_all('#([a-z0-9_{}$>-]+)(?:\s|/\*.*\*/|(?:\#|//)[^\r\n]*(?:\r|\n))*\(#si', $expr_value, $matches))
				{
					foreach($matches[1] as $function)
					{
						$function = \strtolower(\stripslashes($function));

						if($this->options & self::OPT_NO_FUNCTION_CALL_LIMIT || isset($this->functions[$function]))
						{
							continue;
						}
						elseif($function{0} == '$')
						{
							if($this->options & self::OPT_NO_CLASS_CALL_LIMIT || ($pos = \strpos($function, '->')) !== false && isset($this->classes[\substr($function, 1, $pos - 1)]))
							{
								continue;
							}
							elseif($this->options & self::OPT_NO_CLOSURE_CALL_LIMIT || \strpos($function, '->') === false && isset($this->closures[\substr($function, 1)]))
							{
								continue;
							}
						}

						throw new Exception\TemplateCompiler('Use of unsafe call expression: ' . $function . '()', $this->conditions);
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

				if($ptr['else'] == -1)
				{
					$true 	= \substr($src, $expr_end + $ptr['else_bytes'], $ptr['if_close'] - \strlen($tokens['if_end']) - $expr_end + 2);
					$false 	= '';
				}
				else
				{
					$true 	= \substr($src, $expr_end + $ptr['else_bytes'], $ptr['else'] - $expr_end - $ptr['else_bytes']);
					$false	= \substr($src, $ptr['else'] + \strlen($tokens['else']), $ptr['if_close'] - \strlen($tokens['if_end']) - $ptr['else'] - $ptr['else_bytes']);
				}

				$compiler = new self($this->options, $this->conditions);

				if(\stripos($true, $tokens['if_start']) !== false)
				{
					if(\strpos($true, '\"') !== false)
					{
						$true = \str_replace('\"', '"', $true);
					}

					$compiler->set($true);
					$compiler->compile();

					$true = $compiler->get();
				}

				if(\stripos($false, $tokens['if_start']) !== false)
				{
					if(\strpos($false, '\"') !== false)
					{
						$false = \str_replace('\"', '"', $false);
					}

					$compiler->set($false);
					$compiler->compile();

					$false = $compiler->get();
				}

				$compiler 		= NULL;
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
						if((string)(integer) $src{$pos + 1} === $src{$pos + 1})
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
							while(isset($src{$pos + $x}) && (string)(integer) $src{$pos + $x} !== $src{$pos + $x});

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

			if($this->options & self::OPT_NO_INTERPOLATED_CALLS && strpos($src, '{${') !== false)
			{
				throw new Exception\TemplateCompiler('Interpolated function calls are not allowed');
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

			$this->error_reporting = \error_reporting(\error_reporting() & ~E_NOTICE);

			if($this->classes || $this->closures)
			{
				foreach(\array_merge(\array_keys($this->classes), \array_keys($this->closures)) as $name)
				{
					if(!isset(${$name}))
					{
						${$name} = new Dummy;
					}
				}

				unset($name);
			}

			return(@eval('$test = "' . $this->compiled_source . '"; \error_reporting($this->error_reporting); return(true);') === true);
		}
	}
?>