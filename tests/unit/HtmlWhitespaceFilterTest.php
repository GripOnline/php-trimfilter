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

use Grip\HtmlWhitespaceFilter;
use PHPUnit\Framework\TestCase;

class HtmlWhitespaceFilterTest extends TestCase
{

    public function testFilterOutput()
    {
        $trimFilter = new HtmlWhitespaceFilter();

        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\">\n  \n\t\t\n<body>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\">\n<body>some text</body></html>", $output);

        // leave text inside <pre> alone
        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><pre>\n  \n\t\t\n</pre>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\"><body><pre>\n  \n\t\t\n</pre>some text</body></html>", $output);

        // leave text inside <PRE> alone
        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><PRE>\n  \n\t\t\n</PRE>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\"><body><PRE>\n  \n\t\t\n</PRE>some text</body></html>", $output);

        // leave text inside comments alone

        $input = "<body><!--\n  \n\t\t\ncomment here-->some  text</body>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<body><!--\n  \n\t\t\ncomment here-->some text</body>", $output);

        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><!--\n  \n\t\t\ncomment here-->some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\"><body><!--\n  \n\t\t\ncomment here-->some text</body></html>", $output);

        // leave text inside <textarea> alone
        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><textareA>\n  \n\t\t\ntextarea\n\n</textArea>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\"><body><textareA>\n  \n\t\t\ntextarea\n\n</textArea>some text</body></html>", $output);

        // leave text inside <script> alone
        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><scripT language=\"text/javascript\">\n  \n\t\t\nsome script('<');\n\n</sCript>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals(
            "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><scripT language=\"text/javascript\">\n  \n\t\t\nsome script('<');\n\n</sCript>some text</body></html>",
            $output);

        // leave text inside <style> alone
        $input = "<!DOCTYPE HTML>\n<html lang=\"nl\"><body><Style type=\"text/css\">\n  \n\t\t\n#body  {'<'}\n\n</stYle>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\"><body><Style type=\"text/css\">\n  \n\t\t\n#body  {'<'}\n\n</stYle>some text</body></html>",
            $output);

    }

    public function testFilterOutputLeadingNewLines()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        // remove leading whitespace
        $input = "\n\n<!DOCTYPE HTML>\n<html lang=\"nl\">\n  \n\t\t\n<body>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\">\n<body>some text</body></html>", $output);

    }

    public function testFilterOutputLeadingNewLinesAndSpaces()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $input = "\n  \n <!DOCTYPE HTML>\n<html lang=\"nl\">\n  \n\t\t\n<body>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\">\n<body>some text</body></html>", $output);

    }

    public function testFilterOutputLeadingNewLinesBeforeText()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $input = "\n  \n <!DOCTYPE HTML>\n<html lang=\"nl\">\n  \n\t\t\ntext\ntext<body>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\">\ntext\ntext<body>some text</body></html>", $output);

    }

    public function testFilterOutputLeadingNewLinesAndSpacesBeforeText()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $input = "\n  \n <!DOCTYPE HTML>\n<html lang=\"nl\">\n  \n\t\t\n text\n text<body>some  text</body></html>";
        $output = $trimFilter->filter($input);
        $this->assertEquals("<!DOCTYPE HTML>\n<html lang=\"nl\">\ntext\ntext<body>some text</body></html>", $output);

    }

    public function testFilterOutput01()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $file = '01';

        $testFileDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testfiles' . DIRECTORY_SEPARATOR;

        $input = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-raw.html');
        $output = $trimFilter->filter($input);

        $expected = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-trimmed.html');
        $this->assertEquals($expected, $output);

    }

    public function testFilterOutput01Chunked100()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $file = '01';

        $testFileDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testfiles' . DIRECTORY_SEPARATOR;

        $input = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-raw.html');

        $chunks = array();

        while (strlen($input) > 0) {

            $firstChunk = substr($input, 0, 100);

            if (strlen($firstChunk) > 0) {

                $chunks[] = $firstChunk;
                $input = substr($input, strlen($firstChunk));

            }

        }

        $output = '';

        foreach ($chunks as $chunk) {

            $output .= $trimFilter->filter($chunk);

        }

        $expected = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-trimmed.html');
        $this->assertEquals($expected, $output);

    }

    public function testFilterOutput01Chunked256()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $file = '01';

        $testFileDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testfiles' . DIRECTORY_SEPARATOR;

        $input = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-raw.html');

        $chunks = array();

        while (strlen($input) > 0) {

            $firstChunk = substr($input, 0, 256);

            if (strlen($firstChunk) > 0) {

                $chunks[] = $firstChunk;
                $input = substr($input, strlen($firstChunk));

            }

        }

        $output = '';

        foreach ($chunks as $chunk) {

            $output .= $trimFilter->filter($chunk);

        }

        $expected = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-trimmed.html');
        $this->assertEquals($expected, $output);

    }

    public function testFilterOutputSpaceAfterTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter("Text <b>more</b> text");

        $this->assertEquals("Text <b>more</b> text", $output);

    }

    public function testFilterOutputSpaceAfterTagAtEndOfChunk()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter('<a href="#chapter-2438">Kennismaken<span class="shorten-on-mobile"> ');
        $output .= $trimFilter->filter("met Grip</span>?</a>");

        $this->assertEquals('<a href="#chapter-2438">Kennismaken<span class="shorten-on-mobile"> met Grip</span>?</a>', $output);

    }

    public function testFilterOutputSpaceAndNewlineAfterTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter("Text <b>more</b> \ntext");

        $this->assertEquals("Text <b>more</b> text", $output);

    }

    public function testFilterOutputNewlineAndSpaceAfterTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter("Text <b>more</b>\n text");

        $this->assertEquals("Text <b>more</b>\ntext", $output);

    }

    public function testFilterOutputNewlineAndTwoSpacesAfterTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter("Text <b>more</b>\n  text");

        $this->assertEquals("Text <b>more</b>\ntext", $output);

    }

    public function testFilterOutputTwoNewlinesAndTwoSpacesAfterTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $output = $trimFilter->filter("Text <b>more</b>\n\n  text");

        $this->assertEquals("Text <b>more</b>\ntext", $output);

    }

    public function testDetectPartsTextAndTagAndMoreText()
    {

        $trimFilter = new HtmlWhitespaceFilter();
        $parts = $trimFilter->detectParts('Text <a> more text');

        $this->assertCount(3, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(3, $parts[1][2]);
        $this->assertEquals('a', $parts[1][3]);

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(8, $parts[2][1]);
        $this->assertNull($parts[2][2]);

    }

    public function testDetectPartsTextAndUpperCaseTagAndMoreText()
    {

        $trimFilter = new HtmlWhitespaceFilter();
        $parts = $trimFilter->detectParts('Text <DIV> more text');

        $this->assertCount(3, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(5, $parts[1][2]);
        $this->assertEquals('div', $parts[1][3]);

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(10, $parts[2][1]);
        $this->assertNull($parts[2][2]);

    }

    public function testDetectPartsTextAndTagWithAttributesAndMoreText()
    {

        $trimFilter = new HtmlWhitespaceFilter();
        $input = 'Text <a href="foo"> more text';
        $parts = $trimFilter->detectParts($input);

        $this->assertCount(3, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);
        $this->assertEquals('Text ', substr($input, $parts[0][1], $parts[0][2]));

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(14, $parts[1][2]);
        $this->assertEquals('a', $parts[1][3]);
        $this->assertEquals('<a href="foo">', substr($input, $parts[1][1], $parts[1][2]));

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(19, $parts[2][1]);
        $this->assertNull($parts[2][2]);

    }

    public function testDetectPartsTextAndTagWithAttributesAndMoreTextAndUnclosedTag()
    {

        $trimFilter = new HtmlWhitespaceFilter();

        $input = 'Text <a href="foo"> more text<foo';
        $parts = $trimFilter->detectParts($input);

        $this->assertCount(4, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);
        $this->assertEquals('Text ', substr($input, $parts[0][1], $parts[0][2]));

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(14, $parts[1][2]);
        $this->assertEquals('a', $parts[1][3]);
        $this->assertEquals('<a href="foo">', substr($input, $parts[1][1], $parts[1][2]));

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(19, $parts[2][1]);
        $this->assertEquals(10, $parts[2][2]);
        $this->assertEquals(' more text', substr($input, $parts[2][1], $parts[2][2]));

        $this->assertEquals('tag', $parts[3][0]);
        $this->assertEquals(29, $parts[3][1]);
        $this->assertNull($parts[3][2]);

        $input = 'Text <a href="foo"> more text<foo attr="bar';
        $parts = $trimFilter->detectParts($input);

        $this->assertCount(4, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);
        $this->assertEquals('Text ', substr($input, $parts[0][1], $parts[0][2]));

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(14, $parts[1][2]);
        $this->assertEquals('a', $parts[1][3]);
        $this->assertEquals('<a href="foo">', substr($input, $parts[1][1], $parts[1][2]));

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(19, $parts[2][1]);
        $this->assertEquals(10, $parts[2][2]);
        $this->assertEquals(' more text', substr($input, $parts[2][1], $parts[2][2]));

        $this->assertEquals('tag', $parts[3][0]);
        $this->assertEquals(29, $parts[3][1]);
        $this->assertNull($parts[3][2]);

    }

    public function testDetectPartsTextAndCommentTagAndMoreText()
    {

        $trimFilter = new HtmlWhitespaceFilter();
        $input = 'Text <!-- comment --> more text';
        $parts = $trimFilter->detectParts($input);

        $this->assertCount(3, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);
        $this->assertEquals('Text ', substr($input, $parts[0][1], $parts[0][2]));

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(16, $parts[1][2]);
        $this->assertEquals('!', $parts[1][3]);
        $this->assertEquals('<!-- comment -->', substr($input, $parts[1][1], $parts[1][2]));

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(21, $parts[2][1]);
        $this->assertNull($parts[2][2]);

    }

    public function testDetectPartsTextAndTagAndMoreTextAndClosingTagAndMoreText()
    {

        $trimFilter = new HtmlWhitespaceFilter();
        $parts = $trimFilter->detectParts('Text <b>bold</b> text');

        $this->assertCount(5, $parts);

        $this->assertEquals('text', $parts[0][0]);
        $this->assertEquals(0, $parts[0][1]);
        $this->assertEquals(5, $parts[0][2]);

        $this->assertEquals('tag', $parts[1][0]);
        $this->assertEquals(5, $parts[1][1]);
        $this->assertEquals(3, $parts[1][2]);
        $this->assertEquals('b', $parts[1][3]);

        $this->assertEquals('text', $parts[2][0]);
        $this->assertEquals(8, $parts[2][1]);
        $this->assertEquals(4, $parts[2][2]);

        $this->assertEquals('tag', $parts[3][0]);
        $this->assertEquals(12, $parts[3][1]);
        $this->assertEquals(4, $parts[3][2]);
        $this->assertEquals('/b', $parts[3][3]);

        $this->assertEquals('text', $parts[4][0]);
        $this->assertEquals(16, $parts[4][1]);
        $this->assertNull($parts[4][2]);

    }

    public function testFilterTime() {

        $file = '01';

        $testFileDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testfiles' . DIRECTORY_SEPARATOR;

        $input = file_get_contents($testFileDir . 'html-trimfilter-' . $file . '-raw.html');

        $chunks = array();

        while (strlen($input) > 0) {

            $firstChunk = substr($input, 0, 100);

            if (strlen($firstChunk) > 0) {

                $chunks[] = $firstChunk;
                $input = substr($input, strlen($firstChunk));

            }

        }

        $this->assertCount(430, $chunks);

        $trimFilter = new HtmlWhitespaceFilter();

        $timeStart = microtime(true);

        foreach ($chunks as $chunk) {

            $trimFilter->filter($chunk);

        }

        $timeStop = microtime(true);

        $timeSpent = $timeStop - $timeStart;

        $this->assertTrue($timeSpent < .1, 'Time needed to trim ' . count($chunks) .' chunks must be less than 0.1s');

    }

}
