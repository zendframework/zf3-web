---
layout: issue
title: "unit test failure for Zend_Mime; OS/env dependent?  TRAC-145"
id: ZF-9
---

ZF-9: unit test failure for Zend\_Mime; OS/env dependent? TRAC-145
------------------------------------------------------------------

 Issue Type: Bug Created: 2006-06-16T14:55:48.000+0000 Last Updated: 2007-07-05T14:44:26.000+0000 Status: Closed Fix version(s): - 0.1.4 (29/Jun/06)
 
 Reporter:  Richard (openmacnews)  Assignee:  Matthew Weier O'Phinney (matthew)  Tags: - Zend\_Mime
 
 Related issues: 
 Attachments: 
### Description

hi,

per comments from ZF list from Matthew Weier O'Phinney:

=========================== Please open a bug for this issue, and, when doing so, please indicate what platform you're running on (Windows, Mac, Linux, etc.), what version of PHP you're using, and which version of PHPUnit you used.

I'm currently unable to recreate the failure -- all tests run on my machine, even under a fresh checkout -- using Debian GNU/Linux, PHP 5.1.2, and PHPUnit 2.3.5. A colleague reports the test failing under Cygwin + Windows using PHP 5.1.4 and PHPUnit 3.0.0alpha5. This information leads me to believe the failure may be related to OS, PHP version, or PHPUnit version.

I'll get someone with specs closer to those you report to delve into it further

so we can close the bug.
========================

on OSX 10.4.6 ...

% zend\_framework > % zend\_framework > svn info | grep Revision Revision: 668 % zend\_framework > pear list | grep -i phpunit2 PHPUnit2 3.0.0alpha11 alpha % zend\_framework > php -i PHP Version => 5.2.0-dev System => Darwin devuser 8.6.0 Darwin Kernel Version 8.6.0: Tue Mar 7 16:58:48 PST 2006; root:xnu-792.6.70.obj~1/RELEASE\_PPC Power Macintosh

 
    ./configure \
    ...
    --with-apxs2=/usr/local/apache2/sbin/apxs \
    --enable-shared --disable-static \
    --disable-debug \
    --disable-safe-mode \
    --disable-dmalloc \
    --enable-inline-optimization \
    --enable-session \
    --with-tsrm-pthreads \
    --enable-maintainer-zts \
    ...


% httpd -V

 
    Server version: Apache/2.2.3-dev Server built: Jun 8 2006 17:00:58 Server's Module Magic Number: 20051115:2 Server loaded: APR 1.2.8-dev, APR-Util 1.2.8-dev Compiled using: APR 1.2.8-dev, APR-Util 1.2.8-dev Architecture: 32-bit Server MPM: Worker
    
        threaded: yes (fixed thread count)
        forked: yes (variable process count)


% zend\_framework > cd tests % zend\_framework/tests > php Zend/Mime/AllTests.php

Time: 00:00 There was 1 failure: 1) testStreamEncoding(Zend\_Mime\_PartTest) failed asserting that <'<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once \\'Zend/Mime/Part.php\\';

/\*\* \* PHPUnit2 test case \*/ require\_once \\'PHPUnit2/Framework/TestCase.php\\';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                       . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = \'test.txt\';
        $this->part->disposition = \'attachment\';
        $this->part->charset = \'iso8859-1\';
        $this->part->id = \'4711\';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array(\'Content-Type: text/plain\',
                                 \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                 \'Content-Disposition: attachment\',
                                 \'filename="test.txt"\',
                                 \'charset="iso8859-1"\',
                                 \'Content-ID: <4711>\');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$original);
    }


} '> is equal to <string:'<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once \\'Zend/Mime/Part.php\\';

/\*\* \* PHPUnit2 test case \*/ require\_once \\'PHPUnit2/Framework/TestCase.php\\';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                       . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = \'test.txt\';
        $this->part->disposition = \'attachment\';
        $this->part->charset = \'iso8859-1\';
        $this->part->id = \'4711\';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array(\'Content-Type: text/plain\',
                                 \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                 \'Content-Disposition: attachment\',
                                 \'filename="test.txt"\',
                                 \'charset="iso8859-1"\',
                                 \'Content-ID: <4711>\');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$o'>


expected string <<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ /\*\* \* Zend\_Mime\_Part \*/ require\_once 'Zend/Mime/Part.php'; /\*\* \* PHPUnit2 test case \*/ require\_once 'PHPUnit2/Framework/TestCase.php'; /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText; protected function setUp() { $this->\_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj ' . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg'; $this->part = new Zend\_Mime\_Part($this->\_testText); $this->part->encoding = Zend\_Mime::ENCODING\_BASE64; $this->part->type = "text/plain"; $this->part->filename = 'test.txt'; $this->part->disposition = 'attachment'; $this->part->charset = 'iso8859-1'; $this->part->id = '4711'; } public function testHeaders() { $expectedHeaders = array('Content-Type: text/plain', 'Content-Transfer-Encoding: ' . Zend\_Mime::ENCODING\_BASE64, 'Content-Disposition: attachment', 'filename="test.txt"', 'charset="iso8859-1"', 'Content-ID: <4711>'); $actual = $this->part->getHeaders(); foreach ($expectedHeaders as $expected) { $this->assertContains($expected, $actual); } } public function testContentEncoding() { // Test with base64 encoding $content = $this->part->getContent(); $this->assertEquals($this->\_testText, base64\_decode($content)); // Test with quotedPrintable Encoding: $this->part->encoding = Zend\_Mime::ENCODING\_QUOTEDPRINTABLE; $content = $this->part->getContent(); $this->assertEquals($this->\_testText, quoted\_printable\_decode($content)); // Test with 8Bit encoding $this->part->encoding = Zend\_Mime::ENCODING\_8BIT; $content = $this->part->getContent(); $this->assertEquals($this->\_testText, $content); } public function testStreamEncoding() { $testfile = realpath(\_\_FILE\_\_); $original = file\_get\_contents($testfile); // Test Base64 $fp = fopen($testfile,'rb'); $this->assertTrue(is\_resource($fp)); $part = new Zend\_Mime\_Part($fp); $part->encoding = Zend\_Mime::ENCODING\_BASE64; $fp2 = $part->getEncodedStream(); $this->assertTrue(is\_resource($fp2)); $encoded = stream\_get\_contents($fp2); fclose($fp); $this->assertEquals(base64\_decode($encoded),$original); // test QuotedPrintable $fp = fopen($testfile,'rb'); $this->assertTrue(is\_resource($fp)); $part = new Zend\_Mime\_Part($fp); $part->encoding = Zend\_Mime::ENCODING\_QUOTEDPRINTABLE; $fp2 = $part->getEncodedStream(); $this->assertTrue(is\_resource($fp2)); $encoded = stream\_get\_contents($fp2); fclose($fp); $this->assertEquals(quoted\_printable\_decode($encoded),$o> difference < ??????????????????> got string <<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once 'Zend/Mime/Part.php';

/\*\* \* PHPUnit2 test case \*/ require\_once 'PHPUnit2/Framework/TestCase.php';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj '
                       . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = 'test.txt';
        $this->part->disposition = 'attachment';
        $this->part->charset = 'iso8859-1';
        $this->part->id = '4711';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array('Content-Type: text/plain',
                                 'Content-Transfer-Encoding: ' . Zend_Mime::ENCODING_BASE64,
                                 'Content-Disposition: attachment',
                                 'filename="test.txt"',
                                 'charset="iso8859-1"',
                                 'Content-ID: <4711>');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,'rb');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,'rb');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$original);
    }


} > /webapps/tools/zend\_framework/tests/Zend/Mime/PartTest.php:90 /webapps/tools/zend\_framework/tests/Zend/Mime/AllTests.php:16 /webapps/tools/zend\_framework/tests/Zend/Mime/AllTests.php:31

FAILURES! Tests: 8, Failures: 1.

 

 

### Comments

Posted by Richard (openmacnews) on 2006-06-17T16:30:17.000+0000

 
    <pre class="literal"> 
    w/ ZF svn r650
    php 52-dev
    OSX 10.4.6
    upgrade to PHPUnit 300a11
    
    
    % php Zend/Mime/AllTests.php 
    PHPUnit 3.0.0alpha11 by Sebastian Bergmann.
    
    ..F.....
    
    Time: 00:00
    There was 1 failure:
    1) testStreamEncoding(Zend_Mime_PartTest)
    failed asserting that <'<?php
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    
    
    /**
     * Zend_Mime_Part
     */
    require_once \'Zend/Mime/Part.php\';
    
    /**
     * PHPUnit2 test case
     */
    require_once \'PHPUnit2/Framework/TestCase.php\';
    
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    class Zend_Mime_PartTest extends PHPUnit2_Framework_TestCase
    {
        /**
         * MIME part test object
         *
         * @var Zend_Mime_Part
         */
        protected $_part = null;
        protected $_testText;
    
        protected function setUp()
        {
            $this->_testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                           . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
            $this->part = new Zend_Mime_Part($this->_testText);
            $this->part->encoding = Zend_Mime::ENCODING_BASE64;
            $this->part->type = "text/plain";
            $this->part->filename = \'test.txt\';
            $this->part->disposition = \'attachment\';
            $this->part->charset = \'iso8859-1\';
            $this->part->id = \'4711\';
        }
    
        public function testHeaders()
        {
            $expectedHeaders = array(\'Content-Type: text/plain\',
                                     \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                     \'Content-Disposition: attachment\',
                                     \'filename="test.txt"\',
                                     \'charset="iso8859-1"\',
                                     \'Content-ID: <4711>\');
    
            $actual = $this->part->getHeaders();
    
            foreach ($expectedHeaders as $expected) {
                $this->assertContains($expected, $actual);
            }
        }
    
        public function testContentEncoding()
        {
            // Test with base64 encoding
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, base64_decode($content));
            // Test with quotedPrintable Encoding:
            $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, quoted_printable_decode($content));
            // Test with 8Bit encoding
            $this->part->encoding = Zend_Mime::ENCODING_8BIT;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, $content);
        }
        
        public function testStreamEncoding()
        {
            $testfile = realpath(__FILE__);
            $original = file_get_contents($testfile);
    
            // Test Base64
            $fp = fopen($testfile,\'rb\');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_BASE64;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(base64_decode($encoded),$original);
            
            // test QuotedPrintable
            $fp = fopen($testfile,\'rb\');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(quoted_printable_decode($encoded),$original);
        }
    }
    '> is equal to _testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                           . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
            $this->part = new Zend_Mime_Part($this->_testText);
            $this->part->encoding = Zend_Mime::ENCODING_BASE64;
            $this->part->type = "text/plain";
            $this->part->filename = \'test.txt\';
            $this->part->disposition = \'attachment\';
            $this->part->charset = \'iso8859-1\';
            $this->part->id = \'4711\';
        }
    
        public function testHeaders()
        {
            $expectedHeaders = array(\'Content-Type: text/plain\',
                                     \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                     \'Content-Disposition: attachment\',
                                     \'filename="test.txt"\',
                                     \'charset="iso8859-1"\',
                                     \'Content-ID: <4711>\');
    
            $actual = $this->part->getHeaders();
    
            foreach ($expectedHeaders as $expected) {
                $this->assertContains($expected, $actual);
            }
        }
    
        public function testContentEncoding()
        {
            // Test with base64 encoding
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, base64_decode($content));
            // Test with quotedPrintable Encoding:
            $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, quoted_printable_decode($content));
            // Test with 8Bit encoding
            $this->part->encoding = Zend_Mime::ENCODING_8BIT;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, $content);
        }
        
        public function testStreamEncoding()
        {
            $testfile = realpath(__FILE__);
            $original = file_get_contents($testfile);
    
            // Test Base64
            $fp = fopen($testfile,\'rb\');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_BASE64;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(base64_decode($encoded),$original);
            
            // test QuotedPrintable
            $fp = fopen($testfile,\'rb\');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(quoted_printable_decode($encoded),$o'>
    expected string <<?php
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    
    
    /**
     * Zend_Mime_Part
     */
    require_once 'Zend/Mime/Part.php';
    
    /**
     * PHPUnit2 test case
     */
    require_once 'PHPUnit2/Framework/TestCase.php';
    
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    class Zend_Mime_PartTest extends PHPUnit2_Framework_TestCase
    {
        /**
         * MIME part test object
         *
         * @var Zend_Mime_Part
         */
        protected $_part = null;
        protected $_testText;
    
        protected function setUp()
        {
            $this->_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj '
                           . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg';
            $this->part = new Zend_Mime_Part($this->_testText);
            $this->part->encoding = Zend_Mime::ENCODING_BASE64;
            $this->part->type = "text/plain";
            $this->part->filename = 'test.txt';
            $this->part->disposition = 'attachment';
            $this->part->charset = 'iso8859-1';
            $this->part->id = '4711';
        }
    
        public function testHeaders()
        {
            $expectedHeaders = array('Content-Type: text/plain',
                                     'Content-Transfer-Encoding: ' . Zend_Mime::ENCODING_BASE64,
                                     'Content-Disposition: attachment',
                                     'filename="test.txt"',
                                     'charset="iso8859-1"',
                                     'Content-ID: <4711>');
    
            $actual = $this->part->getHeaders();
    
            foreach ($expectedHeaders as $expected) {
                $this->assertContains($expected, $actual);
            }
        }
    
        public function testContentEncoding()
        {
            // Test with base64 encoding
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, base64_decode($content));
            // Test with quotedPrintable Encoding:
            $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, quoted_printable_decode($content));
            // Test with 8Bit encoding
            $this->part->encoding = Zend_Mime::ENCODING_8BIT;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, $content);
        }
        
        public function testStreamEncoding()
        {
            $testfile = realpath(__FILE__);
            $original = file_get_contents($testfile);
    
            // Test Base64
            $fp = fopen($testfile,'rb');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_BASE64;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(base64_decode($encoded),$original);
            
            // test QuotedPrintable
            $fp = fopen($testfile,'rb');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(quoted_printable_decode($encoded),$o>
    difference      <                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          ??????????????????>
    got string      <<?php
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    
    
    /**
     * Zend_Mime_Part
     */
    require_once 'Zend/Mime/Part.php';
    
    /**
     * PHPUnit2 test case
     */
    require_once 'PHPUnit2/Framework/TestCase.php';
    
    /**
     * @package     Zend_Mime
     * @subpackage  UnitTests
     */
    class Zend_Mime_PartTest extends PHPUnit2_Framework_TestCase
    {
        /**
         * MIME part test object
         *
         * @var Zend_Mime_Part
         */
        protected $_part = null;
        protected $_testText;
    
        protected function setUp()
        {
            $this->_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj '
                           . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg';
            $this->part = new Zend_Mime_Part($this->_testText);
            $this->part->encoding = Zend_Mime::ENCODING_BASE64;
            $this->part->type = "text/plain";
            $this->part->filename = 'test.txt';
            $this->part->disposition = 'attachment';
            $this->part->charset = 'iso8859-1';
            $this->part->id = '4711';
        }
    
        public function testHeaders()
        {
            $expectedHeaders = array('Content-Type: text/plain',
                                     'Content-Transfer-Encoding: ' . Zend_Mime::ENCODING_BASE64,
                                     'Content-Disposition: attachment',
                                     'filename="test.txt"',
                                     'charset="iso8859-1"',
                                     'Content-ID: <4711>');
    
            $actual = $this->part->getHeaders();
    
            foreach ($expectedHeaders as $expected) {
                $this->assertContains($expected, $actual);
            }
        }
    
        public function testContentEncoding()
        {
            // Test with base64 encoding
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, base64_decode($content));
            // Test with quotedPrintable Encoding:
            $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, quoted_printable_decode($content));
            // Test with 8Bit encoding
            $this->part->encoding = Zend_Mime::ENCODING_8BIT;
            $content = $this->part->getContent();
            $this->assertEquals($this->_testText, $content);
        }
        
        public function testStreamEncoding()
        {
            $testfile = realpath(__FILE__);
            $original = file_get_contents($testfile);
    
            // Test Base64
            $fp = fopen($testfile,'rb');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_BASE64;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(base64_decode($encoded),$original);
            
            // test QuotedPrintable
            $fp = fopen($testfile,'rb');
            $this->assertTrue(is_resource($fp));
            $part = new Zend_Mime_Part($fp);
            $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
            $fp2 = $part->getEncodedStream();
            $this->assertTrue(is_resource($fp2));
            $encoded = stream_get_contents($fp2);
            fclose($fp);
            $this->assertEquals(quoted_printable_decode($encoded),$original);
        }
    }
    >
    /webapps/tools/zend_framework/tests/Zend/Mime/PartTest.php:90
    /webapps/tools/zend_framework/tests/Zend/Mime/AllTests.php:16
    /webapps/tools/zend_framework/tests/Zend/Mime/AllTests.php:31
    
    FAILURES!
    Tests: 8, Failures: 1.


 

 

Posted by Elisamuel Resto (user00265) on 2006-06-21T06:30:37.000+0000

Not the same error, at least, I doesn't look like it, but it is a unit test failure, so I'll update this.

Versions: php -i

 
    <pre class="literal">PHP Version => 5.1.4-pl0-gentoo
    
    System => Linux user00265 2.6.16-gentoo-r9 #1 SMP PREEMPT Mon Jun 19 04:51:19 AST 2006 i686
    Build Date => Jun 20 2006 17:43:25
    Configure Command =>  './configure' '--prefix=/usr/lib/php5' '--sysconfdir=/etc' '--cache-file=./config.cache' '--enable-cli' '--disable-cgi' '--with-config-file-path=/etc/php/cli-php5' '--with-config-file-scan-dir=/etc/php/cli-php5/ext-active' '--without-pear' '--enable-bcmath' '--with-bz2' '--enable-calendar' '--with-curl' '--with-curlwrappers' '--disable-dbase' '--enable-exif' '--without-fbsql' '--without-fdftk' '--disable-filepro' '--enable-ftp' '--with-gettext' '--with-gmp' '--without-hwapi' '--without-informix' '--without-kerberos' '--enable-mbstring' '--with-mcrypt' '--enable-memory-limit' '--with-mhash' '--without-ming' '--without-msql' '--without-mssql' '--with-ncurses' '--with-openssl' '--with-openssl-dir=/usr' '--enable-pcntl' '--with-pgsql' '--with-pspell' '--without-recode' '--disable-shmop' '--with-snmp' '--enable-soap' '--enable-sockets' '--without-sybase' '--without-sybase-ct' '--enable-sysvmsg' '--enable-sysvsem' '--enable-sysvshm' '--with-tidy' '--disable-tokenizer' '--enable-wddx' '--with-xmlrpc' '--with-xsl' '--with-zlib' '--disable-debug' '--enable-dba' '--with-cdb' '--with-db4' '--without-flatfile' '--with-gdbm' '--with-inifile' '--without-qdbm' '--with-freetype-dir=/usr' '--with-t1lib=/usr' '--disable-gd-jis-conv' '--enable-gd-native-ttf' '--with-jpeg-dir=/usr' '--with-png-dir=/usr' '--with-xpm-dir=/usr/X11R6' '--with-gd' '--with-imap' '--with-imap-ssl' '--with-ldap' '--without-ldap-sasl' '--with-mysql=/usr/lib/mysql' '--with-mysql-sock=/var/run/mysqld/mysqld.sock' '--with-mysqli=/usr/bin/mysql_config' '--without-pdo-dblib' '--without-pdo-firebird' '--with-pdo-mysql=/usr' '--without-pdo-odbc' '--with-pdo-pgsql' '--with-readline' '--without-libedit' '--without-mm' '--enable-sqlite-utf8' '--with-pic' '--enable-maintainer-zts'
    Server API => Command Line Interface
    Virtual Directory Support => enabled
    Configuration File (php.ini) Path => /etc/php/cli-php5/php.ini
    Scan this dir for additional .ini files => /etc/php/cli-php5/ext-active
    additional .ini files parsed => /etc/php/cli-php5/ext-active/zip.ini
    
    PHP API => 20041225
    PHP Extension => 20050922
    Zend Extension => 220051025
    Debug Build => no
    Thread Safety => enabled
    Zend Memory Manager => enabled
    IPv6 Support => enabled

httpd -V

 
    <pre class="literal">user00265 ~ # apache2 -V
    Server version: Apache/2.0.55
    Server built:   Jun 19 2006 20:56:35
    Server's Module Magic Number: 20020903:11
    Architecture:   32-bit
    Server compiled with....
     -D APACHE_MPM_DIR="server/mpm/worker"
     -D APR_HAS_SENDFILE
     -D APR_HAS_MMAP
     -D APR_HAVE_IPV6 (IPv4-mapped addresses enabled)
     -D APR_USE_SYSVSEM_SERIALIZE
     -D APR_USE_PTHREAD_SERIALIZE
     -D SINGLE_LISTEN_UNSERIALIZED_ACCEPT
     -D APR_HAS_OTHER_CHILD
     -D AP_HAVE_RELIABLE_PIPED_LOGS
     -D HTTPD_ROOT="/usr"
     -D SUEXEC_BIN="/usr/sbin/suexec2"
     -D DEFAULT_SCOREBOARD="logs/apache_runtime_status"
     -D DEFAULT_ERRORLOG="logs/error_log"
     -D AP_TYPES_CONFIG_FILE="/etc/apache2/mime.types"
     -D SERVER_CONFIG_FILE="/etc/apache2/httpd.conf"

svn info | grep -i revision

 
    <pre class="literal">ryuji@user00265 ~/Zend/Framework $ svn info | grep -i revision
    Revision: 677

- - - - - -

Error returned:

 
    <pre class="literal">ryuji@user00265 ~/Zend/Framework/tests $ php Zend/Mime/AllTests.php
    PHPUnit 2.3.6 by Sebastian Bergmann.
    
    ..F.....
    
    Time: 0.005915
    There was 1 failure:
    1) testStreamEncoding(Zend_Mime_PartTest)
    expected: <...> but was: <...riginal);
        }
    }
    >
    /home/ryuji/Zend/Framework/tests/Zend/Mime/PartTest.php:90
    /home/ryuji/Zend/Framework/tests/Zend/Mime/AllTests.php:16
    /home/ryuji/Zend/Framework/tests/Zend/Mime/AllTests.php:31
    
    FAILURES!!!
    Tests run: 8, Failures: 1, Errors: 0, Incomplete Tests: 0.

 

 

Posted by Matthew Weier O'Phinney (matthew) on 2006-06-21T21:16:44.000+0000

I found a syntax issue in the B64Filter (base64 stream conversion filter) where it was using a concatenation operator instead of addition operator. This fix was applied in patch 689.

Please test and update the ticket to indicate whether this corrects the testing issue.

Thanks!

 

 

Posted by Richard (openmacnews) on 2006-06-21T22:32:37.000+0000

w/ ZF Revision: 689, still problems ....

/webapps/tools/zend\_framework/tests > php Zend/Mime/AllTests.php PHPUnit 3.0.0alpha11 by Sebastian Bergmann.

..F.....

Time: 00:00 There was 1 failure: 1) testStreamEncoding(Zend\_Mime\_PartTest) failed asserting that <'<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once \\'Zend/Mime/Part.php\\';

/\*\* \* PHPUnit2 test case \*/ require\_once \\'PHPUnit2/Framework/TestCase.php\\';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                       . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = \'test.txt\';
        $this->part->disposition = \'attachment\';
        $this->part->charset = \'iso8859-1\';
        $this->part->id = \'4711\';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array(\'Content-Type: text/plain\',
                                 \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                 \'Content-Disposition: attachment\',
                                 \'filename="test.txt"\',
                                 \'charset="iso8859-1"\',
                                 \'Content-ID: <4711>\');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$original);
    }


} '> is equal to <string:'<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once \\'Zend/Mime/Part.php\\';

/\*\* \* PHPUnit2 test case \*/ require\_once \\'PHPUnit2/Framework/TestCase.php\\';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = \'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj \'
                       . \'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg\';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = \'test.txt\';
        $this->part->disposition = \'attachment\';
        $this->part->charset = \'iso8859-1\';
        $this->part->id = \'4711\';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array(\'Content-Type: text/plain\',
                                 \'Content-Transfer-Encoding: \' . Zend_Mime::ENCODING_BASE64,
                                 \'Content-Disposition: attachment\',
                                 \'filename="test.txt"\',
                                 \'charset="iso8859-1"\',
                                 \'Content-ID: <4711>\');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,\'rb\');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$o'>


expected string <<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ /\*\* \* Zend\_Mime\_Part \*/ require\_once 'Zend/Mime/Part.php'; /\*\* \* PHPUnit2 test case \*/ require\_once 'PHPUnit2/Framework/TestCase.php'; /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText; protected function setUp() { $this->\_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj ' . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg'; $this->part = new Zend\_Mime\_Part($this->\_testText); $this->part->encoding = Zend\_Mime::ENCODING\_BASE64; $this->part->type = "text/plain"; $this->part->filename = 'test.txt'; $this->part->disposition = 'attachment'; $this->part->charset = 'iso8859-1'; $this->part->id = '4711'; } public function testHeaders() { $expectedHeaders = array('Content-Type: text/plain', 'Content-Transfer-Encoding: ' . Zend\_Mime::ENCODING\_BASE64, 'Content-Disposition: attachment', 'filename="test.txt"', 'charset="iso8859-1"', 'Content-ID: <4711>'); $actual = $this->part->getHeaders(); foreach ($expectedHeaders as $expected) { $this->assertContains($expected, $actual); } } public function testContentEncoding() { // Test with base64 encoding $content = $this->part->getContent(); $this->assertEquals($this->\_testText, base64\_decode($content)); // Test with quotedPrintable Encoding: $this->part->encoding = Zend\_Mime::ENCODING\_QUOTEDPRINTABLE; $content = $this->part->getContent(); $this->assertEquals($this->\_testText, quoted\_printable\_decode($content)); // Test with 8Bit encoding $this->part->encoding = Zend\_Mime::ENCODING\_8BIT; $content = $this->part->getContent(); $this->assertEquals($this->\_testText, $content); } public function testStreamEncoding() { $testfile = realpath(\_\_FILE\_\_); $original = file\_get\_contents($testfile); // Test Base64 $fp = fopen($testfile,'rb'); $this->assertTrue(is\_resource($fp)); $part = new Zend\_Mime\_Part($fp); $part->encoding = Zend\_Mime::ENCODING\_BASE64; $fp2 = $part->getEncodedStream(); $this->assertTrue(is\_resource($fp2)); $encoded = stream\_get\_contents($fp2); fclose($fp); $this->assertEquals(base64\_decode($encoded),$original); // test QuotedPrintable $fp = fopen($testfile,'rb'); $this->assertTrue(is\_resource($fp)); $part = new Zend\_Mime\_Part($fp); $part->encoding = Zend\_Mime::ENCODING\_QUOTEDPRINTABLE; $fp2 = $part->getEncodedStream(); $this->assertTrue(is\_resource($fp2)); $encoded = stream\_get\_contents($fp2); fclose($fp); $this->assertEquals(quoted\_printable\_decode($encoded),$o> difference < ??????????????????> got string <<?php /\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/

/\*\* \* Zend\_Mime\_Part \*/ require\_once 'Zend/Mime/Part.php';

/\*\* \* PHPUnit2 test case \*/ require\_once 'PHPUnit2/Framework/TestCase.php';

/\*\* \* @package Zend\_Mime \* @subpackage UnitTests \*/ class Zend\_Mime\_PartTest extends PHPUnit2\_Framework\_TestCase { /\*\* \* MIME part test object \* \* @var Zend\_Mime\_Part \*/ protected $\_part = null; protected $\_testText;

 
    protected function setUp()
    {
        $this->_testText = 'safdsafsaˆlg ˆˆgdˆˆ sdˆjgˆsdjgˆldˆgksdˆgjˆsdfgˆdsjˆgjsdˆgjˆdfsjgˆdsfjˆdjsˆg kjhdkj '
                       . 'fgaskjfdh gksjhgjkdh gjhfsdghdhgksdjhg';
        $this->part = new Zend_Mime_Part($this->_testText);
        $this->part->encoding = Zend_Mime::ENCODING_BASE64;
        $this->part->type = "text/plain";
        $this->part->filename = 'test.txt';
        $this->part->disposition = 'attachment';
        $this->part->charset = 'iso8859-1';
        $this->part->id = '4711';
    }
    
    public function testHeaders()
    {
        $expectedHeaders = array('Content-Type: text/plain',
                                 'Content-Transfer-Encoding: ' . Zend_Mime::ENCODING_BASE64,
                                 'Content-Disposition: attachment',
                                 'filename="test.txt"',
                                 'charset="iso8859-1"',
                                 'Content-ID: <4711>');
    
        $actual = $this->part->getHeaders();
    
        foreach ($expectedHeaders as $expected) {
            $this->assertContains($expected, $actual);
        }
    }
    
    public function testContentEncoding()
    {
        // Test with base64 encoding
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, base64_decode($content));
        // Test with quotedPrintable Encoding:
        $this->part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, quoted_printable_decode($content));
        // Test with 8Bit encoding
        $this->part->encoding = Zend_Mime::ENCODING_8BIT;
        $content = $this->part->getContent();
        $this->assertEquals($this->_testText, $content);
    }
    
    public function testStreamEncoding()
    {
        $testfile = realpath(__FILE__);
        $original = file_get_contents($testfile);
    
        // Test Base64
        $fp = fopen($testfile,'rb');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_BASE64;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(base64_decode($encoded),$original);
    
        // test QuotedPrintable
        $fp = fopen($testfile,'rb');
        $this->assertTrue(is_resource($fp));
        $part = new Zend_Mime_Part($fp);
        $part->encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE;
        $fp2 = $part->getEncodedStream();
        $this->assertTrue(is_resource($fp2));
        $encoded = stream_get_contents($fp2);
        fclose($fp);
        $this->assertEquals(quoted_printable_decode($encoded),$original);
    }


} > /webapps/tools/zend\_framework/tests/Zend/Mime/PartTest.php:90 /webapps/tools/zend\_framework/tests/Zend/Mime/AllTests.php:16 /webapps/tools/zend\_framework/tests/Zend/Mime/AllTests.php:31

FAILURES! Tests: 8, Failures: 1.

 

 

Posted by Matthew Weier O'Phinney (matthew) on 2006-06-22T09:20:49.000+0000

Patch 692 refactors the encoding to use native PHP stream conversion filters. Please update, run tests, and report the status of this fix.

 

 

Posted by Richard (openmacnews) on 2006-06-22T09:58:54.000+0000

w/ ZF r692,

php Zend/Mime/AllTests.php PHPUnit 3.0.0alpha11 by Sebastian Bergmann.

........

Time: 00:00

OK (8 tests)

which, if it's expected, looks like the issue is fixed.

thx,

richard

 

 

Posted by Matthew Weier O'Phinney (matthew) on 2006-06-22T10:24:37.000+0000

Fixed with patch 692.

 

 