---
layout: issue
title: "Ability to use TrueType fonts in PDF documents"
id: ZF-6
---

ZF-6: Ability to use TrueType fonts in PDF documents
----------------------------------------------------

 Issue Type: New Feature Created: 2006-06-16T13:32:15.000+0000 Last Updated: 2007-07-05T14:44:26.000+0000 Status: Closed Fix version(s): - 0.1.4 (29/Jun/06)
 
 Reporter:  Willie Alberty (willie)  Assignee:  Willie Alberty (willie)  Tags: - Zend\_Pdf
 
 Related issues: - [ZF-19](/issues/browse/ZF-19)
- [ZF-18](/issues/browse/ZF-18)
 
 Attachments: 
### Description

Given a path to a TrueType font file on disk, create a font object that can be used with Zend\_Pdf\_Page::setFont() and Zend\_Pdf\_Page::drawText(). The font program should be able to be embedded in the resulting PDF document.

 

 

### Comments

Posted by Kevin McArthur (kevin) on 2006-06-16T23:17:43.000+0000

I have tested this patch and it has my go ahead for commit. Only questions are to the new API function names as we will have to stick with them once they are committed.

This fix should be included in 0.1.4

 

 

Posted by Willie Alberty (willie) on 2006-06-17T16:12:59.000+0000

Done.

- - - - - -

h4. New Functionality

h5. Fonts

It is now possible to use custom TrueType fonts in your PDF documents.

You can currently use any individual TrueType font (which usually has a '.ttf' extension) or an OpenType font ('.otf' extension) if it contains TrueType outlines. Currently unsupported, but planned for a future revision are Mac OS X .dfont files and Microsoft TrueType Collection ('.ttc' extension) files.

In addition, font instantiation is now managed via factory methods in Zend\_Pdf\_Font. This class replaces the deprecated Zend\_Pdf\_Font\_Standard (see the API Changes section below) and can create any type of font object: Standard PDF, TrueType, Type 1 (coming soon), etc. If you attempt to create a font using an unsupported format, the factory will throw an exception.

Since font objects are immutable, the factory methods also cache them so they are reused if a font with the same name or file path is requested again.

To create a font object, you either provide its PostScript name or the full file path to the font file:

 
    <pre class="highlight">
    $helveticaFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    $goodDogCoolFont = Zend_Pdf_Font::fontWithPath('/path/to/GOODDC__.TTF');


Constants are provided for the PostScript names of the standard 14 PDF fonts. See the FONT\_ constants defined in Zend\_Pdf\_Font.

Once instantiated, custom fonts behave exactly the same as built-in fonts, i.e. - you use the same drawing commands you are already familiar with:

 
    <pre class="highlight">
    $page->setFont($goodDogCoolFont, 24);
    $page->drawText('Good Dog Cool!', 72, 720);


By default, custom fonts will be embedded in the resulting PDF document. This permits recipients to view the page as intended, even if they don't have the proper fonts installed on their system. If you are concerned about file size, you can request that the font program not be embedded by passing the appropriate option to the font factory method:

 
    <pre class="highlight">
    $goodDogCoolFont = Zend_Pdf_Font::fontWithPath('/path/to/GOODDC__.TTF',
                                                   Zend_Pdf_Font::EMBED_DONTEMBED);


If the font program is not embedded but the recipient of the PDF file has the font installed on their system, they will see the document as intended. If they do not have the correct font installed, the PDF viewer application will do its best to synthesize a replacement.

Some fonts have very specific licensing rules which prevent them from being embedded in PDF documents. So you are not caught off-guard by this, if you try to use a font that cannot be embedded, the factory method will throw an exception.

You can still use these fonts, but you must either pass the do not embed flag as described above, or you can simply suppress the exception:

 
    <pre class="highlight">
    $font = Zend_Pdf_Font::fontWithPath('/path/to/GOODDC__.TTF', 
                                        Zend_Pdf_Font::EMBED_SUPPRESSEMBEDEXCEPTION);


This technique is preferred if you allow an end-user to choose their own fonts. Fonts which can be embedded in the PDF document will be; those that cannot, won't.

Font programs can be rather large, some reaching into the tens of megabytes. By default, all embedded fonts are compressed using the Flate compression scheme, resulting in a space savings of 50% on average. If, for some reason, you do not want to compress the font program, you can disable it with an option:

 
    <pre class="highlight">
    $font = Zend_Pdf_Font::fontWithPath('/path/to/GOODDC__.TTF',
                                        Zend_Pdf_Font::EMBED_DONTCOMPRESS);


Finally, when necessary, you can combine the embedding options by using the bitwise OR operator:

 
    <pre class="highlight">
    $font = Zend_Pdf_Font::fontWithPath('/path/to/GOODDC__.TTF',
                                        (Zend_Pdf_Font::EMBED_SUPPRESSEMBEDEXCEPTION |
                                         Zend_Pdf_Font::EMBED_DONTCOMPRESS));


h5. Character Encoding Methods

It is now possible to specify the character encoding method used by a string at draw time via an optional parameter to drawText(). For example:

 
    <pre class="highlight">
    $page->drawText('Hello world!', 72, 720, 'ISO-8859-1');
    $page->drawText($aString, 72, 360, 'UTF-8');


If no character encoding method is provided, the string will be interpreted using the encoding method of the current locale (see <http://www.php.net/manual/function.setlocale.php>), which is usually 'ISO-8859-1'. This is consistent with the existing behavior.

Please note that although you can now specify the string's character encoding method as one of the Unicode encodings, you are still limited to the Latin-1 character set for all but the built-in Symbol and Zapf Dingbats fonts (see the next section below). Full Unicode support is still under development.

h5. Symbol and Zapf Dingbats

As discussed at length on the framework mailing list (see the thread "Zend\_Pdf: Anybody actively using Symbol or Zapf Dingbats fonts?" started on 2006-05-25), the built-in Symbol and Zapf Dingbats fonts contain characters which fall outside the Latin-1 character set. Internally, the PDF document uses special encoding methods to access characters in these fonts.

These fonts were broken in versions 0.1.4 and earlier of the framework because they did not contain the code necessary to deal with the special encoding methods. Symbol and Zapf Dingbats are now available for use, and the framework shields you from the details of their special encodings. You simply use regular Unicode characters as you would with any other font.

For example, to display a Star of David character (U+2721), a black diamond character (U+25C6), a check mark character (U+2713), and an airplane character (U+2708) using Zapf Dingbats, you would use the following string, which is in UTF-16 big-endian encoding:

 
    <pre class="highlight">
    $utf16String = "\x27\x21\x25\xC6\x27\x13\x27\x08";


Note how easy it is to read the Unicode character codes when the string uses UTF-16 big-endian encoding. Here are the same characters using UTF-8 encoding:

 
    <pre class="highlight">
    $utf8String  = "\xE2\x9C\xA1\xE2\x97\x86\xE2\x9C\x93\xE2\x9C\x88";


You then feed this string to drawText() as any other, making sure to specify the encoding method you used:

 
    <pre class="highlight">
    $zapfDingbatsFont = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_ZAPFDINGBATS);
    $page->setFont($zapfDingbatsFont, 24);
    $page->drawText($utf16String, 72, 720, 'UTF-16BE');


The Unicode characters to use with the Zapf Dingbats font can be found in the Dingbats character range (U+2700-27BF) of the Unicode standard. A reference document for these characters can be found here: <http://www.unicode.org/charts/PDF/U2700.pdf>

The Symbol font contains a more diverse character set, coving Arabic numerals, the Greek alphabet, many mathematical symbols and more. One of the better references available online is part of the XSL-FO test suite, and can be found at the World Wide Web Consortium's web site: [http://w3.org/Style/XSL/…](http://www.w3.org/Style/XSL/TestSuite/contrib/XEP/Tests/symbol.pdf)

Similar reference documents, which will be generated by the framework itself, will be made available in a future documentation update.

If you're new to Unicode, the following Wikipedia article provides a good introduction and contains links to other excellent Unicode resources: <http://en.wikipedia.org/wiki/Unicode>

There is a compatibility method available for the built-in Symbol and Zapf Dingbats fonts which converts a string using the font's internal encoding to the proper Unicode characters (in UTF-16BE encoding). This method should only be used if you need to support these fonts with legacy applications which are not Unicode-aware:

 
    <pre class="highlight">
    $utf16String = $zapfDingbatsFont->toUnicode($legacyString);


- - - - - -

h4. API Changes

h5. Zend\_Pdf\_Font\_Standard

The old Zend\_Pdf\_Font\_Standard class has been removed. (Technically, it still exists, but it has been renamed and made into an abstract class.)

Font instantiation is now managed by the factory methods in Zend\_Pdf\_Font. Consequently, you will need to make the following changes to your code:

 
    <pre class="literal">
    OLD: $font = new Zend_Pdf_Font_Standard(Zend_Pdf_Const::FONT_HELVETICA);
    NEW: $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);


Please note that the name of the constants for the built-in PDF fonts have changed as well (see the discussion in ZF-14). Both of these changes are easily handled with one global search-and-replace operation:

 
    <pre class="literal">
    SEARCH:  new Zend_Pdf_Font_Standard(Zend_Pdf_Const
    REPLACE: Zend_Pdf_Font::fontWithName(Zend_Pdf_Font


- - - - - -

h5. Other Changes

Many more changes have taken place "under the hood." The entire text system is being prepared to fully support Unicode text. Additionally, font and character metrics are now available, paving the way for some powerful layout tools which are already under development. More information on these upcoming features will be available soon.

For more information on the internal changes, please refer to the inline class documentation.

 

 

Posted by Willie Alberty (willie) on 2006-06-17T19:20:25.000+0000

{panel:title=NOTE|titleBGColor=#F7D6C1|bgColor=#FFFFCE} The font embedding constants referred to above have been renamed (see ZF-14):

EMBED\_DONTEMBED -> EMBED\_DONT\_EMBED\\ EMBED\_SUPPRESSEMBEDEXCEPTION -> EMBED\_SUPPRESS\_EMBED\_EXCEPTION\\ EMBED\_DONTCOMPRESS -> EMBED\_DONT\_COMPRESS {panel}

 

 