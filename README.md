LibCompactMVC
=============

LibCompactMVC is a lightweight PHP framework including:

- ORM
- Pages / Controller Routing
- HTMLMail
- Logging
- Cached Templating
- Request Based Response Cache
- Session Handling
- Captcha Generator
- ... and a lot of different useful functionalities.

AND ALL THESE FEATURES ARE BROUGHT TO YOU BY ONLY 4K LINES OF CODE!!!

Installation
============

- Install the project to a webserver with the project root folder beeing the
  document root.
- Open man/demodb.mwb with the MySQL Workbench and forward engineer the
  database.
- Apply man/dbscripts/event_types.sql and man/dbscripts/mailpart_types.sql to
  the DB.
- Make all required changes to include/config.php.
- Have fun!


Controller
==========

Place the controller files under /application/controller/. Every controller
must be derived from CMVCController.

Methods to overwrite
--------------------

There are several methods that can be overwritten. It depends on the HTTP verb
whitch ones are executed. These are their definitions:

- protected function retrieve_data_get();
- protected function retrieve_data_post();
- protected function retrieve_data_put();
- protected function retrieve_data_delete();
- protected function retrieve_data();
- protected function run_page_logic_get();
- protected function run_page_logic_post();
- protected function run_page_logic_put();
- protected function run_page_logic_delete();
- protected function run_page_logic();
- protected function exception_handler($e);

For examplte, when a GET request occurs, the following methods are called in
order:

- protected function retrieve_data_get():
- protected function retrieve_data();
- protected function run_page_logic_get();
- protected function run_page_logic();

In case an exception occurs in one of the upper methods, exception_handler($e)
is called, where it is up to you to re-throw the exception or to handle it in
some other way.

TIP:

In most cases it is useful to use a switch or a if .. elseif .. else-construct
based on $this->param0 in these methods. This will give you more flexibility
as it handles the next path-level. Together with the .htaccess file the URI
path levels are controlled as described below:

/index.php/controller/switch-construct

A real URL would look like:

/app/home/login

This could be handled by the routing in index.php, which leads to the controller
"Home". In there (as we have a POST request, for example),
$this->run_page_logic_post() is called (among others), and thanks to your
switch-construct the login-logic within that method does what must be done.

Long things short: use switch or if .. else to enable your controller to
behave differently to one and the same HTTP verb. $this->param0 lends itself to
be used for this.

In case you do not want to use the default database access object in a specific
controller, overwrite:

- protected function dba();

and return the name of the DBA class as string. This will change the object
referenced by $this->db to an instance of the corrensponding class. Without
overwriting the dba() method, $this->db will hold an instance of
DBA_DEFAULT_CLASS, which is defined in include/config.php. In case
DBA_DEFAULT_CLASS is not defined, $this->db will point to an DbAccess instance.
For further details see section "ORM".

Methods to call
---------------

To retrieve data from the request, write:

$value = $this->request("varname");

This will give you the corresponding content in EVERY situation, even from PUT
requests. Afterwards you can be shure, that:

- The variable will be defined.
- In case, the request did not contain such a field, it will be null.

In most cases it will not be neccessary to use $this->request(). The method

$this->populate_members();

will create all "variables" received from the request as properties of your
controller. The default behaviour if this should be done automatically can
be set via the REGISTER_HTTP_VARS define in include/config.php.

The Request Based Response Cache is intended to work as follows: call

$this->rbrc($observer_headers);

to let the RBRC check whether already a response is cached based on the current
request. If also the request headers should be taken into account can be
specified by the $observe_headers (Boolean) parameter. In case a response can
be found in the cache, the controller is left immediately and the cache content
is returned to the client. This can save you load on your application and
database server in some situations.

Per default, the output is rendered when all four methods (retreive_.. and
run_page_...) have been called and no redirect has been set (more on that
later). In case you do not want the templating to do some rendering, but
instead you have some binary content in a variable and want to return this
to the client, use:

$this->binary_response($obj);

This will call clear() on the $this->view member (which represents the template
engine) and use an internal template to directly output your data.

Similarly works

$this->json_response($obj);

but it serializes the $obj to JSON before.

Member Variables
----------------

$this->db

points to an instance of your DBA class. Which one it is can be controlled by
overwriting $this->dba() (see above).

$this->log

is a Log instance. you do not need to use it directly, use the DLOG(), NLOG(),
WLOG() and ELOG() functions instead. In case you want to use logging in other
classes than the controllers, define a public member named "$log". Afterwards
above mentioned functions can be used.

To redirect application flow into another controller than requested by the
client, set

$this->redirect

to the corresponding action value described by the routing. Do NOT give the
class name here, but the value the routing maps to it.

$this->view


Routing
=======




HTMLMail
========




ORM
===






