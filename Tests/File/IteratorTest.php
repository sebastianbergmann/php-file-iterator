<?php
/**
 * php-file-iterator
 *
 * Copyright (c) 2009-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   File
 * @author    Ben Selby <benmatselby@gmail.com>
 * @copyright 2009-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since     File available since Release 1.2.2
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . '/../../File/Iterator.php';

/**
 * @author    Ben Selby <benmatselby@gmail.com>
 * @copyright 2009-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/php-file-iterator/tree
 * @since     Class available since Release 1.2.2
 */
class File_IteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that the acceptPath function excludes the correct files
     *
     * @covers File_Iterator::acceptPath
     * @covers File_Iterator_Factory::getFilesAsArray
     *
     * @return void
     */
    public function testAcceptPathExclusionGetsApplied()
    {
        $files = File_Iterator_Factory::getFilesAsArray(
            array(dirname(__FILE__) . '/../_files/'),
            'php',
            '',
            array(dirname(__FILE__) . '/../_files/exclusion/disallowed')
        );

        $files = array_map('basename', $files);

        $this->assertContains('allowed.php', $files);
        $this->assertNotContains('disallowed.php', $files);
    }
}
