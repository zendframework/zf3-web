---
layout: post
title: Zend\Db in ZF 2.1
date: 2013-02-01
author: Ralph Schindler
url_author: http://ralphschindler.com/
permalink: /blog/zend-db-in-zf-2-1.html
categories:
- blog

---

`Zend\Db` just got a little better with the release of Zend Framework 2.1. All the cool things you could do on Mysql, SQLite, Postgresql and SQL Server can now be done on DB2 and Oracle. In addition, a number of additions were brought into the `Zend\Db\Sql\Select` object as well.

<!--more-->

### Connecting to DB2 and Oracle

 Connecting to DB2 whether on Windows, \*nix, Mac, or the IBM i, is the same as any other driver - using the array notation:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #007700">use </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Db</span><span style="color: #007700">\</span><span style="color: #0000BB">Adapter</span><span style="color: #007700">\</span><span style="color: #0000BB">Adapter </span><span style="color: #007700">as </span><span style="color: #0000BB">DbAdapter</span><span style="color: #007700">;<br></br><br></br></span><span style="color: #FF8000">// DB2 Connection<br></br></span><span style="color: #0000BB">$adapter </span><span style="color: #007700">= new </span><span style="color: #0000BB">DbAdapter</span><span style="color: #007700">(array(<br></br>    </span><span style="color: #DD0000">'driver' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'IbmDb2'</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'database' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'*LOCAL'</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'username' </span><span style="color: #007700">=> </span><span style="color: #DD0000">''</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'password' </span><span style="color: #007700">=> </span><span style="color: #DD0000">''</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'driver_options' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'i5_naming' </span><span style="color: #007700">=> </span><span style="color: #0000BB">DB2_I5_NAMING_ON</span><span style="color: #007700">,<br></br>        </span><span style="color: #DD0000">'i5_libl' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'LIB1 LIB2 LIB3'<br></br>    </span><span style="color: #007700">),<br></br>    </span><span style="color: #DD0000">'platform_options' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'quote_identifiers' </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">)<br></br>);<br></br><br></br></span><span style="color: #FF8000">// Oracle Connection<br></br></span><span style="color: #0000BB">$adapter </span><span style="color: #007700">= new </span><span style="color: #0000BB">DbAdapter</span><span style="color: #007700">(array(<br></br>    </span><span style="color: #DD0000">'driver' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'Oci8'</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'hostname' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'localhost/XE'</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'username' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'developer'</span><span style="color: #007700">,<br></br>    </span><span style="color: #DD0000">'password' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'developer'<br></br></span><span style="color: #007700">));<br></br></span>
    </span>


 Both Oracle and DB2 carry different conventional usage patterns and workflows than their more modern successors in the relational database space. As such, certain default behaviors can be turned off. For example, by default, when queries are generated via any of the `Zend\Db\Sql` object (SQL abstraction), all known identifiers are identifier quoted. That means if a developer wrote: `$select->from('foo');` then "foo" would be quoted in the database platform specific way. For MySQL this means back-ticks, like ``foo``, and for most other database that means being quoted with double quotes. In cases of Oracle and DB2 where there is a pronounced history of not quoting identifiers, `Zend\Db\Adapter` can be provided an option to turn this off - as you can see above in the "platform\_options".

 Once an adapter is created, it can be used by any of the `Zend\Db` API. Here are a few examples of what you can do:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #FF8000">// Zend\Db\TableGateway<br></br></span><span style="color: #007700">use </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Db</span><span style="color: #007700">\</span><span style="color: #0000BB">TableGateway</span><span style="color: #007700">\</span><span style="color: #0000BB">TableGateway</span><span style="color: #007700">;<br></br> <br></br></span><span style="color: #0000BB">$table </span><span style="color: #007700">= new </span><span style="color: #0000BB">TableGateway</span><span style="color: #007700">(</span><span style="color: #DD0000">'ARTIST'</span><span style="color: #007700">), </span><span style="color: #0000BB">$adapter</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$results </span><span style="color: #007700">= </span><span style="color: #0000BB">$table</span><span style="color: #007700">-></span><span style="color: #0000BB">select</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'ARTIST_ID > ?' </span><span style="color: #007700">=> </span><span style="color: #0000BB">5000</span><span style="color: #007700">));<br></br><br></br></span><span style="color: #FF8000">// iterate results outputting each column<br></br></span><span style="color: #007700">foreach (</span><span style="color: #0000BB">$results </span><span style="color: #007700">as </span><span style="color: #0000BB">$row</span><span style="color: #007700">) {<br></br>  echo </span><span style="color: #DD0000">'&lt;tr&gt;'</span><span style="color: #007700">;<br></br>  foreach (</span><span style="color: #0000BB">$row </span><span style="color: #007700">as </span><span style="color: #0000BB">$col</span><span style="color: #007700">) {<br></br>    echo </span><span style="color: #DD0000">'&lt;td&gt;' </span><span style="color: #007700">. </span><span style="color: #0000BB">$col </span><span style="color: #007700">. </span><span style="color: #DD0000">'&lt;/td&gt;'</span><span style="color: #007700">;<br></br>  }<br></br>  echo </span><span style="color: #DD0000">'&lt;/tr&gt;'</span><span style="color: #007700">;<br></br>}<br></br></span>
    </span>


 A more complex query:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #FF8000">// complex query<br></br></span><span style="color: #0000BB">$sql </span><span style="color: #007700">= new </span><span style="color: #0000BB">Sql</span><span style="color: #007700">(</span><span style="color: #0000BB">$adapter</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$select </span><span style="color: #007700">= </span><span style="color: #0000BB">$sql</span><span style="color: #007700">-></span><span style="color: #0000BB">select</span><span style="color: #007700">()-></span><span style="color: #0000BB">from</span><span style="color: #007700">(</span><span style="color: #DD0000">'ARTIST'</span><span style="color: #007700">)<br></br>    -></span><span style="color: #0000BB">columns</span><span style="color: #007700">(array()) </span><span style="color: #FF8000">// no columns from main table<br></br>    </span><span style="color: #007700">-></span><span style="color: #0000BB">join</span><span style="color: #007700">(</span><span style="color: #DD0000">'ALBUM'</span><span style="color: #007700">, </span><span style="color: #DD0000">'ARTIST.ARTIST_ID = ALBUM.ARTIST_ID'</span><span style="color: #007700">, array(</span><span style="color: #DD0000">'TITLE'</span><span style="color: #007700">, </span><span style="color: #DD0000">'RELEASE_DATE'</span><span style="color: #007700">))<br></br>    -></span><span style="color: #0000BB">order</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'RELEASE_DATE'</span><span style="color: #007700">, </span><span style="color: #DD0000">'TITLE'</span><span style="color: #007700">))<br></br>    -></span><span style="color: #0000BB">where</span><span style="color: #007700">-></span><span style="color: #0000BB">like</span><span style="color: #007700">(</span><span style="color: #DD0000">'ARTIST.NAME'</span><span style="color: #007700">, </span><span style="color: #DD0000">'%Brit%'</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$statement </span><span style="color: #007700">= </span><span style="color: #0000BB">$sql</span><span style="color: #007700">-></span><span style="color: #0000BB">prepareStatementFromSqlObject</span><span style="color: #007700">(</span><span style="color: #0000BB">$select</span><span style="color: #007700">);<br></br>foreach (</span><span style="color: #0000BB">$statement</span><span style="color: #007700">-></span><span style="color: #0000BB">execute</span><span style="color: #007700">() as </span><span style="color: #0000BB">$row</span><span style="color: #007700">) {<br></br>    </span><span style="color: #FF8000">// var_dump($row);<br></br></span><span style="color: #007700">}<br></br></span>
    </span>


### Other Interesting Additions to Zend\\Db\\Sql

 Join From SubSelect:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$subselect </span><span style="color: #007700">= new </span><span style="color: #0000BB">Select</span><span style="color: #007700">;<br></br></span><span style="color: #0000BB">$subselect</span><span style="color: #007700">-></span><span style="color: #0000BB">from</span><span style="color: #007700">(</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">)-></span><span style="color: #0000BB">where</span><span style="color: #007700">-></span><span style="color: #0000BB">like</span><span style="color: #007700">(</span><span style="color: #DD0000">'y'</span><span style="color: #007700">, </span><span style="color: #DD0000">'%Foo%'</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$select </span><span style="color: #007700">= new </span><span style="color: #0000BB">Select</span><span style="color: #007700">;<br></br></span><span style="color: #0000BB">$select</span><span style="color: #007700">-></span><span style="color: #0000BB">from</span><span style="color: #007700">(</span><span style="color: #DD0000">'foo'</span><span style="color: #007700">)-></span><span style="color: #0000BB">join</span><span style="color: #007700">(array(</span><span style="color: #DD0000">'z' </span><span style="color: #007700">=> </span><span style="color: #0000BB">$select39subselect</span><span style="color: #007700">), </span><span style="color: #DD0000">'z.foo = bar.id'</span><span style="color: #007700">);<br></br><br></br></span><span style="color: #FF8000">/* produces SQL92 SQL (newlines added for readability):<br></br>SELECT "foo".*, "z".*<br></br>    FROM "foo"<br></br>    INNER JOIN (<br></br>        SELECT "bar".* FROM "bar"<br></br>            WHERE "y" LIKE '%Foo%'<br></br>        ) AS "z" ON "z"."foo" = "bar"."id"<br></br> */<br></br></span>
    </span>


 Expressions inside Order:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$select </span><span style="color: #007700">= new </span><span style="color: #0000BB">Select</span><span style="color: #007700">;<br></br></span><span style="color: #0000BB">$select</span><span style="color: #007700">-></span><span style="color: #0000BB">order</span><span style="color: #007700">(new </span><span style="color: #0000BB">Expression</span><span style="color: #007700">(</span><span style="color: #DD0000">'RAND()'</span><span style="color: #007700">));<br></br></span>
    </span>


### Call to Action

 Since our DB2 and Oracle drivers are new, we are sure they are not perfect yet and can be improved to better allow a more natural workflow for the database needs of a DB2 or Oracle developer. If you find anything that is a bug, or feature request, please take the time to fill out an issue on our github repository for ZF2:

- <https://github.com/zendframework/zf2/issues>

 Happy ZFing!
