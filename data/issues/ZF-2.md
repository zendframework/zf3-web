---
layout: issue
title: "Test Fisheye to JIRA linker"
id: ZF-2
---

ZF-2: Test Fisheye to JIRA linker
---------------------------------

 Issue Type: Task Created: 2006-06-15T02:42:09.000+0000 Last Updated: 2007-07-05T14:43:05.000+0000 Status: Resolved Fix version(s): 
 Reporter:  Crowd Administrator (admin)  Assignee:  Jayson Minard (jayson)  Tags: 
 Related issues: 
 Attachments: 
### Description

Test Fisheye to JIRA Linking with simpler linker configured as:

regex: [a-zA-Z]{2,}-\\d+

href: [http://framework.zend.com/issues/browse/${0](http://framework.zend.com/issues/browse/$%7B0)}

 

 

### Comments

Posted by Crowd Administrator (admin) on 2006-06-15T02:50:24.000+0000

changed regex to: [Z][F]-\\d+ to be more specific. Link worked.

 

 

Posted by Crowd Administrator (admin) on 2006-06-15T03:03:38.000+0000

Need to fix the link form JIRA back to Fisheye. Trying the Fisheye plugin to see how it works rather than using the JIRA SVN scanner.

The plugin can be found at:

[http://confluence.atlassian.com/display/JIRAEXT/…](http://confluence.atlassian.com/display/JIRAEXT/FishEye+for+JIRA)

 

 

Posted by Jayson Minard (jayson) on 2006-06-17T23:54:17.000+0000

Ok, setup with all...

ZF\*-#

patterns

 

 