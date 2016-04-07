# Blast ORM

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-coveralls]][link-coveralls]

Framework agnostic data access and persistence based on Doctrine 2 DBAL.

## Features

 - Data and relation mapper _since 0.1_
 - Decoupled entities as POPO's (Plain-old PHP objects) _since 0.3_
 - Auto-Suggesting entity definition as well as configure custom definition _since 0.5_
 - Data hydration to entity and vice versa _since 0.5_
 - Repository contracted to a single entity class _since 0.5_
 - Integration of fields from definition _since 0.5.2_
 - Field type aware converting _since 0.5.2_

## Upcoming features

 - Unit of Work - Entity-aware transactions
 - Identity map - Reduce load by storing entity by primary key 
 - Saveable relations
 - Integration of indexes from definition
 
## Install

### Using Composer

Blast ORM is available on [Packagist](https://packagist.org/packages/blast/orm) and can be installed using [Composer](https://getcomposer.org/). This can be done by running the following command or by updating your `composer.json` file.

```bash
composer require blast/orm
```

composer.json

```json
{
    "require": {
        "blast/orm": "~1.0"
    }
}
```

Be sure to also include your Composer autoload file in your project:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

### Downloading .zip file

This project is also available for download as a `.zip` file on GitHub. Visit the [releases page](https://github.com/phpthinktank/blast-orm/releases), select the version you want, and click the "Source code (zip)" download button.

### Requirements

The following versions of PHP are supported by this version.

* PHP >= 5.5.9
* PHP 5.6
* PHP 7.0
* HHVM

## Example

An example can be found in this [blog post](http://bit.ly/php-orm).

## Concept

### Entities

An entity object is an in-memory representations of a database entity. Entity object are plain objects (aka POPO). 
It is recommended to use accessors (getters) and mutators (setters) for properties on plain objects.
 
#### Provider
 
The provider is a link between independent data entity and data access. The provider is also able to hydrate data to 
entity object and extract data from entity object

#### Definition

Definition managing entity meta and schema specific configuration. Definition could be passed to mapper or provider, 
instead of an entity.
 
### Mappers

Each entity does have it's own mapper. A mapper is determined by the entity provider. Mappers mediate between dbal 
and entity and provide convenient CRUD (Create, Read, Update, Delete). In addition to CRUD, the mapper is also delivering 
convenient methods to to work with relations.

### Query

The query acts as accessor to the persistence layer. The query class is hydrating data on execution and transforms the
result into a single entity class or `\ArrayObject` as fallback, a collection of entity classes as `\SplStack` or as a 
raw data array. Furthermore the query is able to receive hydration options to control the result. Create, delete and 
update are always returning a numeric value!

### Repository

The repository is mediating between persistence layer and abstract from persistence or data access through mapper or query. 
Blast orm is just delivering a `Blast\Orm\RepositoryInterface` for completion!
  
## Usage

### Connections

Blast ORM is managing all connections with `\Blast\Orm\ConnectionManager`. You are able to create connections directly 
or add connections to manager cache and access them later on.

#### Direct access

Create a new connection

```php
<?php

use Blast\Orm\ConnectionManager;

$connection = ConnectionManager::create('mysql://root:root@localhost/defaultdb?charset=UTF-8');
```

#### Stored and named connections

##### Connection manager

The connection manager stores all connection in it's own cache by name.

In some cases you would like to use a new connection manager instance, e.g. in a separate container.

```php
<?php

use Blast\Orm\ConnectionManager;

$connections = new ConnectionManager();

```

##### Work with connections

Add a connection. If second parameter name is not set, name is `default`.
```php
<?php

$connections->add('mysql://root:root@localhost/defaultdb?charset=UTF-8', 'myconnectionname');
```

Get connection, default connection name is always `default`

```php
<?php

//get default connection
$defaultConnection = $connections->get();

//get connection by name
$anotherConnection = $connections->get('another');
```

Swap default connection with another connection.

```php
<?php
$connections->setDefaultConnection('another');

```

### Query

The query object is is providing high level API methods of [doctrine 2 query builder](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#security-safely-preventing-sql-injection).

The query is automatically determining current active connection from connection manager.

```php
<?php

use Blast\Orm\Query;

$query = new Query();
```

or create a new query with a custom connection

```php
<?php

use Blast\Orm\Query;
use Blast\Orm\ConnectionManager;

$query = new Query(ConnectionManager::create('mysql://root:root@localhost/acme'));
```

or create a new query for an entity

```php
<?php

use Blast\Orm\Query;

$query = new Query(null, Post::class);
```

Custom connection for the query

```php
<?php

use Blast\Orm\ConnectionManager;

$query->setConnection(ConnectionManager::create('mysql://root:root@localhost/acme'));

```

Custom query builder

```php
<?php

$query->setBuilder($connection->createQueryBuilder());

```

#### select

Get all posts as collection `\SplStack` containing post as `\ArrayObject` 

```php
<?php

$result = $query->select()->from('post', 'p')->execute();

//get data from result
$title = $result['title'];
$content = $result['content'];
```

Get post by id as `\ArrayObject`

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
    $title = $result['title'];
    $content = $result['content'];
}

```

#### create

Create a new entry and get number of affected rows

```php
<?php

$affectedRows = $query->insert('post')
    ->setValue('title', 'New Blog post')
    ->setValue('content', 'some blog content')
    ->execute();
```

#### update

Update an entry and get number of affected rows

```php
<?php

$affectedRows = $query->update('post')
    ->set('title', 'New Blog post')
    ->where('id = :id')
    ->setParameter('id', 1)
    ->execute();
```

#### delete

Delete entries and get number of affected rows

```php
<?php

$affectedRows = $query->delete('post')
    ->where('id = :id')
    ->setParameter('id', 1)
    ->execute();
```

#### Advanced execution for select

Execute query and get result as entity

```php
<?php

$post = $query->execute(\Blast\Orm\Hydrator\HydratorInterface::HYDRATE_ENTITY);
```

Execute query and get result as collection

```php
<?php

$posts = $query->execute(\Blast\Orm\Hydrator\HydratorInterface::HYDRATE_COLLECTION);
```

Execute query and get raw result as array

```php
<?php

$result = $query->execute(\Blast\Orm\Hydrator\HydratorInterface::HYDRATE_RAW);
```

#### Events

Query is able to execute events for each statement type.

 - select
 - insert
 - update
 - delete
 
##### build.{type}

Fire this event before query executes statement.

```php
<?php

use Blast\Orm\Query\Events\QueryBuilderEvent;

$query->getEmitter()->addListener('build.select', function (QueryBuilderEvent $event) {
    $event->getBuilder()->setEntity(Post::class);
});

$result = $query->select()->from('post')->where('id = 1')->execute();

```

##### result.{type}

Fire this event after query executes statement and receives result.

```php
<?php

use Blast\Orm\Query\Events\QueryResultEvent;
use Blast\Orm\Query;

$query->getEmitter()->addListener('result.select', function (QueryResultEvent $event, Query $builder) {
    $result = $event->getResult();

    foreach ($result as $key => $value) {
        $result[$key]['contentSize'] = strlen($value['content']);
    }

    $event->setResult($result);
});

$result = $query->select()->from('post')->where('id = 1')->execute();

```

###### Canceling query execution
 
Use `setCancel()` method from given event to cancel a query execution.

On build a query statement

```php
<?php

use Blast\Orm\Query\Events\QueryBuilderEvent;

$query->getEmitter()->addListener('build.select', function (QueryBuilderEvent $event) {
    $event->setCanceled(true);
});
```

On result a query statement

```php
<?php
use Blast\Orm\Query\Events\QueryResultEvent;
use Blast\Orm\Query;

$query->getEmitter()->addListener('result.select', function (QueryResultEvent $event) {
    $event->setCanceled(true);
});
```

### Entities

Entity classes are independent of Blast ORM.

```php
<?php

class Post
{

}

```

#### Definition

In addition to auto-suggest definition from provider, it is also possible to use definition instead of entity.

```php
<?php

use Blast\Orm\Entity\Definition;
use Blast\Orm\Mapper;
use Blast\Orm\Query;

$definition = new Definition();
$definition->setConfiguration([
    'tableName' => 'user_role'
]);

//from mapper
$mapper = new Mapper($definition);

//from query
$query = new Query($connection, $definition);

```

A list of possible configuration

```php
<?php

$configuration = [
        'entity' => \ArrayObject::class,
        'entityCollection' => \SplStack::class,
        'events' => [],
        'fields' => [],
        'indexes' => [],
        'primaryKeyName' => ProviderInterface::DEFAULT_PRIMARY_KEY_NAME,
        'tableName' => '',
        'mapper' => Mapper::class,
        'relations' => []
    ];
$definition->setConfiguration($configuration);
```

#### Provider

Providers are used to determine the entity definition and hydrate data to entity and vice versa. You could pass an 
object, class name or table name to provider. 

If the entity class does not have a definition. the definition is determined automatically by the provider. Definitions 
are represented by static entity methods and / or properties. 

Use providers to access tables without creating a entity class. This is useful for accessing junction tables or 
temporary tables.

```php
<?php

use Blast\Orm\Entity\Provider;

// add an entity as class name
$provider = new Provider(Post::class);

// add an entity as object
$provider = new Provider(Post::class);

// add an entity as table name
// entity object is an array object
$provider = new Provider('user_roles');
```

Add definition to entity as public static property or method. Method name refers to configuration key, mentioned above.

##### Table name 

Return table name as `string`

 - Default: class name without namespace, camelcase converts to underscores e.g. `App\Entities\Post` will have `post` as table name.
 - Static method: or `YourEntity::table()`
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
    public static $tableName = 'post';
}

```

##### Primary key name
 
Return primary key name as `string`
 
 - Default: `id`
 - Static method: `Entity::primaryKeyName()`
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
    public static $primaryKeyName = 'id';
    
}

```

##### Mapper

Return mapper class name as `string` or a instance of `Blast\Orm\MapperInterface`

 - Default: An instance of `Blast\Orm\Mapper`
 - Static method: `Entity::mapper()`
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
    public static $mapper = Mapper::class;
}

```

##### Relations

Return relations as `array` containing instance of `Blast\Orm\Relations\RelationInterface`.

 - Default: `[]`
 - Static method: `Entity::relations()`

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
    public static function relations(EntityWithRelation $entity,  Mapper $mapper){
        return [
            $mapper->hasOne($entity, 'otherTable')
        ];
    }
}

```

##### Access definition from provider

Adapters grant access to data and definition, even if your entity class does not have definitions at all.

```php
<?php

use Blast\Orm\Entity\Provider;

$post = new Post;

$postProvider = new Provider(Post::class);

```

Get table name

```php
<?php
$tableName = $postProvider->getTableName();

```

Get primary key name

```php
<?php
$primaryKeyName = $postProvider->getPrimaryKeyName();

```

Get entities mapper

```php
<?php
$mapper = $postProvider->getMapper();

```

Get entities relation

```php
<?php
$mapper = $postProvider->getRelations();

```

Hydrate data as array to entity

```php
<?php
$entity = $postProvider->hydrate(['title' => 'Hello World']);

```

Hydrate data as entity to array

```php
<?php
$data = $postProvider->extract();

```

### Mapper

The mapper prepares queries for data persistence and access of a provided entity class. All methods always return a query 
instance and need to execute manually. It is also possible to add event listeners for query

#### Create a new mapper for entity

Get entity specific mapper from provider

```php
<?php

use Blast\Orm\Entity\Provider;

$provider = new Provider($post);
$mapper = $provider->getDefinition()->getMapper();

```

#### find

Fetch one entry by primary key

```php
<?php

$post = $mapper->find(1)->execute();

```

#### select

Custom select query

```php
<?php

$post = $mapper->select()
            ->where('title = "Hello world"')
            ->setMaxResults(1)
            ->execute(EntityHydratorInterface::RESULT_ENTITY);
```

#### delete

`delete` expects an primary key or an array of primary keys.

Delete onw entry

```php
<?php

$mapper->delete(1);
```

Delete many entries

```php
<?php

$mapper->delete([1, 2]);
```

### Relations

Relations provide access to related, parent and child entity from another entity.

#### Passing relations

Pass relations as `array` by computed static relation method in entity class. Relations are automatically mapped to entity.

```php
<?php

use Blast\Orm\Mapper;

class Post {

    private $comments = null;
    
    public function getComments(){
        return $this->comments;
    }

    public static function relation(Post $entity, Mapper $mapper){
        return [
            $mapper->hasMany($entity, Comments::class)
        ];
    }
}

$post = $mapper->find(1);
$comments = $post->getComments()->execute();

```

You could also extend the relation query with `RelationInterface::getQuery`.

#### HasOne (one-to-one)

One entity is associated with one related entity by a field which associates with primary key in current entity.

- `$entity` - Current entity instance
- `$foreignEntity` - Entity class name, instance or table name
- `$foreignKey` - Field name on related entity. `null` by default. Empty foreign key is determined by current entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `user_id`

##### Example

One user has one address.

```php
<?php

$relation = $mapper->hasOne($user, Address::class, 'user_id');

```

#### HasMany (one-to-many)

One entity is associated with many related entities by a field which associates with primary key in current entity.

- `$entity` - Current entity instance
- `$foreignEntity` - Entity class name, instance or table name
- `$foreignKey` - Field name on a related entity. `null` by default. Empty foreign key is determined by current entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `post_id`

##### Example

One post has many comments

```php
<?php

$relation = $mapper->hasMany($post, Comments::class, 'post_id');

```

#### BelongsTo (one-to-one or one-to-many)

BelongsTo is the inverse of a HasOne or a HasMany relation.

One entity is associated with one related entity by a field which associates with primary key in related entity.

- `$entity` - Current entity instance
- `$foreignEntity` - Entity class name, instance or table name
- `$localKey` - Field name on current entity. `null` by default. Empty local key is determined by related entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `post_id`

##### Example

One post has one or many comments

```php
<?php

$relation = $mapper->belongsTo($comment, Post::class, 'post_id');

```

#### ManyToMany (many-to-many)

Many entities of type _A_ are associated with many related entities of type _B_ by a junction table. The junction table 
stores associations from entities of type _A_ to entities of type _B_.

- `$entity`: Current entity instance
- `$foreignEntity`: Entity class name, instance or table name
- `$foreignKey` - Field name on a related entity. `null` by default. Empty foreign key is determined by current primary key name.
- `$localKey`: Field name on current entity. `null` by default. Empty foreign key is determined by related entity primary key name.
- `$junction`: Junction table name. `null` by default. Empty table name is determined by entity table name and foreign entity table name as follows: `{tableName}_{foreignTableName}`, e.g `post_comment`.
- `$junctionLocalKey`: Field name on a related entity. `null` by default. Empty junction local key is determined by current entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `post_id`.
- `$junctionForeignKey`: Field name on current entity. `null` by default. Empty junction foreign key is determined by related entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `comment_id`.

##### Example

One user has many roles, and one role has many users. Users primary key name is `id`, Roles primary key name is `pk` 
(Primary key short name). The junction table `user_role` contains `user_id` and `role_id` columns.

```php
<?php

$relation = $mapper->manyToMany($user, Role::class, 'pk', 'id', 'user_role', 'user_id', 'role_id');

```

### Repository

The repository abstracts methods for data persistence and access. All methods execute their queries directly. 

Blast ORM provides a repository interface `\Blast\Orm\RepositoryInterface`.

A repository knows it's entity. Therefore we need to pass the entity as class name or instance 

Create from interface

```php
<?php

use Blast\Orm\MapperFactoryInterface;
use Blast\Orm\MapperFactoryTrait;
use Blast\Orm\RepositoryInterface;
use Blast\Orm\Hydrator\HydratorInterface;

class PostRepository implements MapperFactoryInterface, RepositoryInterface
{
    
    use MapperFactoryTrait;
    
    /**
     * Get repository entity
     */
    public function getEntity(){
        return Post::class;
    }

    /**
     * Get a collection of all entities
     *
     * @return \SplStack|array
     */
    public function all()
    {
        return $this->createMapper($this->getEntity())->select()->execute(HydratorInterface::HYDRATE_COLLECTION);
    }

    /**
     * Find entity by primary key
     *
     * @param mixed $primaryKey
     * @return \ArrayObject|\stdClass|object
     */
    public function find($primaryKey)
    {
        return $this->createMapper($this->getEntity())->find($primaryKey)->execute(HydratorInterface::HYDRATE_ENTITY);
    }

    /**
     * Save new or existing entity data
     *
     * @param object|array $data
     * @return int|bool
     */
    public function save($data)
    {
        return $this->createMapper($data)->save($data)->execute();
    }

}

```

Create repository by abstract

```php
<?php

use Blast\Orm\AbstractRepository;

class PostRepository extends AbstractRepository {
    
    use \Blast\Orm\Entity\EntityAwareTrait;
    
    /**
     * Init repository and bind related entity
     */
    public function __construct(){
        $this->setEntity(Post::class);
    }
}
```

Create repository instance

```php
<?php

$postRepository = new PostRepository();

```

#### find

Fetch one entry by primary key

```php
<?php

$post = $postRepository->find(1);

```

#### all

Fetch all entries and return as collection `Blast\Orm\Data\DataObject`

```php
<?php

$posts = $postRepository->all();

foreach($posts as $post){

    //do something

}

```

#### save

Save is determining if the entity is new and executes `Blast\Orm\Mapper::update` or if it is new `Blast\Orm\Mapper::create`.

```php
<?php

$post = new Post();

$post['title'] = 'Hello World';
$post['content'] = 'Some content about hello world.';
$post['date'] = new \DateTime();

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
[ico-coveralls]: https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=master

[link-packagist]: https://packagist.org/packages/blast/orm
[link-travis]: https://travis-ci.org/phpthinktank/blast-orm
[link-downloads]: https://packagist.org/packages/blast/orm
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
[link-coveralls]: https://coveralls.io/github/phpthinktank/blast-orm
