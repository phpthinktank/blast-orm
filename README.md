# Blast ORM

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status](link-coveralls)

Framework agnostic data access and persistence based on Doctrine 2 DBAL.

## Install

Via Composer

``` bash
$ composer require blast/orm
```

## Example

An example can be found in this [blog post](http://bit.ly/php-orm).

## Concept

### Entities

Entity classes are a memory representations of a database entity. Entity classes are plain objects and don't need to 
match contracts. You could use prepared traits and classes of `Blast\Orm\Data` component for convenient data handling. 
It is recommended to use accessors (getters) and mutators (setters) for properties on plain objects.

Entity classes could also be instances of ![`stdClass`](http://php.net/manual/en/reserved.classes.php), 
![`ArrayObject`](http://php.net/manual/de/class.arrayobject.php) or ![`DataObject`](src/Data/DataObject.php)

#### Adapters

Blast ORM adapts an entity to access or determine data and definition.
 
### Mappers

Each entity does have it's own mapper. Mappers mediate between dbal and entity and provide convenient CRUD 
(Create, Read, Update, Delete).

### Query

The query acts as accessor to the persistence layer. The query class is hydrating data on execution and transforms the
result in a single entity class, collection of entity classes or as a raw data array.

### Repository

The repository is mediating between persistence layer and abstract from persistence or data access through mapper or query.
  
## Usage

### Configure connections

Blast ORM is using a connection facade, which is loading a connection 
collection by contract interface `Blast\Orm\ConnectionCollectionInterface` 

Add a connection. If second parameter name has been set, name is `default`.
```php
<?php

use Blast\Orm\ConnectionFacade;

ConnectionFacade::addConnection('mysql://root:root@localhost/defaultdb?charset=UTF-8');
```

Add another connection (with __UTF-8__)

```php
<?php

ConnectionFacade::addConnection('another', 'mysql://root:root@localhost/another');

```

Get connection, default connection name is always `default`

```php
<?php

//get default connection
$defaultConnection = ConnectionFacade::getConnection();

//get connection by name
$anotherConnection = ConnectionFacade::getConnection('another');
```

Swap default connection with another connection.

```php
<?php
ConnectionFacade::setDefaultConnection('another');

```

Get a connection collection instance.

```php
<?php
$connections = ConnectionFacade::__instance();

```

### Query

The query object is is providing all high level API methods of [doctrine 2 query builder](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#security-safely-preventing-sql-injection).

Create a new query for entity

```php
<?php

$query = new Query();
```

#### select

Get all posts as `Blast\Orm\Data\DataObject` containing post as `Blast\Orm\Query\Result` 

```php
<?php

$result = $query->select()->from('post', 'p')->execute();

//get data from result
$title = $result->get('title');
$content = $result->get('content');

```

Get post by id as `Blast\Orm\Query\Result`

```php
<?php

$id = 1;
$results = $query->select()
    ->from('post', 'p')
    ->where('id = :id')
    ->setParameter('id', $id)
    ->execute();

//loop results and get data 
foreach($results as $result){
    $title = $result->get('title');
    $content = $result->get('content');
}

```

#### create

Create a new entry and get number of affected rows

```php
<?php

$affectedRows = $query->insert()
    ->setValue('title', 'New Blog post')
    ->setValue('content', 'some blog content')
    ->execute();
```

#### update

Update an entry and get number of affected rows

```php
<?php

$affectedRows = $query->update()
    ->set('title', 'New Blog post')
    ->where('id = :id')
    ->setParameter('id', 1)
    ->execute();
```

#### delete

Delete entries and get number of affected rows

```php
<?php

$affectedRows = $query->delete()
    ->where('id = :id')
    ->setParameter('id', 1)
    ->execute();
```

#### Advanced execution for select

Execute query and get result as entity

```php
<?php

$post = $query->execute(EntityHydratorInterface::RESULT_ENTITY);
```

Execute query and get result as collection

```php
<?php

$posts = $query->execute(EntityHydratorInterface::RESULT_COLLECTION);
```

Execute query and get raw result as array

```php
<?php

$result = $query->execute(EntityHydratorInterface::RESULT_RAW);
```

### Working with Entities

Entity classes are independent of Blast ORM.

```php
<?php

class Post
{

}

```

#### Generic entities

Use generic entities to access tables without creating a entity class. This is useful for accessing junction tables or 
temporary tables.
 
```php
<?php

use Blast\Orm\Entities\GenericEntity;

$tableName = 'user_roles';
$userRolesJunction = new GenericEntity($tableName);
```

#### Definition

A entity does not need any definition. If the entity class does not have a definition. the definition is determined 
automatically. Declare a definition as static method or property. 

#### Table name 

Return table name as `string`

 - Default: class name without namespace, camelcase converts to underscores e.g. `App\Entities\Post` will have `post` as table name.
 - Static method: `YourEntity::getTable()` or `YourEntity::table()`
 - Static property: `YourEntity::$table`

```php
<?php

class Post
{

    /**
     * Get table name
     *
     * @var string
     */
    private static $tableName = 'post';
}

```

#### Primary key name
 
Return primary key name as `string`
 
 - Default: `id`
 - Static method: `YourEntity::getPrimaryKeyName()` or `Entity::primaryKeyName()`
 - Static property: `YourEntity::$primaryKeyName`

```php
<?php

class Post
{

    /**
     * Get primary key name
     *
     * @var string
     */
    private static $primaryKeyName = 'id';
    
}

```

#### Mapper

Return mapper class name as `string` or a instance of `Blast\Orm\MapperInterface`

 - Default: An instance of `Blast\Orm\Mapper`
 - Static method: `YourEntity::getMapper()` or `Entity::mapper()`
 - Static property: `YourEntity::$mapper`

```php
<?php

use Blast\Orm\Mapper;

class Post
{

    /**
     * Get mapper for entity
     *
     * @return string
     */
    private static $mapper = Mapper::class;
}

```

#### Adapters

Adapters grant access to data and definition, even if your entity class does not have definitions at all.

```php
<?php

use Blast\Orm\Mapper;

$post = new Post;

$postAdapter = new EntityAdapter($post);

```

Get table name

```php
<?php
$tableName = $postAdapter->getTableName();

```

Get primary key name

```php
<?php
$primaryKeyName = $postAdapter->getPrimaryKeyName();

```

Get entities mapper

```php
<?php
$mapper = $postAdapter->getMapper();

```

### Mapper

#### Get mapper

```php
<?php

use Blast\Orm\Mapper;

$mapper = new Mapper($post);

```

#### find

Fetch one entry by primary key

```php
<?php

$post = $mapper->find(1);

```

#### select

Custom select query

```php
<?php

$first = $mapper->select()->where('title = "Hello world"')->setMaxResults(1)->execute(EntityHydratorInterface::RESULT_ENTITY);
```

#### delete

`delete` expects an primary key or an array of primary keys.

Delete onw entry

```php
<?php

$repository->delete(1);
```

Delete many entries

```php
<?php

$repository->delete([1, 2]);
```

### Repository

#### all

Fetch all entries

```php
<?php
//find all posts as collection
$posts = $repository->all();

foreach($posts as $post){

    //do something

}

```

#### save

Save is determining whether to create or update an entity, but you could also use `update` or `create` instead of save.

```php
<?php

$post->title = 'Hello World';
$post->content = 'Some content about hello world.';
$post->date = new \DateTime();

//create or update entity
$repository->save($post);
```

## Further development

Please visit our [milestones](https://github.com/phpthinktank/blast-orm/milestones)

## Change log

Please see [CHANGELOG](ChangeLog-0.1.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email <mjls@web.de> instead of using the issue tracker.

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
[link-coveralls]: https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=1.0.x-dev
