# Changes in Blast orm 1.0

All notable changes of the Blast orm 1.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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