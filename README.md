Prototype
=========

Simple prototype-based programming in PHP

[![Build Status](https://secure.travis-ci.org/tassoevan/prototype.png)](http://travis-ci.org/tassoevan/prototype)

Version: 1.0.0<br />
Release date: 2013-06-10<br />
Project state: stable<br />
Released under the MIT license

Credits: Tasso Evangelista (tassoevan@tassoevan.me)

Project description
-------------------

Prototype programming is flavor of object-oriented programming that deals with
object without categorization classes or interfaces. All object instances can
be identified and differentiated by [duck typing](http://en.wikipedia.org/wiki/Duck_typing).

Dependencies
------------

* PHP 5.3+

Documentation
-------------

All prototypes are instance of `Prototype` class. Properties can be add and removed exactly
like the native `stdClass`. The key difference between `Prototype` and `stdClass` is that
the last one don't treats callable arguments and closures very well. You can call closures
in a `Prototype` instance exactly as they are methods of object.

    <?php

    $obj = new Prototype();
    $obj->a = function($x) {
    	return $x + 2;
    };
    $obj->a(2); // returns 4
