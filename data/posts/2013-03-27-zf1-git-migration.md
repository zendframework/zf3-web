---
layout: post
title: Zend Framework 1 is Migrating to Git!
date: 2013-03-27T17:45:00Z
update: 2013-03-27T17:45:00Z
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/2013-03-27-zf1-git-migration.html
categories:
- blog

---

 Since its inception, Zend Framework 1 has used [Subversion](http://subversion.apache.org/) for versioning. However, as we approach its end-of-life (which will occur 12-18 months from the time this post is written), and as our experience with ZF2 processes becomes more familiar, we -- the Zend team and the Community Review team -- feel that we can better support ZF1 via [GitHub](http://github.com).

 As such, we will be migrating the ZF1 Subversion repository to GitHub this week. Please read on for details!

What will happen
----------------

### Subversion Access

 **First: you will still have access to Subversion!**

 For versions prior to 1.12, we will continue hosting our current Subversion repository, and if you are using `svn:externals` with a tag or branch on that repository, that will continue to work. However, the repository will be read-only going forward.

 The release-1.12 branch, however, will become the "master" branch on the GitHub repository. All future updates, including security fixes, will be made to this branch and this branch only; future tags will be made against GitHub. However, for those of you using Subversion, all is not lost: Github [supports](https://github.com/blog/966-improved-subversion-client-support) [Subversion](https://github.com/blog/626-announcing-svn-support)! (That's _2_ separate links!)

 If you are pointing your `svn:externals` at either trunk or the release-1.12 branch, you will need to update your `svn:externals` to point at the new repository location. To do this, you will execute the following:

 
    <pre class="highlight">
    [user@server]$ svn propedit svn:externals path/in/working-dir/to/zf1


 This will spawn an editor. In that editor, change the URL for ZF1:

- "http://framework.zend.com/svn/framework/standard/trunk" becomes **"https://github.com/zendframework/zf1/trunk"**
- "http://framework.zend.com/svn/framework/standard/branches/release-1.12" becomes **"https://github.com/zendframework/zf1/trunk"**
- "http://framework.zend.com/svn/framework/standard/tags/release-1.12`.*`" become **"https://github.com/zendframework/zf1/tags/release-1.12`.*`"**

 Once you have made this change, save and exit. Then run:

 
    <pre class="highlight">
    [user@server]$ svn commit -m "update externals"


### svn:externals - Dojo

 Currently, the ZF1 repository defines a single `svn:externals` property, which adds [Dojo Toolkit](http://dojotoolkit.org/).

 Currently, this points to version 1.5 of Dojo, as that is the last version with which ZF1 maintained compatibility. We have decided with the move to GitHub to remove the `svn:externals` entry, and _not_ add a git submodule; however, we _will_ continue packaging Dojo 1.5 in any ZF1 releases.

 If you relied on the `svn:externals` definition in ZF1 in order to obtain Dojo, you will need to add it to your repository yourself. If you need to do so, point the definition at "http://svn.dojotoolkit.org/src/branches/1.5".

### "Extras" Repository

 The "extras" repository, which contains the jQuery integration, Firebird DB adapter, and console process forking tools, will also be migrated to GitHub, following the same pattern as for the ZF1 repo (i.e., release-1.12 branch will become the master branch on the GitHub repository, and the old subversion repository will be marked read-only). You will need to update any `svn:externals` definitions just as you would for ZF1, with the following mappings:

- "http://framework.zend.com/svn/framework/extras/trunk" becomes **"https://github.com/zendframework/zf1-extras/trunk"**
- "http://framework.zend.com/svn/framework/extras/branches/release-1.12" becomes **"https://github.com/zendframework/zf1-extras/trunk"**

### Issue Tracker

 Since Zend Framework 1 is in maintenance mode at this time, we are only addressing critical or security issues. As such, we have decided to port only open issues created since 1.12.0 was released (at the time of this writing, around 66 issues) to GitHub issues.

 The JIRA instance will be marked read-only. We will likely disable it in the near future; if we do so, we will likely provide static pages of all issues for later reference. _(This item is still to be determined.)_

 Information on the new issue tracker location will be placed on the JIRA landing page, with links to each of the ZF1, ZF1 Extras, and ZF2 repositories.

### Collaboration

 Once the cutover occurs, only the Zend team and the Community Review team will have commit rights on the GitHub repository. This means that patches will need to be submitted as [Pull Requests](https://help.github.com/articles/creating-a-pull-request). Once submitted, members of these teams, as well as the general community, can comment and provide feedback; once consensus is reached that the patch is ready, it will be merged to the repository.

 If you are unfamiliar with Git and/or GitHub, we recommend the following resources:

- [GitHub help site](https://help.github.com/), which details everything from setting up Git to creating and forking repositories, to collaborating.
- [git-scm site](http://git-scm.org), which provides a comprehensive, online book detailing basics to advanced features of Git.
- [ZF2 Git Guide](https://github.com/zendframework/zf2/blob/master/README-GIT.md), which details the typical contributor workflow; simply substitute "zf1" for "zf2" in that guide. (We will likely add this to ZF1 soon as well.)

 One note: at this point, we will only accept bug and security fixes; new features are not currently in scope for Zend Framework 1.

 **Important!** You will still need to have a Contributors License Agreement (CLA) on file for us to accept your contributions. For this reason, we recommend setting your `user.email` git configuration setting to the same email used for your CLA; with this information, we can easily look up your CLA status, which will expedite our ability to merge your pull requests/patches. To do this, simply execute the following from within your ZF1 checkout:

 
    <pre class="highlight">
    [user@server]$ git config user.email "your-email@example.com"


 Do this _prior_ to making any commits, to ensure that the commits in your patch are attributed to that email.

Timeline
--------

 The plan at this time is to mark the ZF1 subversion repository and issue tracker read-only starting Friday, 5 April 2013.

Future
------

 The repository and issues migration is the first step in a series of planned migrations. We also plan to eventually migrate our wiki to GitHub; this will allow us to offload functionality from the main ZF website, and also consolidate all development-related functionality (other than the mailing list) in a central location.

 If you have any concerns, please feel free to contact [myself](mailto:matthew@zend.com), [my team](/contact), or the [Community Review Team](mailto:zf-crteam@zend.com).
