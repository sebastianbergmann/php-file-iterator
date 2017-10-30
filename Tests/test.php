<?php

function file_get_php_classes($filepath) {
    $php_code = file_get_contents($filepath);
    $classes = get_php_classes($php_code);
    return $classes;
}

function get_php_classes($php_code) {
    $classes = array();
    $tokens = token_get_all($php_code);
    $count = count($tokens);
    for ($i = 2; $i < $count; $i++) {
        if (   $tokens[$i - 2][0] == T_CLASS
            && $tokens[$i - 1][0] == T_WHITESPACE
            && $tokens[$i][0] == T_STRING) {

            $class_name = $tokens[$i][1];
            $classes[] = $class_name;
        }
    }
    return $classes;
}

$arrPath = [__DIR__.'/FileIteratorFacadeTest.php'];

foreach($arrPath as $path) {

    require $path;

    $arrClass = file_get_php_classes($path);

    foreach($arrClass as $class) {
        $o = new $class();
        $arrMethod = get_class_methods($o);
        foreach($arrMethod as $method) {
            call_user_func(array($o, $method));
        }
        echo "\n";
    }

    echo "Finish Test\n";
}
