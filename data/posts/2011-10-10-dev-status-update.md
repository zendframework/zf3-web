---
layout: post
title: 2011-10-10 Dev status update
date: 2011-10-10 21:40
update: 2011-10-10 21:40
author: Matthew Weier O'Phinney
url_author: http://mwop.net/
permalink: /blog/2011-10-10-dev-status-update.html
categories:
- blog
- dev

---

 Status update for the weeks of 27 September - 10 October 2011.

2011-09-28 IRC Meeting
----------------------

 We held our fourth IRC meeting on Wednesday, 28 September 2011. On the agenda were:

- Discussion of a Doctrine Bridge
- Discussion of the Standard Distribution
- Roadmap

 The meeting [has been summarized previously](http://framework.zend.com/wiki/display/ZFDEV2/2011-09-28+Meeting+Log). The **tl;dr** for those who don't want to click through on the link:

- Everyone agrees we'd like a Doctrine Bridge; it's up to somebody to get something concrete rolling.
- The Standard Distribution was previously defined; however, additions will be considered on a case-by-case basis.
- Expect beta 1 to drop during ZendCon.

Development Notes
-----------------

### View Convenience API

 After a [prolonged discussion](http://zend-framework-community.634137.n4.nabble.com/ZF2-Status-Update-tp3845571p3845571.html), I worked on a "View Convenience API". The goal was to simplify view usage and syntax.

 The end result is a syntax very much like ZF1, but more performant under the hood, and with easier ways to get at the various helper objects. Per a suggestion (and pull request!) from [Rob Allen](http://akrabat.com/), we now extract view variables prior to rendering the view script. Since the Variables container returns escaped versions of variables by default, this makes for very succinct syntax. You may also access variables using property overloading (e.g., `$this->foo`), via a `get()` method, or by accessing the Variables container directly. Additionally, you can now access the unescaped values using a new `raw()` method in the renderer.

 Helpers can now be accessed via method overloading, as they were in ZF1. The following will occur:

- If the helper is invocable (i.e., a functor, implementing the `__invoke()` magic method), it will be invoked with any arguments passed.
- If not invocable, the helper instance will be returned.

 Additionally, an "escape" view helper was created. This is composed into the Variables container by default, and allows specifying alternate callbacks and encoding when desired.

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php $this</span><span style="color: #007700">-></span><span style="color: #0000BB">headTitle</span><span style="color: #007700">(</span><span style="color: #0000BB">$title</span><span style="color: #007700">) </span><span style="color: #0000BB">?><br></br></span><h2><span style="color: #0000BB"><?= $title ?></span></h2><br></br><ul><br></br><span style="color: #0000BB"><?php </span><span style="color: #007700">foreach (</span><span style="color: #0000BB">$entries </span><span style="color: #007700">as </span><span style="color: #0000BB">$entry</span><span style="color: #007700">): </span><span style="color: #0000BB">?><br></br></span>    <li><span style="color: #0000BB"><?= $this</span><span style="color: #007700">-></span><span style="color: #0000BB">escape</span><span style="color: #007700">(</span><span style="color: #0000BB">$entry</span><span style="color: #007700">-></span><span style="color: #0000BB">getName</span><span style="color: #007700">()) </span><span style="color: #0000BB">?></span></li><br></br><span style="color: #0000BB"><?php </span><span style="color: #007700">endforeach </span><span style="color: #0000BB">?><br></br></span></ul><br></br></span>


 **tl;dr:** The view layer is fun again!

### Controller Convenience API

 Following a couple [short](http://zend-framework-community.634137.n4.nabble.com/ActionController-convenience-API-tp3848772p3848772.html) [threads](http://zend-framework-community.634137.n4.nabble.com/Controller-convenience-API-redux-tp3873283p3873283.html) about a controller convenience API, I implemented a few new features in the MVC controller layer:

- Controllers may be optionally "event injectible". If they are, they will be injected with the `MvcEvent` used in the `Application` object. This allows it to be tied more closely into the full request cycle.
- Controllers may be optionally "locator aware". If they implement the `LocatorAware` interface, they will be injected with the DI container or Service Locator attached to the Application. This can be used to pull out optional dependencies.
- We've created a controller-layer plugin broker, analagous to ZF1's "action helper" system. The idea behind this is to provide re-usable functionality for controllers in a light-weight fashion. Unlike ZF1, these plugins will not be workflow-aware, and will only optionally be injected with the current controller (based on presence of a `setController()` method).
- You can access controller plugins via method overloading. Unlike view helpers, method overloading only ever returns the plugin instance.

 So, in a nutshell:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #007700">class </span><span style="color: #0000BB">FooController </span><span style="color: #007700">extends </span><span style="color: #0000BB">ActionController<br></br></span><span style="color: #007700">{<br></br>    public function </span><span style="color: #0000BB">processAction</span><span style="color: #007700">()<br></br>    {<br></br>        </span><span style="color: #FF8000">// Locator-awareness for pulling conditional functionality:<br></br>        </span><span style="color: #0000BB">$form </span><span style="color: #007700">= </span><span style="color: #0000BB">$this</span><span style="color: #007700">-></span><span style="color: #0000BB">getLocator</span><span style="color: #007700">()-></span><span style="color: #0000BB">get</span><span style="color: #007700">(</span><span style="color: #DD0000">'foo-form'</span><span style="color: #007700">);<br></br>        <br></br>        </span><span style="color: #0000BB">$post </span><span style="color: #007700">= </span><span style="color: #0000BB">$this</span><span style="color: #007700">-></span><span style="color: #0000BB">getRequest</span><span style="color: #007700">()-></span><span style="color: #0000BB">post</span><span style="color: #007700">()-></span><span style="color: #0000BB">toArray</span><span style="color: #007700">();<br></br>        if (</span><span style="color: #0000BB">$form</span><span style="color: #007700">-></span><span style="color: #0000BB">isValid</span><span style="color: #007700">(</span><span style="color: #0000BB">$post</span><span style="color: #007700">)) {<br></br>            </span><span style="color: #FF8000">// do some processing<br></br>            // Redirection via a plugin:<br></br>            </span><span style="color: #007700">return </span><span style="color: #0000BB">$this</span><span style="color: #007700">-></span><span style="color: #0000BB">redirect</span><span style="color: #007700">()-></span><span style="color: #0000BB">toRoute</span><span style="color: #007700">(</span><span style="color: #DD0000">'foo-success'</span><span style="color: #007700">);<br></br>        }<br></br>    }<br></br>}<br></br></span>
    </span>


 Working controller plugins now include:

- **Url** (generates URLs from a configured router)
- **Redirect** (updates the Response object with a redirect status code an Location header; Location URL can be either a static URL or generated from the router)
- **FlashMessenger** (session-based flash messages)
- **Forward** (dispatch an additional controller)

 The last plugin enables users to dispatch an additional controller -- without requiring a dispatch loop within the `Application::run()` logic! It _does_, however, require that the controller calling it be defined as `LocatorAware`, so that it can obtain the configured controller instance.

 **tl;dr:** The Controller layer is getting a lot of good functionality.

### Zend\\Code refactoring

 Ralph worked for several weeks on a refactoring of the `Zend\Code` component. Part of this was moving `Reflection` and `CodeGenerator` under that tree, as well as ensuring the APIs were consistent across all three components (as `Zend\Code` also contains the `Scanner` component). This work was merged on 6 October 2011.

 Part of this update also included a comprehensive annotations parser. This allows us to now scan docblocks for annotations and represent them as objects, giving rise to a number of potential new workflows for ZF2.

### DI Updates

 One key driver behind annotations support was to allow using annotations to hint to the DI Compiler how to create definitions. Additionally, work was done to allow creating definitions via configuration. This allows things like:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br></span><span style="color: #007700">use </span><span style="color: #0000BB">Zend</span><span style="color: #007700">\</span><span style="color: #0000BB">Di</span><span style="color: #007700">\</span><span style="color: #0000BB">Definition</span><span style="color: #007700">\</span><span style="color: #0000BB">Annotation </span><span style="color: #007700">as </span><span style="color: #0000BB">Di</span><span style="color: #007700">;<br></br><br></br>class </span><span style="color: #0000BB">Foo<br></br></span><span style="color: #007700">{<br></br>    </span><span style="color: #FF8000">/**<br></br>     * @Di\Inject()<br></br>     */<br></br>    </span><span style="color: #007700">public function </span><span style="color: #0000BB">setEvents</span><span style="color: #007700">(</span><span style="color: #0000BB">EventCollection $events</span><span style="color: #007700">)<br></br>    {<br></br>    }<br></br>}<br></br></span>
    </span>


 which will inform the DI container that this method should be injected at construction. Additionally, you can now do things like this:

 
    <pre class="highlight"><span style="color: #000000">
    <span style="color: #0000BB"><?php<br></br>$config</span><span style="color: #007700">[</span><span style="color: #DD0000">'di'</span><span style="color: #007700">] = array(<br></br></span><span style="color: #DD0000">'definition' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'class' </span><span style="color: #007700">=> array(<br></br>    </span><span style="color: #DD0000">'Mongo' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'__construct' </span><span style="color: #007700">=> array(<br></br>            </span><span style="color: #DD0000">'server'  </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>            ),<br></br>            </span><span style="color: #DD0000">'options' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">),<br></br>        ),<br></br>    ),<br></br>    </span><span style="color: #DD0000">'MongoDB' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'__construct' </span><span style="color: #007700">=> array(<br></br>            </span><span style="color: #DD0000">'conn' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #DD0000">'Mongo'</span><span style="color: #007700">,<br></br>            ),<br></br>            </span><span style="color: #DD0000">'name' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">, <br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>            ),<br></br>        ),<br></br>    ),<br></br>    </span><span style="color: #DD0000">'MongoCollection' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'__construct' </span><span style="color: #007700">=> array(<br></br>            </span><span style="color: #DD0000">'db' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #DD0000">'MongoDB'</span><span style="color: #007700">,<br></br>            ),<br></br>            </span><span style="color: #DD0000">'name' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">true</span><span style="color: #007700">, <br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>            ),<br></br>        ),<br></br>    ),<br></br>    </span><span style="color: #DD0000">'Blog\EntryResource' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'setCollectionClass' </span><span style="color: #007700">=> array(<br></br>            </span><span style="color: #DD0000">'class' </span><span style="color: #007700">=> array(<br></br>                </span><span style="color: #DD0000">'required' </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>                </span><span style="color: #DD0000">'type'     </span><span style="color: #007700">=> </span><span style="color: #0000BB">false</span><span style="color: #007700">,<br></br>            ),<br></br>        ),<br></br>    ),<br></br>)),<br></br></span><span style="color: #DD0000">'instance' </span><span style="color: #007700">=> array(<br></br>    </span><span style="color: #DD0000">'Mongo' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'parameters' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'server'  </span><span style="color: #007700">=> </span><span style="color: #DD0000">'mongodb://localhost:27017'</span><span style="color: #007700">,<br></br>    )),<br></br> <br></br>    </span><span style="color: #DD0000">'MongoDB' </span><span style="color: #007700">=> array( </span><span style="color: #DD0000">'parameters' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'conn' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'Mongo'</span><span style="color: #007700">,<br></br>        </span><span style="color: #DD0000">'name' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'myblogdb'</span><span style="color: #007700">,<br></br>    )),<br></br> <br></br>    </span><span style="color: #DD0000">'MongoCollection' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'parameters' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'db'   </span><span style="color: #007700">=> </span><span style="color: #DD0000">'MongoDB'</span><span style="color: #007700">,<br></br>        </span><span style="color: #DD0000">'name' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'entries'</span><span style="color: #007700">,<br></br>    )),<br></br> <br></br>    </span><span style="color: #DD0000">'Blog\EntryResource' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'parameters' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'dataSource' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'CommonResource\DataSource\Mongo'</span><span style="color: #007700">,<br></br>        </span><span style="color: #DD0000">'class'      </span><span style="color: #007700">=> </span><span style="color: #DD0000">'CommonResource\Resource\MongoCollection'</span><span style="color: #007700">,<br></br>    )),<br></br> <br></br>    </span><span style="color: #DD0000">'CommonResource\DataSource\Mongo' </span><span style="color: #007700">=> array(</span><span style="color: #DD0000">'parameters' </span><span style="color: #007700">=> array(<br></br>        </span><span style="color: #DD0000">'connection' </span><span style="color: #007700">=> </span><span style="color: #DD0000">'MongoCollection'</span><span style="color: #007700">,<br></br>    )),<br></br>));<br></br></span>
    </span>


 The above will ensure that we use the provided scalar values for injection in the various constructors and setters in the definition, and still allows for object injection when required (e.g., conn, db, dataSource, connection).

 **tl;dr:** DI has become incredibly flexible and powerful!

### Cloud Infrastructure

 Enrico managed to finish testing all `Zend\Cloud\Infrastructure` functionality, with _both_ online _and_ offline tests! This gives us great confidence in the components (plural, as all infrastructure services also have related components under `Zend\Service`), and makes for a great new feature in ZF2. Enrico also backported the Infrastructure component to ZF1's trunk, in anticipation of an additional release on the ZF1 tree in the future.

### CLI Module

 [Robert Basic](http://robertbasic.com/) [ wrote to the list about a prototype CLI module](http://zend-framework-community.634137.n4.nabble.com/A-ZF2-Cli-module-tp3865659p3865659.html). The basic idea is to leverage the module architecture for executing CLI requests; routing would be performed utilizing `Zend\Console\Getopt` instead of the MVC router. Additionally, it would provide functionality for colorizing output. Overall, a solid beginning to a potential new console component!

### Presentations

 Both [Rob Allen](http://akrabat.com) and [Robert Basic](http://robertbasic.com) gave presentations during the weekend of 7 - 9 October 2011, with Robert Basic giving a general overview session on ZF2 at [Webkonf](http://webkonf.org/) and Rob Allen giving a full day tutorial at [PHPNW](http://phpnw.org.uk). Rob Allen's slides may be viewed [online](http://akrabat.com/wp-content/uploads/PHPNW11-ZF2-Tutorial.pdf).

The Future
----------

 We have an [IRC meeting this coming Wednesday, 12 Oct 2011](http://framework.zend.com/wiki/display/ZFDEV2/2011-10-12+Meeting+Agenda); please post topics and/or vote on those already posted -- and make sure you're in attendance for the discussions!

 Also, we'll be tagging **beta1** this week, in preparation for a release during ZendCon next week. If you have feedback on the MVC or DI, please let us know ASAP!