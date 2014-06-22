About...
========

This is the main Addventure2 repository.  It aims to be a full replacement for the old
Addventure system, but with additional and improved features.


How to install?
===============
If you haven't already, get composer:
```
$ curl -s https://getcomposer.org/installer | php
```

Then, you can install the current development version of the Addventure2:
```
$ php composer.phar create-project -s dev application/addventure /path/to/addventure
```


What is the current state?
==========================
It's all in development; the very core is stable enough to be considered 99% frozen,
but much functionality relying on it isn't fully implemented yet (if at all).
For example, users can register for an account and even activate it, which enables
them to write new episodes -- but there's only a very, very simple edit form they get
without any working business logic behind it, i.e. all they write will be redirected to
``/dev/null``.
