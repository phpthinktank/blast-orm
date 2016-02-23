# Blast orm

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status](https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=1.0.x-dev)

Framework agnostic data access and persistence based on Doctrine 2 DBAL.

## Install

Via Composer

``` bash
$ composer require blast/orm
```

## Concept

### Entities

Entity classes are representations of a database objects like a table or view. There is __no need__ to extend any other 
class. 

There is __no need__ to extend any other class. Entity classes are basically plain classes and could be designed as you 
like. You could use prepared traits and classes of `Blast\Orm\Data` package to be more efficient.

Entity classes could also be instances of `stdClass`, [`ArrayObject`]() or [`DataObject`](src/Data/DataObject.php) 

### Query

The query acts as accessor to the persistence layer. Instead of to use a big data mapper, Blast ORM uses a query class. 
The query class is able to process the result as raw array or a map the result to a single entity class or a collection 
of entity classes.

### Repository

The repository is acting as convenient accessor to persistence layer and is using an entity class as database object 
representation and the query as accessor to the persistence layer. 
  
## Usage

### Initialize

Create a factory with [container-interopt](https://github.com/container-interop/container-interop) compatible Container e.g. [league/container](https://github.com/thephpleague/container) and connection data.

```php
<?php

use Blast\Db\Factory;
use League\Container;


Factory::create(new Container(), [
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);

```

### Working with Entities

Entity class needs as a minimum required definition the `table` and `primaryKeyName`.
  
Pass `table` as a static method or static property as follows
 
 - `Entity::getTable()`
 - `Entity::table()`
 - `Entity::$table`

Similar to `table` pass `primaryKeyName` as follows
 
 - `Entity::getPrimaryKeyName()`
 - `Entity::primaryKeyName()`
 - `Entity::$primaryKeyName`

```php
<?php

class Post
{

    /**
     * Get table for model
     *
     * @return string
     */
    public static function getTable()
    {
        return 'post';
    }
}

```

#### Accessor with `Blast\Orm\Data\AccessorTrait`

Accessing data with `get`

```php
<?php

$content = $post->get('content');
```

Accessing data by property, which is internal calling `get`

```php
<?php

$content = $post->content;

//or with magic accessor
$content = $post->getContent();
```

Manipulating accessor with `value.get` event, for example passing html markup to post title.

```php
<?php

$post->getEmitter()->addListener($post::VALUE_GET, function(ValueEvent $event){
    if($event->getKey() === 'same'){
        $event->setValue(sprintf('<h1>%s</h1>', $event->getValue()));
    }
});
```

Events could als attached on `AbstractEntity::configure`

#### Mutator

Passing data with `set` to entity

```php
<?php

$post->set('content', 'A lot of content');
```

Accessing data by property, which is internal calling `get`

```php
<?php

$post->content = 'A lot of content';

//or with magic accessor
$post->setContent('A lot of content');
```

Manipulating mutator with `value.set` event, for example stripping html markup from post content.

```php
<?php

$post->getEmitter()->addListener($post::VALUE_SET, function(ValueEvent $event){
    if($event->getKey() === 'content'){
        $event->setValue(strip_tags($event->getValue()));
    }
});
```

Events could als attached on `AbstractEntity::configure`

#### Relations

Blast Db is providing a way to connect entities with relations.

Relation types: 

- `HasMany`
- `HasOne`
- `BelongsTo`
- `ManyThrough`

All relations do have the same architecture.

Creating a new relation

```php
<?php

//...previous code

use Blast\Db\Orm\Relations\BelongsTo

class Post extends AbstractEntity
{

    /**
     * Configure entity
     */
    public function configure()
    {
        //... previous configuration
        $this->addRelation(new BelongsTo($this, new User(), 'id'));
    }
}
```

Get related entity from entity like any field

```php
<?php

$user = $post->user;

//or with accssor
$user = $post->get('user');

//or with magic accessor
$user = $post->getUser();
```

Update entity with related entity

```php
<?php

$post->user->name = "fred";
$mapper->save($post);
```

Add a new related entity

```php
<?php

$user = new User();
$user->name = "Peter Pan";

$post->user = $user;
$mapper->save($post);
```

If an entity is deleted, it's related entities will not deleted or updated!

Access relation object

```php
<?php

$userRelation = $post->getRelation('user');
```

Check if relation exists

```php
<?php

$hasRelation = $post->hasRelation('user');
```

### Query

The query object is the foundation of accessing and persisting data. It is providing all high level API methods of 
[doctrine 2 query builder](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/query-builder.html#high-level-api-methods).

Create a new query for entity

```php
<?php

$query = new Query($post);
```

Do a complex query

```php
<?php
$query->select() // string 'u' is converted to array internally
   ->from($post->getTable()->getName(), 'p')
   ->where($qb->expr()->orX(
       $query->expr()->eq('p.id', 1),
       $query->expr()->like('p.title', "Hello%")
   ))
   ->orderBy('p.date', 'ASC'));
```

Execute query and get result

```php
<?php

$result = $query->execute();
```

Execute query and get result as entity

```php
<?php

$post = $query->execute(Query::RESULT_ENTITY);
```

Execute query and get result as collection

```php
<?php

$posts = $query->execute(Query::RESULT_COLLECTION);
```

Execute query and get raw result as array

```php
<?php

$result = $query->execute(Query::RESULT_RAW);
```

### Object relational mapper - ORM

ORM is utilizing convenient CRUD (create, read, update, delete) methods of accessing and persisting data. It uses previous introduced Query as foundation
and acts as addition for he query object instead of a foundation for database communication.

#### Get mapper

Get mapper from entity.

```php
<?php

$post = new Post;
$mapper = $post->getMapper();
```

Create new mapper with entity

```php
<?php

use Blast\Db\Orm;

$mapper = new Mapper($post);

```

#### Save data

Save is determining whether to create or update an entity, but you could also use update or create instead of save. All work as the same!

```php
<?php

$post->title = 'Hello World';
$post->content = 'Some content about hello world.';
$post->date = new \DateTime();

//create or update entity
$mapper->save($post);
```

Save many entries as array

```php
<?php

$mapper->save([$post, $post2]);
```

Save many entries from collection, e.g. from manipulated previous result

```php
<?php

use Blast\Db\Entity\Collection;
 
$collection = new Collection([$post, $post2]);

$mapper->save($collection);
```

#### Delete data

Delete is following the same behaviour like save, create or update, but is removing instead of manipulating data

Delete onw entry

```php
<?php

$mapper->delete($post);
```

Delete many entries

```php
<?php

$mapper->delete([$post, $post2]);
```

Delete a collection of entries

```php
<?php

use Blast\Db\Entity\Collection;
 
$collection = new Collection([$post, $post2]);
$mapper->delete($collection);
```

#### Working with data

Fetch one entry by primary key

```php
<?php

$post = $mapper->find(1);

```

Fetch one entry by query

```php
<?php

$first = $mapper->select()->where('title = "Hello world"')->setMaxResults(1)->execute(Query::RESULT_ENTITY);
```

Fetch all entries

```php
<?php
//find all posts as collection
$posts = $mapper->all();

foreach($posts as $post){

    //do somesthing

}

```

### Data conversion

Convert a collection or entity into an array or json with `toArray` and `toJson`;

## Further development

Please visit our [milestones](https://github.com/phpthinktank/blast-orm/milestones)

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Marco Bunge][link-author]
- [All contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/blast/orm.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/phpthinktank/blast-orm/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/blast/orm.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/blast/orm
[link-travis]: https://travis-ci.org/phpthinktank/blast-orm
[link-downloads]: https://packagist.org/packages/blast/orm
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
