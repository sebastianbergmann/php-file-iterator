<?php
/*
 * This file is part of the File_Iterator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

spl_autoload_register(
  function ($class)
  {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
          $classes = array(
            'file_iterator' => '/Iterator.php',
            'file_iterator_facade' => '/Iterator/Facade.php',
            'file_iterator_factory' => '/Iterator/Factory.php'
          );

          $path = dirname(dirname(__FILE__));
      }

      $cn = strtolower($class);

      if (isset($classes[$cn])) {
          require $path . $classes[$cn];
      }
  }
);
