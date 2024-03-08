<?php
/**
 * Copyright 2024 Grip Online
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Grip;

class HtmlWhitespaceFilter
{

    private $buffer          = null;
    private $lastWrittenByte = null;
    private $currentTag      = null;
    private $noTrimTags = array('!', 'pre', 'textarea', 'script', 'style');

    /**
     * @param string $bytes
     */
    public function filter($bytes)
    {

        // create input to work with
        $input = ($this->buffer !== null) ? ($this->buffer . $bytes) : $bytes;

        // reset current buffer
        $this->buffer = null;

        // create output buffer
        $outputParts = array();

        // detect parts
        $parts = $this->detectParts($input);

        $numParts = count($parts);

        for ($i = 0; $i < $numParts; $i++) {

            $part = $parts[$i];

            if (($i == ($numParts - 1)) && ($part[0] == 'tag') && ($part[2] == null)) {

                // unfinished tag, set this part as new buffer and quit
                $newBuffer = substr($input, $part[1]);
                $this->buffer = $newBuffer;

                break;

            } else {

                if ($part[0] == 'tag') {

                    // tag part
                    if ($part[3] != '!') {
                        $this->currentTag = $part[3];
                    }

                    // output as-is
                    $outputParts[] = substr($input, $part[1], $part[2]);
                    $this->lastWrittenByte = '>';

                } else {

                    // text part

                    $textLen = ($part[2] !== null) ? $part[2] : (strlen($input) - $part[1]);

                    // check if current tag prohibits trimming

                    if (($this->currentTag !== null) && in_array($this->currentTag, $this->noTrimTags)) {

                        $outputPart = substr($input, $part[1], $textLen);

                        // output as-is
                        $outputParts[] = $outputPart;

                        if (strlen($outputPart) > 0) {

                            $this->lastWrittenByte = substr($outputPart, -1);

                        }

                    } else {

                        // trim text part

                        $lines = explode("\n", substr($input, $part[1], $textLen));

                        $trimmedLines = array();

                        foreach ($lines as $line) {

                            $trimmedLine = str_replace(array("\r", "\t"), array('', ''), $line);

                            while (strpos($trimmedLine, '  ') !== false) {
                                $trimmedLine = str_replace('  ', ' ', $trimmedLine);
                            }

                            $trimmedLines[] = $trimmedLine;

                        }

                        $anyLinePrinted = false;

                        foreach ($trimmedLines as $trimmedLine) {

                            if (($trimmedLine === '') || ($trimmedLine === ' ')) {

                                if ($this->lastWrittenByte === null) {

                                    // do not start with newline

                                } else if (($this->lastWrittenByte !== null) && ($this->lastWrittenByte == "\n")) {

                                    // do not add second newline after newline

                                } else if (($this->lastWrittenByte !== null) && ($this->lastWrittenByte == ">") && ($trimmedLine == ' ')) {

                                    // keep space after tag

                                    $outputParts[] = " ";
                                    $this->lastWrittenByte = " ";

                                } else {

                                    $outputParts[] = "\n";
                                    $this->lastWrittenByte = "\n";

                                }

                            } else {

                                if ($anyLinePrinted) {

                                    $outputParts[] = "\n";
                                    $this->lastWrittenByte = "\n";

                                }

                                if (($this->lastWrittenByte == "\n") && (substr($trimmedLine, 0, 1) == ' ')) {

                                    // do not start line with space after newline
                                    $outputParts[] = substr($trimmedLine, 1);

                                } else {

                                    $outputParts[] = $trimmedLine;

                                }

                                $this->lastWrittenByte = substr($trimmedLine, -1);

                                $anyLinePrinted = true;

                            }

                        }

                    }

                    $this->currentTag == null;

                }

            }

        }

        $res = implode('', $outputParts);
        return $res;

    }

    /**
     * Clears the buffer and outputs the buffer contents
     */
    public function endFlush()
    {

        $buffer = $this->flush();

        echo $buffer !== null ? $buffer : '';

    }

    /**
     * Clears the buffer and returns the buffer contents
     */
    public function flush()
    {

        $buffer = $this->buffer;

        $this->buffer = null;
        $this->lastWrittenByte = null;
        $this->currentTag = null;

        return $buffer;

    }

    /**
     * detect tags and text parts in the given input
     * @param string $input
     * @return array detected parts, each holding
     */
    public function detectParts($input)
    {

        $parts = array();

        $len = strlen($input);

        for ($i = 0; $i < $len; $i++) {

            $char = substr($input, $i, 1);

            if ($char == '<') {

                // tag part

                $partLen = null;

                $tagEnd = strpos($input, '>', $i);

                if ($tagEnd !== false) {

                    $partLen = $tagEnd - $i + 1;

                }

                $type = null;

                if ($tagEnd !== false) {

                    // determine tag name/type
                    if (substr($input, $i + 1, 1) == '!') {

                        $type = '!';

                    } else {

                        $name = '';

                        for ($j = $i + 1; $j < $tagEnd; $j++) {

                            $tagChar = strtolower(substr($input, $j, 1));

                            if (preg_match('/[a-z]/', $tagChar) || (($j == ($i + 1) && $tagChar == '/'))) {

                                $name .= $tagChar;

                            } else {
                                break;
                            }

                        }

                        if ($name != '') {

                            $type = $name;

                        }

                    }

                }

                $part = array('tag', $i, $partLen, $type);
                $parts[] = $part;

                if ($tagEnd !== false) {

                    $i = $tagEnd;

                } else {

                    break;

                }

            } else {

                // text part

                $partLen = null;

                $nextTagStart = strpos($input, '<', $i);

                if ($nextTagStart !== false) {

                    $partLen = $nextTagStart - $i;

                }

                $part = array('text', $i, $partLen);
                $parts[] = $part;

                if ($partLen !== null) {

                    $i = $nextTagStart - 1;

                } else {

                    break;

                }

            }

        }

        return $parts;

    }

}
