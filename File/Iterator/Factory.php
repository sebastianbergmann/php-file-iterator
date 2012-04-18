<?php
/**
 * php-file-iterator
 *
 * Copyright (c) 2009-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since     File available since Release 1.1.0
 */

/**
 * Factory Method implementation that creates a File_Iterator that operates on
 * an AppendIterator that contains an RecursiveDirectoryIterator for each given
 * path.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/php-file-iterator/tree
 * @since     Class available since Release 1.1.0
 */
class File_Iterator_Factory
{
    /**
     * @param  array|string $paths
     * @param  array|string $suffixes
     * @param  array|string $prefixes
     * @param  array        $exclude
     * @return AppendIterator
     */
    public function getFileIterator($paths, $suffixes = '', $prefixes = '', array $exclude = array())
    {
        $paths = $this->expandAntPaths($paths);

        if (is_string($prefixes)) {
            if ($prefixes != '') {
                $prefixes = array($prefixes);
            } else {
                $prefixes = array();
            }
        }

        if (is_string($suffixes)) {
            if ($suffixes != '') {
                $suffixes = array($suffixes);
            } else {
                $suffixes = array();
            }
        }

        $iterator = new AppendIterator;
        foreach ($paths as $regex => $path) {
            if (!is_dir($path)) {
                continue;
            }

            $current_path_iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            );

            if (is_string($regex)) {
                $current_path_iterator = new RegexIterator($current_path_iterator, $regex);
            }

            $iterator->append(
                new File_Iterator(
                    $current_path_iterator,
                    $suffixes,
                    $prefixes,
                    $exclude,
                    $path
                )
            );
        }


        return $iterator;
    }

    protected function expandAntPath($path) {
        if (($star_position = strpos($path, '*')) === false) {
            return array('path' => $path);
        }

        $trimmed_path = substr($path, 0, $star_position - 1);
        $regex_path = str_replace('\\', '/', $path);
        // replace ** with stub first so single star replacements can be evaluated correctly
        $regex_path = str_replace('**', '__double_star__', $regex_path);
        // expand single star only within directory
        $regex_path = '=' . preg_replace('/\*[^*]/', '[^/]*', $regex_path) . '=';
        // expand double star to recursive directories
        $regex_path = str_replace('__double_star__', '.*', $regex_path);
        return array('path' => $trimmed_path, 'regex' => $regex_path);
    }

    protected function expandAntPaths($paths)
    {
        if (is_string($paths)) {
            $paths = array($paths);
        }

        $_paths = array();

        foreach ($paths as $path) {
            $result = $this->expandAntPath($path);
            if (isset($result['regex'])) {
                $_paths[$result['regex']] = $result['path'];
            } else {
                $_paths[] = $result['path'];
            }
        }

        return $_paths;
    }

}
