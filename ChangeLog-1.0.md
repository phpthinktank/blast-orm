# Changes in Blast orm 1.0

All notable changes of the Blast orm 1.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 0.6.3

### Altered

 - `\Blast\Orm\Provider` additional data extraction is now working correctly
 - Update Readme

## 0.6.2

### Added

 - add `\Blast\Orm\Gateway` as accessor of a single database table or view

## 0.6.1

### Added

 - `\Blast\Orm\QueryFactoryInterface` act as contract for query creation
 - `\Blast\Orm\Connection` acts as connection wrapper for doctrine connection and is able to create connection-aware mappers and queries. createQueryBuilder is still creating doctrine SQL query builder!

### Altered

 - `\Blast\Orm\MapperFactoryInterface` is not forcing to add a connection

## 0.6.0

### Altered

 - add `\Blast\Orm\Entity\DefinitionInterface` and use definition instead of provider
 - Refactor hydrators use [`zendframework/zend-hydrator`](http://framework.zend.com/manual/current/en/modules/zend.stdlib.hydrator.html) for internal logic.

### Removed

 - remove definition data getters from provider, you need to use `$provider->getDefiniton()->get*()`

## 0.5.3

### Altered

 - Upgrade minimum requirements and dependecies

## 0.5.2

### Altered

 - Consider fields
 - Convert result from sql select to php value
 - Convert values from sql update or create to sql value

## 0.5.1

### Fixes

 - Fix bug: Use configured collection from definition instead of always \SplStack

### Altered

 - Add definition tests for mapper and query
 - Rename query `before` event to `build` 
 - Rename query `after` event to `result` 
 - Update readme

## 0.5

### Fixes

 - Fix bug where plain object don't get data from hydrator
 - Fix bug throwing exception when attaching relation without name

### Added
 
 - `Blast\Orm\Entity\Transformer` converts any configuration into definition and entity
 - `Blast\Orm\Entity\Definition` is holding entity definitions.

### Altered

 - connection manager is now accessible as singleton via `Blast\Orm\ConnectionManager::getInstance()`
 - Relations don't need to initialize to get relation name
 - Relation internals refactored
 - `\Blast\Orm\Hydrator\ArrayToObjectHydrator` hydrates relations as well as data.
 - Get pluralized table names when table name was suggested from entity shortname
 - `Blast\Orm\Entity\Provider` uses definition and transformer for preparing entities
 - update abstract repository with new provider and mapper

### Removed

 - `Blast\Orm\Facades`
 - Locator and Container no longer dependencies for blast orm

## 0.4

### Added

 - `Blast\Orm\Locator` deliver methods to access providers, mappers and connections and replaces `Blast\Orm\Entity\EntityAdapterCollectionFacade` and `Blast\Orm\ConnectionCollectionFacade`
 - `Blast\Orm\LocatorFacade` solves IoC concerns by providing swappable locator from container.
 - `Blast\Orm\Entity\Provider` replaces entity adaption 
 - `Blast\Orm\Hydrator` manages hydration from object to array and vice versa 

### Altered

 - `Blast\Orm\Data\DataAdapter` is now delivering logic to call data
 - `Blast\Orm\Entity\EntityAdapter` is now delivering logic to call definitions
 - Rename `Blast\Orm\Entity\EntityAdapterCollection` to `Blast\Orm\Entity\EntityAdapterManager`
 - Connection manager simplify redundant method names. Removed `Configuration` word from `get, set, getPrevious`, `getConnections` becomes `all`
 - Connection initiation to mapper or query
 - Add query event classes

### Removed

 - Replace `Blast\Orm\Container` with `League\Container`
 - `Blast\Orm\Entity\Provider` replaces entity adapter classes and definition interfaces
 - `Blast\Orm\Object\ObjectAdapter`
 - `Blast\Orm\ConnectionCollectionFacade`
 - `Blast\Orm\Query\Result`
 - `Blast\Orm\Entity\GenericEntity`
 - `Blast\Orm\Query\ResultInterface`
 - `Blast\Orm\Data`
 - `Blast\Orm\Hook`

## 0.3

### Added

 - `Blast\Orm\Mapper` for accessing and persisting data
 - `Blast\Orm\Relations` component to create relations between entities
 - `Blast\Orm\Object\ObjectAdapterCache` for reusing entity definition
 - `Blast\Orm\Facades` for more customizable classes against contracts
 
### Altered

 - `Blast\Orm\Repository` is mediating between entity and mapper delivered by entity, repository queries are excluded to mappers
 
### Removed

- `Blast\Orm\Repository::create`
- `Blast\Orm\Repository::delete`
- `Blast\Orm\Repository::update`
- `Blast\Orm\Manager` has been replaced by ConnectionFacade

## 0.2

### Added

 - `Blast\Orm\Data` component for convenient data access
 - `Blast\Orm\Repository` mediates between entities and query
 - `Blast\Orm\Hook` is providing a low-level Observer-Subject implementation basically for extending logic of class methods in `Blast\Orm\Data`

### Altered

 - Rename `Blast\Db`to `Blast\Orm\`
 - `Blast\Orm\Query::execute` is now able to emit before and after on execution by type name
 - `Blast\Orm\Entity` component is providing adapters for accessing entity classes and determine definition and data
 
### Removed
 
 - `Blast\Db\Entity` component
 - `Blast\Db\Orm` component (replaced by repository)
