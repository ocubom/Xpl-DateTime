XPL DateTime
============

_XPL DateTime_ component attempts to improve the [Date and Time Extension][]
shipped with PHP. It adds some enhancements while is almost fully compatible
with the default extension. It is also more [ISO 8601][] compilant.


| Version    | Status |
| ---------- | ------ |
| dev-master | [![Build Status](https://travis-ci.org/ocubom/Xpl-DateTime.png?branch=master)](https://travis-ci.org/ocubom/Xpl-DateTime) |


Documentation
-------------

The component may be 100% compatible with standard [Date and Time Extension][]
classes but the names used are based on [ISO 8601][].

Just use like the standard PHP extension classes.

Installation
------------

Just use [composer][] to add the dependency:

```
composer require xpl/datetime:dev-master
```

Or add the dependecy manually:

1.  Update ``composer.json`` file with the lines:

    ```
    {
        "require": {
            "xpl/datetime": "dev-master"
        }
    }
    ```

2.  And update the dependencies:

    ```
    composer update xpl/datetime
    ```

Authorship
----------

Current maintainer:

* [Oscar Cubo Medina](http://github.com/ocubom/ "@ocubom projects").
  Twitter: [@ocubom](http://twitter.com/ocubom/ "@ocubom on twitter").

This component starts as an utility class used on an internal project. This is
a fork of the idea rewrited from scratch.

Copyright and License
---------------------

_XPL DateTime_ is licensed under the MIT License – see the [`LICENSE`][0] file
for details.

If you did not receive a copy of the license, contact with the author.

[0]: http://github.com/ocubom/Xpl-DateTime/blob/master/LICENSE
    "XPL DateTime license file"


[Composer]: http://getcomposer.org/
    "Composer Dependency Manager for PHP"

[Date and Time Extension]: http://php.net/manual/refs.calendar.php
    "PHP Date and Time Related Extensions"

[ISO 8601]: http://en.wikipedia.org/wiki/ISO_8601
    "Data elements and interchange formats – Information interchange – Representation of dates and times (Wikipedia)"
