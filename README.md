# Prototype [![Build Status](https://travis-ci.org/tassoevan/prototype.png?branch=master)](https://travis-ci.org/tassoevan/prototype)

*Simple prototype-based programming in PHP*

Version: 1.2.2  
Release date: 2013-07-19  
Project state: release candidate  
Released under the MIT license

Credits: Tasso Evangelista ([tassoevan@tassoevan.me](mailto:tassoevan@tassoevan.me), [@tassoevan](http://twitter.org/tassoevan))

## Project description

Prototype programming is flavor of object-oriented programming that deals with object without categorization classes or interfaces. All object instances can be identified and differentiated by [duck typing](http://en.wikipedia.org/wiki/Duck_typing). Basically, properties and methods are put in objects in runtime, instead of previously declared in classes;  inheritance is obtained by object cloning and property/method dynamic insertion. Thus parent objects act as *prototypes* for their children.

**Obs.:** This allow you to quickly code, once you do structural changes in your source code *while* you're programming. However, although good for small projects, it can generate unmaintainable applications. It's recommmended that you use prototyping to prototype application modules first and implement an equivalent traditional class-based structure after.

## Dependencies

* PHP 5.3+

## Installation

All you need is in `classes/Prototype.php` class file, but the recommended way to install this package is through [Composer](http://getcomposer.org/).

Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
    "minimum-stability": "dev",
    "require": {
		"tasso/prototype": "*"
    }
}
``` 

## Documentation

### Basic usage

All prototypes are instances of `Prototype` class. Properties can be add and removed exactly like the native `stdClass`. The key difference between `Prototype` and `stdClass` is that
the last one don't treats callable arguments and closures very well. You can call closures in a `Prototype` instance exactly as they are methods of object.

```php
<?php

$obj = new Prototype();
$obj->a = function($x) {
	return $x + 2;
};

$obj->a(2); // returns 4

```

Also, you can use `Prototype` instances as closures too:

```php
<?php

$obj = new Prototype(function($x) {
     return $x + 2;
});

$obj(2); // returns 4

```

### Wrappers

All properties and closures assigned to `Prototype` instances without the usage of *wrappers* are treated like wrapped by `Prototype::normal()` method. So, this code

```php
<?php

$obj->a = 2;

```

is treated in the same way of this code:

```php
<?php

$obj->a = Prototype::normal(2);

```

`Prototype::normal()` wrapper handle properties *normally*, i.e., they can be assigned, evaluated and called (if they are closures) exactly in the same way that PHP variables are.

`Prototype::dynamic()` wrapper offers the possibility of handle *dynamic* properties, since the handled value are not stored, but passed through closures that you define.

```php
<?php

$obj->a = Prototype::dynamic(function() {
        return "foo";
    },
    function($value) {
        echo "Do you want to set $value to this property?";
    },
    function() {
        echo "Called this property with " . func_num_args() . " arguments.";
    });
    
echo $obj->a;           // outputs "foo"
$obj->a = 3;            // outputs "Do you want to set 3 to this property?"
$obj->a('John', 'Doe'); // outputs "Called this property with 2 arguments."

```

`Prototype::dynamic()` can be useful to represent *service providers and containers*.

`Prototype::lazy()` wrapper gives to property the ability to be *lazy-loaded*: only at first try to get property value or call it, a closure is called to generate a value that will be stored. After that, the property behaves exactly like a `Prototype::normal()` wrappered property.

```php
<?php

$obj->db = Prototype::lazy(function() use($myDSNString) {
	// connected only when $obj->db will be used
    return new PDO($myDSNString);
});

// if $poked is true, will connect to database and perform query
if ( $poked )
    $obj->db->exec('UPDATE poke_registry SET pokes = pokes+1');

```

### Additional features

* `Prototype::closure($callable)`: transforms any callable in a closure that can be passed to `Prototype` instances
* `Prototype::data(Prototype $obj)`: creates an array with all data that your prototype stores or *generates*: that means that `Prototype::dynamic()` and `Prototype::lazy()` calls will be performed. This static method was implemented attending the fact that *prototypes can't be serialized*, once they store closures. Further it can be reviewed.

## Contributions

You are free to fork and contribute to this project.
