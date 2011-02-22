<?php

class FactoryTest extends PHPUnit_Framework_TestCase {

    public function testGetFilesAsArrayWithoutSpecialParametersWorks() {
        $path = dirname(__FILE__)."/_files/twoEmptyPhpFiles"; 
        $files = File_Iterator_Factory::getFilesAsArray($path);
        $expected = array(
            $path."/foo.php",
            $path."/bar.php",
            $path,
            dirname(__FILE__)."/_files",

        );
        $this->assertEquals($expected, $files);
    }

}
