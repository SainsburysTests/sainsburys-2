<?php

class Sainsburys_Library_Function
{

    /**
     * @var Sainsburys_Library_Function
     */
    public static $_Instance;

    /**
     * @param string $function
     * @param array $arguments
     * @return mixed the function's output
     */
    public function __construct($function, $arguments)
    {
        if (!(function_exists($function)))
        {
			require_once '/home/apache/jia/sainsburys/SainsburysLibrary/'.$function . '.php';
        }
        return call_user_func_array($function, $arguments);
    }

    /**
     * @return Sainsburys_Library_Function
     */
    public static function run()
    {
        if (self::$_Instance === null)
        {
            $Instance = new self();
            self::$_Instance = $Instance;
        }
		return self::$_Instance;
    }

}
?>
