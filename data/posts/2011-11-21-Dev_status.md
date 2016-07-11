---
layout: post
title: 2011-11-21 Dev status update
date: 2011-11-21
author: Matthew Weier O'Phinney
url_author: https://mwop.net/
permalink: /blog/2011-11-21-dev-status-update.html
categories:
- blog
- dev

---

 What's going on in ZF2 development this week?

 A few things!

<!--more-->

Repository Changes!
-------------------

 We've now moved our canonical repository to GitHub, which is where most folks were forking and doing development anyways. The reasons were several:

- The main reason for self-hosting was to make checking CLA status simpler. As ZF2 development no longer requires a CLA, this reason is gone.
- ACLs for providing commit access are easier to manage on GitHub, and do not require us to first receive SSH keys from contributors.
- Using GitHub directly simplifies the pull request process. When self-hosting, we would merge and push to the canonical repo, and then need to manually close the pull request; using GitHub, PRs are automatically closed when the code is merged. Additionally, because the mirroring only occurred a few times per day, it was not immediately evident on GitHub when a change was available to test.
- There was often confusion by developers on where the most current changes were, particularly if they forked from the GitHub repository.

 The practical upshot is that if you had git.zendframework.com as a remote on your repository, you should remove it. If you didn't have a github.com/zendframework/zf2 remote, you should add one. The [ZF2 Git Guide](http://bit.ly/zf2gitguide) details adding the remote.

MVC Developments
----------------

 Two big things occurred in the MVC this week.

 First, we did some re-thinking of the duties of the Module Manager. Previously, it was responsible for merging configuration and firing module initialization. A recommended part of module initialization was to initialize autoloading.

 What we noticed was:

- Configuration merging was getting more complex, and we were getting potentially incompatible feature requests.
- Our modules were getting hard dependencies on things like autoloaders, and were also introducing a lot of boiler-plate code.
- We had no clear path to how we might cache autoloading configuration for production.
- We were noticing more and more places where we might want to loop through the loaded modules in order to trigger methods of interest.

 So, the solution was to change things: `Zend\Module\Manager::loadModule()` now triggers a "loadModule" event, passing it the newly created module. This allows listeners to react to modules real-time as they're loaded.

 This also meant that the code for the following actions could be moved to listeners:

- Autoloading
- Configuration loading
- Module "initialization"

 This allows modules that look like this:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #007700">namespace </span><span style="color: #0000BB">Blog</span><span style="color: #007700">;<br></br><br></br>use </span><span style="color: #0000BB">InvalidArgumentException</span><span style="color: #007700">,<br></br>    </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">EventManager</span><span style="color: #007700">\</span><span style="color: #0000BB">StaticEventManager</span><span style="color: #007700">,<br></br>    </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Module</span><span style="color: #007700">\</span><span style="color: #0000BB">Consumer</span><span style="color: #007700">\</span><span style="color: #0000BB">AutoloaderProvider</span><span style="color: #007700">;<br></br><br></br>class </span><span style="color: #0000BB">Module </span><span style="color: #007700">implements </span><span style="color: #0000BB">AutoloaderProvider<br></br></span><span style="color: #007700">{<br></br>    public function </span><span style="color: #0000BB">init</span><span style="color: #007700">()<br></br>    {<br></br>        </span><span style="color: #0000BB">$events </span><span style="color: #007700">= </span><span style="color: #0000BB">StaticEventManager</span><span style="color: #007700">::</span><span style="color: #0000BB">getInstance</span><span style="color: #007700">();<br></br>        </span><span style="color: #0000BB">$events</span><span style="color: #007700">-></span><span style="color: #0000BB">attach</span><span style="color: #007700">(</span><span style="color: #DD0000">'bootstrap'</span><span style="color: #007700">, </span><span style="color: #DD0000">'bootstrap'</span><span style="color: #007700">, array(</span><span style="color: #0000BB">$this</span><span style="color: #007700">, </span><span style="color: #DD0000">'bootstrap'</span><span style="color: #007700">));<br></br>    }<br></br><br></br>    public function </span><span style="color: #0000BB">getAutoloaderConfig</span><span style="color: #007700">()<br></br>    {<br></br>        return array(<br></br>            </span><span style="color: #DD0000">'Zend\Loader\ClassMapAutoloader' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #0000BB">__DIR__ </span><span style="color: #007700">. </span><span style="color: #DD0000">'/autoload_classmap.php'<br></br>            </span><span style="color: #007700">),<br></br>        );<br></br>    }<br></br><br></br>    public function </span><span style="color: #0000BB">getConfig</span><span style="color: #007700">()<br></br>    {<br></br>        return include </span><span style="color: #0000BB">__DIR__ </span><span style="color: #007700">. </span><span style="color: #DD0000">'/configs/module.config.php'</span><span style="color: #007700">;<br></br>    }<br></br>    <br></br>    </span><span style="color: #FF8000">/* ... */<br></br></span><span style="color: #007700">}<br></br></span>
    </span>


 This looks about the same as before! The differences are:

- `getAutoloaderConfig()` can simply return an array of options to pass to `Zend\Loader\AutoloaderFactory`. This allows us to obtain a fully configured autoloader at the end -- which we will eventually be able to cache, and thus eliminate the need for the autoloader listener.
- `getConfig()` is no longer called directly by the module manager, but instead by a listener. Again, this will make it possible to cache the full application configuration.
- `init()` is called by a listener now.

 In other words, the differences are largely under the hood. But those differences mean that it's trivially easy to develop your own listeners to tie into the module loading process in order to do interesting things -- all without needing to touch or extend the main module manager.

Application Configuration
-------------------------

 Several people indicated that much as they like module configuration merging, they weren't liking the solutions for overriding configuration at the application level. The solutions to date have been:

- Register the module with overrides last -- for instance, your Application module.
- Create a configuration-only module that registers last.

 The consensus was that a module for simply providing configuration overrides "sucks", and that using the "Application" module sometimes was problematic (especially for purposes of registering view script paths).

 The solution was to add some logic to provide application-level configuration. This was added to the configuration listener, and allows for you to specify a directory with configuration (ala "conf.d" style configuration now commonly used across a number of \*nix distributions); this configuration is then merged after module configuration is aggregated. Your `index.php` would then contain something like the following:


    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$moduleManager </span><span style="color: #007700">= new </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Module</span><span style="color: #007700">\</span><span style="color: #0000BB">Manager</span><span style="color: #007700">(</span><span style="color: #0000BB">$appConfig</span><span style="color: #007700">[</span><span style="color: #DD0000">'modules'</span><span style="color: #007700">]);<br></br></span><span style="color: #0000BB">$listenerOptions </span><span style="color: #007700">= new </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Module</span><span style="color: #007700">\</span><span style="color: #0000BB">Listener</span><span style="color: #007700">\</span><span style="color: #0000BB">ListenerOptions</span><span style="color: #007700">(</span><span style="color: #0000BB">$appConfig</span><span style="color: #007700">[</span><span style="color: #DD0000">'module_listener_options'</span><span style="color: #007700">]);<br></br></span><span style="color: #0000BB">$moduleManager</span><span style="color: #007700">-></span><span style="color: #0000BB">setDefaultListenerOptions</span><span style="color: #007700">(</span><span style="color: #0000BB">$listenerOptions</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$moduleManager</span><span style="color: #007700">-></span><span style="color: #0000BB">getConfigListener</span><span style="color: #007700">()-></span><span style="color: #0000BB">addConfigGlobPath</span><span style="color: #007700">(</span><span style="color: #0000BB">dirname</span><span style="color: #007700">(</span><span style="color: #0000BB">__DIR__</span><span style="color: #007700">) . </span><span style="color: #DD0000">'/config/autoload/*.config.php'</span><span style="color: #007700">);<br></br></span><span style="color: #0000BB">$moduleManager</span><span style="color: #007700">-></span><span style="color: #0000BB">loadModules</span><span style="color: #007700">();<br></br></span>
    </span>


 Hopefully, these changes will simplify how app-specific configuration is managed.

Beta2 is coming!
----------------

 Beta2 is coming up soon! We're hoping to have it ready by the end of the month. The components currently under development for beta2 include:

- Zend\\Log
- Zend\\Cache
- Zend\\Mail

 Cache is mostly complete and needs some review and input regarding its usage of the EventManager. Log and Mail are currently under development. We encourage you to reach out on the #zftalk.2 IRC channel on Freenode or the zf-contributors mailing list if you would like to assist with testing or development of these components.

IRC meeting this week
---------------------

 We have another [IRC meeting this week](http://framework.zend.com/wiki/display/ZFDEV2/2011-11-23+Meeting+Agenda). Follow the link to see the agenda -- and add to it if you want to discuss additional topics.
