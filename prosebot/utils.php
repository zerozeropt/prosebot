<?php
require_once(__DIR__.'/exceptions.php');

/**
 * Class of auxiliary functions transversal to all contexts
 * 
 * @author zerozero.pt
 */
class Utils
{
	/**
	 * Get string or null if empty
	 * @param string $string Generic string
	 * @return null|string The string passed as argument if not empty, null otherwise
     */
	static function null_if_empty($string)
	{
		if ($string == '') {
			return null;
		}
		return $string;
	}

	/**
	 * Get the first element of an array that fulfills the filter function
	 * @param array    $array  Generic array
	 * @param function $filter Generic filter function
	 * @param int      $start  Starting index (inclusive)
	 * @return key|boolean The key if element found, false otherwise
     */
	static function find($array, $filter, $start = 0)
	{
		$sliced = array_slice($array, $start);
		foreach ($sliced as $key => $element) {
			if ($filter($element)) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Throws exception if an error is caught
	 * @param int    $severity  The severity level of the exception
	 * @param string $message   The Exception message to throw
	 * @param string $filename  The filename where the exception is thrown
	 * @param string $lineno    The line number where the exception is thrown
	 * @return void|ErrorException Void if no error, otherwise throws exception
     */
	static function exceptions_error_handler($severity, $message, $filename, $lineno)
	{
		if (error_reporting() == 0) {
			return;
		}
		if (error_reporting() & $severity) {
			throw new ErrorException($message, 0, $severity, $filename, $lineno);
		}
	}

	/**
	 * Throws exception if an undefined constant error is caught
	 * @param int    $err_no   	The error number
	 * @param string $err_str   The error message
	 * @return void|ErrorException Void if no error, otherwise throws exception
     */
	static function undefined_constant_handler($err_no, $err_str)
	{
		if (strpos($err_str, 'Use of undefined constant ') === 0) {
			throw new ErrorException($err_str);
		}
	}

	/**
	 * Get random number between 0 and 1
	 * @return float Random number between 0 (inclusive) and 1 (exclusive)
     */
	static function get_rand()
	{
		return random_int(0, mt_getrandmax() - 1) / mt_getrandmax();
	}

	/**
	 * Validate json syntax
	 * @param string $string Json file in string format
	 * @return json Encoded json if no error, otherwise throws exception
     */
	static function json_validate($string)
	{
		// decode the JSON data
		$result = json_decode($string);

		// switch and check possible JSON errors
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid // No error has occurred
				break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
				// PHP >= 5.3.3
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
				// PHP >= 5.5.0
			case JSON_ERROR_RECURSION:
				$error = 'One or more recursive references in the value to be encoded.';
				break;
				// PHP >= 5.5.0
			case JSON_ERROR_INF_OR_NAN:
				$error = 'One or more NAN or INF values in the value to be encoded.';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = 'A value of a type that cannot be encoded was given.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}

		if ($error !== '') {
			// throw the Exception
			throw new ValidationErrorException($error, "/");
		}

		// everything is OK
		return $result;
	}

	/**
	 * Clamps value according to a range of values
	 * @param int $number The number to be clamped
	 * @param int $min    The minimum value
	 * @param int $max    The maximum value
	 * @return int Returns the $number if it is between $min and $max; returns $min if $number < $min or returns $max if $number > $max
     */
	static function clamp($number, $min, $max)
	{
		return max($min, min($max, $number));
	}

	/**
	 * Converts a boolean value to a string "1" or "0"
	 * @param boolean $bool Boolean value (true or false)
	 * @return string "1" if $bool is true, "0" otherwise
     */
	static function boolstr($bool)
	{
		return $bool ? "1" : "0";
	}

	/**
	 * Prints text
	 * @param string $text Text to be printed
     */
	static function printP($text)
	{
		echo $text;
	}

	/**
	 * Prints a list
	 * @param array  $list  List to be printed
	 * @param string $title Title of the list
     */
	static function printList($list, $title)
	{
		echo '<h4>' . $title . '</h4>';
		if (count($list) > 0) {
			echo '<div>';
			echo '<li>' . implode('</li><li>', $list) . '</li>';
			echo '</ul>';
		}
	}
}
