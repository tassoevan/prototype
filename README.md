# Prototype [![Build Status](https://travis-ci.org/tassoevan/prototype.png?branch=master)](https://travis-ci.org/tassoevan/prototype)

*Simple prototype-based programming in PHP*

Prototype programming is a flavor of object-oriented programming that deals with objects without categorization provided
by classes or interfaces. All objects can be identified and differentiated by
[duck typing](http://en.wikipedia.org/wiki/Duck_typing). Basically, properties and methods are put in objects on
runtime, instead of previously declared in classes; inheritance is obtained by object cloning and dynamic insertion of
properties. Thus parent objects act as *prototypes* for their children.

This allow you to quickly code, once you do structural changes in your source code *while* you're programming. However,
although good for small projects, it can generate unmaintainable applications. It's recommmended that you use
prototyping to prototype application modules first and implement an equivalent traditional class-based structure after.

## Installation

The recommended way to install this package is through [Composer](https://getcomposer.org/). Create a common
`composer.json` file and run:

```sh
$ composer require tassoevan/prototype
```

## Documentation

### Basic usage

All prototypes are instances of `TassoEvan\Prototype\Prototype` class. Properties can be added and removed exactly like
the native `stdClass`. The key difference between `Prototype` and `stdClass` is that the last one doesn't handle
callable arguments and closures well. You can call closures in a `Prototype` instance exactly as they are methods.

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();
$obj->a = function($x) {
	return $x + 2;
};

$obj->a(2); // returns 4

```

Also, you can use `Prototype` instances as closures too:

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype(function($x) {
     return $x + 2;
});

$obj(2); // returns 4

```

### Property wrappers

All properties assigned to `Prototype` instances are wrapped in instances of abstract class
`TassoEvan\Prototype\Property`. It can be done explicitly instantiating these objects or calling static methods from
`TassoEvan\Prototype\Prototype`, implicitly wrapping *normal* properties by simple assignment though.

Explicitly, instanciating `TassoEvan\Prototype\Property`:

```php
<?php
use TassoEvan\Prototype\Prototype;
use TassoEvan\Prototype\NormalProperty;

$obj = new Prototype();
$obj->a = new NormalProperty('My value');

echo $obj->a; // outputs 'My value'

```

Explicitly, via static methods of `TassoEvan\Prototype\Prototype`:

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();
$obj->a = Prototype::normal('My value');

echo $obj->a; // outputs 'My value'

```

Implicitly (`TassoEvan\Prototype\NormalProperty` only):

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();
$obj->a = 'My value';

echo $obj->a; // outputs 'My value'

```

#### `TassoEvan\Prototype\NormalProperty`

A *normal* property can be assigned, evaluated and called (if it is callable) as any variable or object public property
in PHP.

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();
$obj->a = 'My value';

echo $obj->a; // outputs 'My value'

$obj->a = 'My new value';

echo $obj->a; // outputs 'My new value'

```

#### `TassoEvan\Prototype\ReadOnlyProperty`

A *read only* property can't be overwritten. In strict mode, a exception will be thrown when a new assignment is done.

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();
$obj->a = Prototype::readOnly('My value'); // non-strict

echo $obj->a; // outputs 'My value'
$obj->a = 'My new value';
echo $obj->a; // outputs 'My value'

$obj->b = Prototype::readOnly('My value', true); // strict

echo $obj->b; // outputs 'My value'
$obj->b = 'My new value'; // a `UnexpectedValueException`

```

#### `TassoEvan\Prototype\LazyProperty`

A *lazy* property has the ability to be *lazy-loaded*: at first try to get a value (or call it), a callable named
*loader* is invoked to generate the property value, that will be stored. After loaded, the property behaves like a
normal property.

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();

$obj->db = Prototype::lazy(function() use($myDSNString) {
    // connected only when used
    return new PDO($myDSNString);
});

$obj->db->exec('UPDATE poke_registry SET pokes = pokes+1'); // connects to database and performs a query

```

#### `TassoEvan\Prototype\ProxyProperty`

<!-- TODO -->

#### `TassoEvan\Prototype\DynamicProperty`

A *dynamic* property doesn't have a stored value: you should define a getter and a setter callables to offer some value.
Can be useful to represent or be a facade to service providers and containers.

```php
<?php
use TassoEvan\Prototype\Prototype;

$obj = new Prototype();

$obj->a = Prototype::dynamic(function() {
        return "foo";
    },
    function($value) {
        echo "Do you want to set {$value} to this property?";
    });

echo $obj->a;           // outputs "foo"
$obj->a = 3;            // outputs "Do you want to set 3 to this property?"

```

### Additional features

* `Prototype::closure($callable)`: transforms any callable in a closure that can be passed to `Prototype` instances
* `Prototype::data(Prototype $obj)`: creates an array with all data that your prototype stores or *generates*: that means that `Prototype::dynamic()` and `Prototype::lazy()` calls will be performed. This static method was implemented attending the fact that *prototypes can't be serialized*, once they store closures. Further it can be reviewed.

## Contributions

You are free to fork and contribute to this project.
