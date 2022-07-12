<?php

/**
 * Class for Templates Validation Algorithm Errors
 * 
 * @author zerozero.pt
 */
class ValidationErrorException extends Exception
{
    /**
     * @param string $path Place in the template JSON structure
     */
    public function __construct($message, $path, $file = "", $code = 0, Throwable $previous = null)
    {
        if ($file === "") {
            parent::__construct("<b>Error:</b> " . $path . " - " . $message, $code, $previous);
        }
        else {
            parent::__construct("[" . $file . "] -> " . $message, $code, $previous);
        }
    }
}

/**
 * Class for undefined method calling
 * 
 * @author zerozero.pt
 */
class UndefinedMethodException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Class for undefined entities
 * 
 * @author zerozero.pt
 */
class UndefinedEntityException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Undefined " . $message, $code, $previous);
    }
}

/**
 * Class for undefined language exception
 * 
 * @author zerozero.pt
 */
class UndefinedLanguageException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Error: " . $message, $code, $previous);
    }
}

/**
 * Class for errors in fetching data
 * 
 * @author zerozero.pt
 */
class DataFetcherException extends Exception
{
    /**
     * @param string $entity The attribute of the entity used to fetch the data
     */
    public function __construct($message, $entity, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Error: " . $message . " " . $entity . " does not exist.", $code, $previous);
    }
}
