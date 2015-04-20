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
- ... and a lot of different useful functionalities.

It can also be used for PHP based REST APIs.

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




Routing
=======




HTMLMail
========




ORM
===






