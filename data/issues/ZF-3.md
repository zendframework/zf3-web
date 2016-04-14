---
layout: issue
title: "Zend_Log Level Ignored"
id: ZF-3
---

ZF-3: Zend\_Log Level Ignored
-----------------------------

 Issue Type: Bug Created: 2006-06-16T09:44:39.000+0000 Last Updated: 2008-02-26T14:34:19.000+0000 Status: Closed Fix version(s): - 0.1.4 (29/Jun/06)
 
 Reporter:  Darby Felton (darby)  Assignee:  Darby Felton (darby)  Tags: - Zend\_Log
 
 Related issues: 
 Attachments: 
### Description

Because of using a bitwise OR operator between the selected log level and the logger level mask, Zend\_Log logs all levels, ignoring the level mask.

 

 

### Comments

Posted by Darby Felton (darby) on 2006-06-16T09:57:52.000+0000

bitwise OR changed to AND

 

 