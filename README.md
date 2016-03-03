# Blast ORM

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-coveralls]][link-coveralls]

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

Entity classes could also be instances of [`stdClass`](http://php.net/manual/en/reserved.classes.php), 
[`ArrayObject`](http://php.net/manual/de/class.arrayobject.php) or [`DataObject`](src/Data/DataObject.php)

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

The mapper prepares queries for data persistence and access of a provided entity class. All methods always return a query 
instance and need to execute manually.

#### Create a new mapper for entity

Create mapper by instance

```php
<?php

use Blast\Orm\Mapper;

$mapper = new Mapper($post);

```

Create mapper from adapter

```php
<?php

$adapter = EntityAdapterCollectionFacade::get($post);
$mapper = $adapter->getMapper();

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

$repository->delete(1);
```

Delete many entries

```php
<?php

$repository->delete([1, 2]);
```

### Relations

Relations provide access to related, parent and child entity from another entity.

#### Passing relations

Pass relations as `array` by computed static relation method in entity class 

```php
<?php

class Post {

    /**
     * @var Query
     */
    $comments = [];
    
    /**
     * @return \Blast\Orm\Relations\RelationInterface
     */
    public function getComments(){
        return $this->comments;
    }

    public static function relation(Post $entity){
        return [
            HasMany($entity, Comments::class)
        ]
    }
}

$post = $mapper->find(1);

$relation = $post->getComments();
$comments = $relation->execute();

```

Execute directly in custom method

```php
<?php

class Post {
    
    /**
     * @return Comments[]
     */
    public function getComments(){
        $relation = HasMany($this, Comments::class);
        
        return $relation->execute();
    }
}

```

Access relation query and modify the result.

__For example__: We assume we have _accepted_ and _denied_ comments. We only want to receive _accepted_ comments.

```php
<?php

$publishedComments = $relation->getQuery()->where('published = 1')->execute();

```

#### HasOne (one-to-one)

One entity is associated with one related entity by a field which associates with primary key in current entity.

- `$entity` - Current entity instance
- `$foreignEntity` - Entity class name, instance or table name
- `$foreignKey` - Field name on related entity. `null` by default. Empty foreign key is determined by current entity table name and primary key name as follows: `{tableName}_{primaryKeyName}`, e.g `user_id`

##### Example

One user has one address.

```php
<?php

$relation = HasOne($user, Address::class, 'user_id');

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

$relation = HasOne($post, Comments::class, 'post_id');

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

$relation = BelongsTo($comment, Post::class, 'post_id');

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

$relation = ManyToMany($user, Role::class, 'pk', 'id', 'user_role', 'user_id', 'role_id');

```

### Repository

The repository abstracts methods for data persistence and access. All methods execute their queries directly. 

Blast ORM integration for repositories are __optional__!

#### Create repository

A repository knows it's entity. Therefore we need to pass the entity as class name or instance 

Create from interface

```php
<?php

use Blast\Orm\RepositoryInterface;
use Blast\Orm\Entity\EntityAdapter;
use Blast\Orm\Entity\EntityAdapterLoaderTrait;
use Blast\Orm\Entity\EntityAwareInterface;
use Blast\Orm\Entity\EntityAwareTrait;

class PostRepository implements EntityAwareInterface, RepositoryInterface
{
    use EntityAwareTrait;
    use EntityAdapterLoaderTrait;
    
    /**
     * Init repository and bind related entity
     */
    public function __construct(){
        $this->setEntity(Post::class);
    }

    /**
     * @var EntityAdapter
     */
    protected $adapter = null;

    /**
     * Get adapter for entity
     *
     * @return \Blast\Orm\Entity\EntityAdapter
     */
    private function getAdapter(){
        if($this->adapter === null){
            $this->adapter = $this->loadAdapter($this->getEntity());
        }
        return $this->adapter;
    }

    /**
     * Get a collection of all entities
     *
     * @return \ArrayObject|\stdClass|\Blast\Orm\Data\DataObject|object
     */
    public function all()
    {
        return $this->getAdapter()->getMapper()->select()->execute(EntityHydratorInterface::HYDRATE_COLLECTION);
    }

    /**
     * Find entity by primary key
     *
     * @param mixed $primaryKey
     * @return \ArrayObject|\stdClass|\Blast\Orm\Query\Result|\Blast\Orm\Data\DataObject|object
     */
    public function find($primaryKey){
        return $this->getAdapter()->getMapper()->find($primaryKey)->execute(EntityHydratorInterface::HYDRATE_ENTITY);
    }

    /**
     * Save new or existing entity data
     *
     * @param object|array $data
     * @return int|bool
     */
    public function save($data){
        $mapper = $this->getAdapter()->getMapper();
        $query = $adapter->isNew() ? $mapper->create($data) : $mapper->update($data);
        return $query->execute();
    }

}

```

Create repository by abstract

```php
<?php

use Blast\Orm\AbstractRepository;

class PostRepository extends AbstractRepository {
    
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
[ico-coveralls]: https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=1.0.x-dev

[link-packagist]: https://packagist.org/packages/blast/orm
[link-travis]: https://travis-ci.org/phpthinktank/blast-orm
[link-downloads]: https://packagist.org/packages/blast/orm
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
[link-coveralls]: https://coveralls.io/github/phpthinktank/blast-orm
