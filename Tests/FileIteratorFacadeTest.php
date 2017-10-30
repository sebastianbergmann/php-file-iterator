<?php

require __DIR__.'/../src/Facade.php';
require __DIR__.'/../src/Factory.php';
require __DIR__.'/../src/Iterator.php';

class FileIteratorFacadeTest {

    public function testWithSuffixDotPhp() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php');

        if($files == [
                $testPath.'/dir11/file11.php',
                $testPath.'/dir11/file12.php',
                $testPath.'/file1.php',
                $testPath.'/file2.php',
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }

    public function testWithPrefixIsFile() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php', 'file');

        if($files == [
                $testPath.'/dir11/file11.php',
                $testPath.'/dir11/file12.php',
                $testPath.'/file1.php',
                $testPath.'/file2.php',
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }

    public function testEmptyWithPrefixIsTmp() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php', 'tmp');

        if($files == [
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }

    public function testWithExcludeNotAPath() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php', 'file', ['file1']);

        if($files == [
                $testPath.'/dir11/file11.php',
                $testPath.'/dir11/file12.php',
                $testPath.'/file1.php',
                $testPath.'/file2.php',
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }

    public function testWithExcludeIsAPath() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php', 'file', [$testPath . '/dir11']);

        if($files == [
                $testPath.'/file1.php',
                $testPath.'/file2.php',
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }

    public function testWithCommonPath() {
        $facade =new File_Iterator_Facade();
        $testPath = __DIR__.'/dir';
        $files = $facade->getFilesAsArray($testPath, '.php', 'file', [$testPath . '/dir11'], true);

        if($files == [
                'commonPath' => $testPath.'/',
                'files' => [
                    $testPath.'/file1.php',
                    $testPath.'/file2.php',
                ]
            ]) {
            echo '.';
        }
        else {
            echo 'F';
        }
    }
}