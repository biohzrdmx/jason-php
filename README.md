# jason-php

A convenient wrapper for JSON documents

Simple to use wrapper to make it even easier to work with JSON documents, loading, saving, accessing and manipulating them.

### Basic usage

First require `biohzrdmx/jason-php` with Composer.

There are four factory methods to create a `Document` instance from various sources:

- `fromArray` - From an array
- `fromString` - From a JSON-encoded string
- `fromFile` - From a JSON-encoded file
- `fromStream` - From a JSON-encoded stream

```php
$document = Document::fromFile('/home/your-user/credentials.json');
```

Also you may use the default `constructor` which takes either an `array` or a `string`.

```php
$payload = [
    'status' => 'accepted',
    'id' => '1234567890',
];
$document = new Document($payload);
```

There are also four output methods to convert the `Document`:

- `toArray` - To an array
- `toString` - To a JSON-encoded string
- `toFile` - To a JSON-encoded file
- `toStream` - To a JSON-encoded stream

```php
$document->toStream($stream_handle);
```

#### Manipulation

To access a `Document` contents you can use the `get` method, which uses _dot notation_ to give you an easy to use interface:

```json
{
    "order": {
        "id": 1234567890,
        "status": "pending",
        "invoice": null,
        "customer": {
            "name": "Bobby Tables"
        },
        "metadata": []
    }
}
```

```php
$customer_name = $document->get('order.customer.name');
```

You can specify a default value in case the item doesn't exist:

```php
$customer_type = $document->get('order.customer.type', 'guest');
```

It also includes a handy `has` method to check for the existence of some value:

```php
if ( $document->has('metadata.processor') ) {
    // Do something
}
```

And you may also build your `Document` from the ground up with the `set` method:

```php
$document = new Document();
$document->set('order' => ['id': 1234567890]);
$document->set('order.status' => 'pending');
$document->set('order.customer' => ['name' => 'Bobby Tables']);
$document->toString(true);
```

```json
{
    "order": {
        "id": 1234567890,
        "status": "pending",
        "customer": {
            "name": "Bobby Tables"
        }
    }
}
```

### Licensing

This software is released under the MIT license.

Copyright © 2024 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Credits

**Lead coder:** biohzrdmx &lt;[github.com/biohzrdmx](http://github.com/biohzrdmx)&gt;
