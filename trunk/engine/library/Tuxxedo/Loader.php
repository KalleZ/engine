<?php

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
            $match = implode($separator, $match);
        }
        
        self::$customPaths[$match] = array(
            "path" => realpath($path),
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
        foreach (array_keys(self::$customPaths) as $match) {
            if (substr($name, 0, strlen($match)) == $match) {
                // Use this path if we don't already have a match, the name is
                // the same as the match, or if this match is "stronger" than 
                // the current
                if (!$matched || $match == $name || strlen($match) < strlen($matched)) {
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
        
        require_once $path;
        
        // Check class/interface actually declared
        if (!class_exists($name) && !interface_exists($name)) {
            throw new Exception\Basic("Class/interface $name not declared in $path.");
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
        $unmatched = substr($name, strlen($match));
        // Compile the path
        $path = $matchInfo["path"] . str_replace($matchInfo["separator"], "/", $unmatched) . ".php";
        
        $fullPath = self::getFullPath($path);
        
        if (!$fullPath) {
            throw new Exception\Basic("Could not find $name (assumed to be in $path) in the include path.");
        }
        
        return $fullPath;
    }
    
    
    /**
     * Attempt to load a class by converting the name into a path, and then
     * checking in the PHP include path.
     */
    protected static function getPathDefault($name) {
        $path = str_replace(self::$separator, "/", $name) . ".php";
        
        $fullPath = self::getFullPath($path);
        
        if (!$fullPath) {
            throw new Exception\Basic("Could not find $name (assumed to be in $path) in the include path.");
        }
        
        return $fullPath;
    }
    
    /**
     * Check if the given path exists in the current include_path
     */
    protected static function getFullPath($path) {
        // Check if the path is already a full path
        if (file_exists($path)) {
            return $path;
        }
        
        $found = false;
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $incPath) {
            $fullPath = $incPath . "/" . $path;
            if (file_exists($fullPath)) {
                $found = realpath($fullPath);
            }
        }
        
        return $found;
    }
}
