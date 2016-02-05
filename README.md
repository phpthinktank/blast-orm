# Blast orm

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status](https://img.shields.io/coveralls/phpthinktank/blast-orm/master.svg?style=flat-square)](https://coveralls.io/github/phpthinktank/blast-orm?branch=1.0.x-dev)

Framework agnostic db package with orm and active record implementation based on Doctrine 2. 

## Install

Via Composer

``` bash
$ composer require blast/orm
```

## Usage



### Getting Started

Create a factory with container-interopt compatible Container e.g. league/container and connection data.

```php
<?php

use Blast\Db\Orm\Factory;
use League\Container;


Factory::create(new Container(), [
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);

```

#### Create a new Entity

```php
<?php

namespace App\Entities\Post;


use Blast\Db\Entity\AbstractEntity;
use Blast\Db\Schema\Table;
use Doctrine\DBAL\Types\Type;

class Post extends AbstractEntity
{

    /**
     * Configure entity
     */
    public function configure()
    {
        $table = new Table('post');
        $table->addColumn('id', Type::INTEGER)
            ->setAutoincrement(true)
            ->setLength(10);
        $table->addColumn('title', Type::STRING);
        $table->addColumn('content', Type::TEXT);
        $table->addColumn('date', Type::DATETIME)
            ->setDefault(new \DateTime());
        $table->setPrimaryKey(['id']);

        //set entity table
        $this->setTable($table);
    }
}

```

#### Save data

```php
<?php

use App\Entities\Post;
use Blast\Db\Orm\Factory;

$post = new Post;

//create mapper from post
$mapper = Factory::getInstance()->createMapper($post);

$post->title = 'Hello World';
$post->content = 'Some content about hello world.';
$post->title = new \DateTime();

//create or update entity
$mapper->save($post);
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
