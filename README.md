# Blast orm

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status](https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=1.0.x-dev)

Framework agnostic ormuration package supporting php and json. More file types under development.

## Install

Via Composer

``` bash
$ composer require blast/orm
```

## Usage

Only a few lines of code:

```php
<?php

// define our repository and add our models
$repository = new Repository(
    [
        'posts' => \Acme\Models\Posts::class,
        'user' => \Acme\Models\User::class
    ]
);

//get user data
//the convention is *table*.*field*
//if the table name can associated with the model name we use model data automatically.
$user = $repository->user()->findBy('user.id', 1);

//results which does not relating to a model are GenericModels and contain information 
//about a table but do not validate or transform fields

//find posts by user relation with repository
$posts = $repository->findBy('posts.user', $user);

//or find with mapper
$repository->mapper('posts')->fetch((new Query)->where('user_id', $user->primaryKey()));

//or more convenient
$posts = $user->posts;

//each result is an instance of ResultInterface

//a single result = ResultInterface::ONE
//a result collection = ResultInterface::MANY

//manipulate data
if($posts->isOne()){
    $posts->anyField = 'value';
}elseif($posts->isMany()){
    foreach($posts as $post){
        $post->anyField = 'value';
    }
}

//we also could force a type
//find posts by user relation
//relation types need to configure within the model
$posts = $repository->findBy('posts.user', $user, ResultInterface::MANY);

//or find with mapper
$repository->mapper('posts')->fetch((new Query)->where('user_id', $user->primaryKey()), ResultInterface::MANY);

//saving data
//with repository

$repository->save($posts);

//or with mapper, object needs to be an instance of model in mapper!
$repository->mapper('posts')->save($posts);

//or in orm style which is similar to $repository->mapper('posts')->save($posts);
$posts->save();
```

### Field definitions

If a definition exists for an field, ORM will automatically validate, cast and transform field data.

```php
    <?php
    
    class Posts extends Blast\Orm\Model
    {
        public function fields(){
            [
                'id' => [
                    'type' => 'int',
                    'auto_increment' => true,
                    'primary' => 'true',
                ],
                'text' => [
                    'type' => 'text'
                    'default' => null
                ],
                'meta' => [
                    'type' => 'json'
                    'default' => '[]'
                ],
                'created_at' => [
                    'type' => 'timestamp',
                    'default' => 'datetime'
                ]
            ]
        }
    }
```

### Relations

Configuring relations is easy.

```php
<?php

class Posts extends Blast\Orm\Model
{

    public function relations(){
        return [
            //belongs to another model or table: local key, target model or table, foreign key, foreign key index
            'user' => $this->belongsTo('user_id', Acme\Model::class, 'id', 'fk_user_id'),
            
            //belongs to many entries of one model: foreign key, target model or table, local key
            'comments' => $this->hasMany('user_id', Acme\Model::class, 'id'),
            
            //belongs to many through: 
            // - lookup: model or table
            // - local[lookup key, local key]
            // - foreign[lookup key, foreign key, model or table]
            'tags' => $this->hasManyThrough('post_tags', ['post_id', 'id'], ['tag_id', 'id', Acme\Model\Tags),
        ]
    }

}

```

### Migrations

If a definition exists for an field, ORM will automatically validate, cast and transform field data.

```php
    <?php
    
    //model version
    class Version1Migration extends Blast\Orm\Migration
    {   
        public function migrate($db){
            $schema->createTable(Acme\Models\Posts::class, false);
            $schema->createTable(Acme\Models\Tags::class, false);
            $schema->createTable('posts_tags', [
                'post_id' => [
                    'type' => 'int',
                ],
                'tag_id' => [
                    'type' => 'int',
                ]
            ]);
            
            //writes schema
            $this->publish($schema);
        }
    }
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
