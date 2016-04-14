---
layout: issue
title: "ZF + php.ini(session.save_handler = eaccelerator) --&gt; apache BusError TRAC-117"
id: ZF-7
---

ZF-7: ZF + php.ini(session.save\_handler = eaccelerator) --> apache BusError TRAC-117
-------------------------------------------------------------------------------------

 Issue Type: Bug Created: 2006-06-16T14:29:34.000+0000 Last Updated: 2007-07-05T14:43:05.000+0000 Status: Resolved Fix version(s): - 0.6.0 (16/Dec/06)
 
 Reporter:  Richard (openmacnews)  Assignee:  Matthew Weier O'Phinney (matthew)  Tags: - Zend\_Controller
 
 Related issues: 
 Attachments: 
### Description

hello,

{panel:title=Enviroment} Zend Framework r654 installed on OSX 10.4.6 Apache/2.2.3-dev + APR 1.2.8-dev + APR-Util 1.2.8-dev (r412878) 32-bit, mpm=worker, threaded, forked PHP Version => 5.2.0-dev (configure: ... --with-tsrm-pthreads --enable-maintainer-zts) Debug Build => no Thread Safety => enabled eAccelerator v0.9.5-beta2 (r213) {panel}

{panel:title=php.ini} session.save\_handler = files zend\_extension\_ts="/usr/local/php\_libs/ext/no-debug-zts/eaccelerator.so" eaccelerator.enable="1" eaccelerator.optimizer="1" eaccelerator.sessions="shm" {panel}

and per prior discuss on ZF list, my vhost config is:

 
    <pre class="highlight">
    
        ServerName zend.mydomain.com
        ServerRoot /webapps/
        DocumentRoot /webapps/zend.mydomain.com/document_root
    
        RewriteEngine Off
    
        
            RewriteEngine On
    
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule !\.(js|ico|gif|jpg|png|css)$ /index.php
    
            Options FollowSymLinks
            Includes ExecCGI MultiViews
    
            Order Deny,Allow
            Deny from All
            Allow from 10.0.0.6
        
    
        RewriteLogLevel 9
        RewriteLog /var/log/modrewrite.log


with the above config, all's well.

if, however i: {panel:title=session.save\_handler change} --- session.save\_handler = files +++ session.save\_handler = eaccelerator {panel}

all non-ZF-controller'd sites work perfectly, with eA as the session save\_handler.

but, the ZF site now pegs CPU to 100%, paints NO output, and eventually kills off the Apache thread with a BusError?:

{panel:title=apache log} [Mon Jun 05 09:57:20 2006] [notice] child pid 24933 exit signal Bus error (10) {panel}

the only other log activity i have at the moment is in modrewrite debug log.

{panel:title=modrewrite.log w/session.save\_handler = eaccelerator} 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c2450/initial] (3) [perdir /] strip per-dir prefix: /webapps/zend.mydomain.com/document\_root/ -> webapps/zend.mydomain.com/document\_root/ 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c2450/initial] (3) [perdir /] applying pattern '.(js|ico|gif|jpg|png|css)$' to uri 'webapps/zend.mydomain.com/document\_root/' 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c2450/initial] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/' pattern='!-f' => matched 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c2450/initial] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/' pattern='!-d' => not-matched 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c2450/initial] (1) [perdir /] pass through /webapps/zend.mydomain.com/document\_root/ 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c8450/subreq] (3) [perdir /] strip per-dir prefix: /webapps/zend.mydomain.com/document\_root/index.php -> webapps/zend.mydomain.com/document\_root/index.php 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c8450/subreq] (3) [perdir /] applying pattern '.(js|ico|gif|jpg|png|css)$' to uri 'webapps/zend.mydomain.com/document\_root/index.php' 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c8450/subreq] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/index.php' pattern='!-f' => not-matched 10.0.0.6 - - [05/Jun/2006:09:56:57 --0700] [zend.mydomain.com/sid#191d6b8][rid#19c8450/subreq] (1) [perdir /] pass through /webapps/zend.mydomain.com/document\_root/index.php {panel}

{panel:title=modrewrite.log w/session.save\_handler = files} 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c3050/initial] (3) [perdir /] strip per-dir prefix: /webapps/zend.mydomain.com/document\_root/ -> webapps/zend.mydomain.com/document\_root/ 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c3050/initial] (3) [perdir /] applying pattern '.(js|ico|gif|jpg|png|css)$' to uri 'webapps/zend.mydomain.com/document\_root/' 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c3050/initial] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/' pattern='!-f' => matched 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c3050/initial] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/' pattern='!-d' => not-matched 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c3050/initial] (1) [perdir /] pass through /webapps/zend.mydomain.com/document\_root/ 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c9050/subreq] (3) [perdir /] strip per-dir prefix: /webapps/zend.mydomain.com/document\_root/index.php -> webapps/zend.mydomain.com/document\_root/index.php 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c9050/subreq] (3) [perdir /] applying pattern '.(js|ico|gif|jpg|png|css)$' to uri 'webapps/zend.mydomain.com/document\_root/index.php' 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c9050/subreq] (4) [perdir /] RewriteCond?: input='/webapps/zend.mydomain.com/document\_root/index.php' pattern='!-f' => not-matched 10.0.0.6 - - [05/Jun/2006:10:17:04 --0700] [zend.mydomain.com/sid#18b5eb8][rid#19c9050/subreq] (1) [perdir /] pass through /webapps/zend.mydomain.com/document\_root/index.php {panel}

to my eye, there's no difference here ...

don't know if/where the 'bug' is.

 

 

### Comments

Posted by Elisamuel Resto (user00265) on 2006-07-31T00:18:56.000+0000

Added formatting for readability's sake

 

 

Posted by Richard (openmacnews) on 2006-07-31T10:37:15.000+0000

close

 

 

Posted by Matthew Weier O'Phinney (matthew) on 2006-11-05T10:10:37.000+0000

Can the OP update the issue and indicate if this is still an issue? By the information provided, it appears to be an eaccelerator configuration issue. There may be something ZF can do to help prevent the issue, but most likely the issue has to do with how the opcode cache is caching dependencies (similar issues have occurred with past APC versions, for instance).

If no further information is provided on the issue, will close prior to the next release.

 

 

Posted by Bill Karwin (bkarwin) on 2006-11-13T15:24:58.000+0000

Changing fix version to 0.6.0.

 

 

Posted by Matthew Weier O'Phinney (matthew) on 2006-11-28T14:00:37.000+0000

Cannot reproduce as have not received additional information from original reporter. Closing.

 

 