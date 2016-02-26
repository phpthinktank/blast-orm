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

Entity classes could also be instances of [`stdClass`](http://php.net/manual/en/reserved.classes.php), [`ArrayObject`](http://php.net/manual/de/class.arrayobject.php) or [`DataObject`](src/Data/DataObject.php) 

### Query

The query acts as accessor to the persistence layer. Instead of to use a big data repository, Blast ORM uses a query class. 
The query class is able to process the result as raw array or a map the result to a single entity class or a collection 
of entity classes.

### Repository

The repository The repository mediates between query (dbal) and entities.
  
## Usage

### Initialize

Create a factory with [container-interopt](https://github.com/container-interop/container-interop) compatible Container e.g. [league/container](https://github.com/thephpleague/container) and connection data.

```php
<?php

use Blast\Orm\Manager;
use League\Container;


Manager::create(new Container(), [
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);

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

### Working with Entities

Entity classes are independent of Blast ORM. Blast ORM is using an entity adaption to access entity data and definition

```php
<?php

class Post
{

}

```

#### Table name

If entity class does not define a table name, the table name is determined automatically by class name without namespace. 
  
`App\Entities\Post` will have `post` as table name.

Pass a custom `table` as a static method or static property as follows
 
 - `Entity::getTable()`
 - `Entity::table()`
 - `Entity::$table`

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

#### Primary key name

If entity class does not define a primary key name, the primary key name is `id`.

Similar to `table` you could a customize `primaryKeyName` as follows
 
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
    public static function getPrimaryKeyName()
    {
        return 'id';
    }
}

```

### Repository

#### Get repository

```php
<?php

use Blast\Orm;

$repository = new Repository($post);

```

#### find

Fetch one entry by primary key

```php
<?php

$post = $repository->find(1);

```

#### select

Custom select query

```php
<?php

$first = $repository->select()->where('title = "Hello world"')->setMaxResults(1)->execute(Query::RESULT_ENTITY);
```

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

### Advanced execution for select

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
