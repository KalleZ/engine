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
	 *
	 * =============================================================================
	 */

namespace Tuxxedo;
use Tuxxedo\Exception;

/**
 * The class autoloader
 */
class Loader
{
    /**
     * Class separator - to use non-namespaced classes, named like 
     * Tuxxedo_Loader change this to "_".
     */
    public static $separator = "\\";

    /**
     * An array of custom paths to use if a match is found when loading a class
     * Format: match => {"path" => path, "separator" => name_separator}
     */
    protected static $customPaths = array();

    /**
     * Add a path to the custom path matching
     */
    public static function addPath($match, $path, $separator = null) {
        $separator = is_null($separator) ? self::$separator : $separator;
        
        if (is_array($match)) {
            $match = \implode($separator, $match);
        }
        
        self::$customPaths[$match] = array(
            "path" => $path,
            "separator" => $separator
        );
    }

    /**
     * Load a class
     * @param   string  Name of the class/interface to load
     * @throws  Tuxxedo_Loader_Exception
     */
    public static function load($name) {
        /**
         * Check for a match in the custom paths array
         * Essentially, if the class/interface starts with the match text, then
         * the loader will use the path to find it (as far as the matched part 
         * of the name, if there are other parts then the default loader will
         * kick in for those parts). If a second match is found and it is
         * longer than the previous match it will replace the previous match.
         * If a match is found that exactly matches the name then that is used.
         */
        $matched = false;
        foreach (\array_keys(self::$customPaths) as $match) {
            if (\substr($name, 0, \strlen($match)) == $match) {
                // Use this path if we don't already have a match, the name is
                // the same as the match, or if this match is "stronger" than 
                // the current
                if (!$matched || $match == $name || \strlen($match) < \strlen($matched)) {
                    $matched = $match;
                    
                    // If the match is exactly the same as the name, stop 
                    // looking and use this match
                    if ($match == $name) {
                        break;
                    }
                }
            }
        }

        // Get the path for the class
        if ($matched) {
            $path = self::getPathMatched($name, $matched, self::$customPaths[$matched]);
        } else {
            $path = self::getPathDefault($name);
        }

	/* @TODO this is seriously bad */
	if (!\is_file($path)) { \tuxxedo_doc_errorf('Unable to locate class or interface file, for \'%s\'', $name); } else { require $path; }
        
        // Check class/interface actually declared
        if (!\class_exists($name) && !\interface_exists($name)) {
		\tuxxedo_doc_errorf('Class or interface \'%s\' must be declared in \'%s\'', $name, \tuxxedo_trim_path(realpath($path)));
        }
    }
    
    /**
     * Get the final full path of a matched class
     */
    protected static function getPathMatched($name, $match, $matchInfo) {
        // If the match is the same as the name use the path
        if ($name == $match) {
            return $matchPath;
        }
        
        // Get the unmatched part of the class name
        $unmatched = \substr($name, strlen($match));
        // Compile the path
        $path = $matchInfo["path"] . \str_replace($matchInfo["separator"], "/", $unmatched) . ".php";
        
        $fullPath = self::getFullPath($path);
    
        return $fullPath;
    }
    
    
    /**
     * Attempt to load a class by converting the name into a path, and then
     * checking in the PHP include path.
     */
    protected static function getPathDefault($name) {
	$name = TUXXEDO_LIBRARY . '/' . $name . '.php';

        $fullPath = self::getFullPath($name);
        
        if ($fullPath === false) {
		\tuxxedo_doc_errorf('Unable to resolve \'%s\'', $name);
        }
        
        return $fullPath;
    }
    
    /**
     * Check if the given path exists in the current include_path
     */
    protected static function getFullPath($path) {
        // Check if the path is already a full path
        if (is_file($path)) {
            return $path;
        }
        
        return false;
    }
}
?>