<?php

class File_Iterator_FactoryTest extends PHPUnit_Framework_TestCase {

    public function testGetFilesAsArrayWithoutAdditionalParametersWorks() {
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

    public function testGetFilesAsArrayWithoutAdditionParamatersWorksForHiddenFiles() {
        $path = dirname(__FILE__)."/_files/oneEmtpyPhpFileAndOneSwapFile";
        $files = File_Iterator_Factory::getFilesAsArray($path);
        $expected = array(
            $path."/foo.php",
            $path."/.foo.php.swp",
            $path,
            dirname(__FILE__)."/_files",

        );
        $this->assertEquals($expected, $files);
    }

    public function testGetFilesAsArrayWithPHPSuffixWorks() {
        $path = dirname(__FILE__)."/_files/twoEmptyPhpFiles"; 
        $files = File_Iterator_Factory::getFilesAsArray($path, array('php'));
        $expected = array(
            $path."/foo.php",
            $path."/bar.php",
        );
        $this->assertEquals($expected, $files);
    }

    public function testGetFilesAsArrayWithPHPSuffixWorksForFoldersWithHiddenFiles() {
        $path = dirname(__FILE__)."/_files/oneEmtpyPhpFileAndOneSwapFile"; 
        $files = File_Iterator_Factory::getFilesAsArray($path, array('php'));
        $expected = array(
            $path."/foo.php",
        );
        $this->assertEquals($expected, $files);
    }



}
