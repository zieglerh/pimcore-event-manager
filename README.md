# EventManagerBundle

Subscribe to Events via PHP Attribute Tags without any configuration and only via function arguments 

<!-- TOC -->
* [EventManagerBundle](#eventmanagerbundle)
  * [Installation](#installation)
    * [Composer](#composer)
  * [Benefits](#benefits)
  * [Usage](#usage)
    * [Examples](#examples)
      * [DataObjects](#dataobjects)
      * [Event argument](#event-argument-)
      * [Any event](#any-event)
      * [Multiple classes](#multiple-classes)
    * [Event arguments](#event-arguments)
  * [Solving problems](#solving-problems)
    * [Good practices](#good-practices)
    * [General mistakes](#general-mistakes)
    * [Debugging](#debugging)
<!-- TOC -->

## Installation

### Composer
```bash
composer require zieglerh/pimcore-event-manager-bundle:^1.0
```

## Benefits

- define EventSubscribers simple with PHP Attributes
- subscribe to only used events
- caching via symfony build cache 

## Usage

- create an EventSubscriber class and implement `EventManagerBundle\EventSubscriber\EventSubscriberInterface`
- make sure your class folder is defined in services.yml
- use `EnabledTrait` in your class
- create a function and define function Properties with one or more events
- the function arguments can be the event subject or the event object

```php
#[Event(DocumentEvents::PRE_ADD)]
#[Event(DocumentEvents::PRE_UPDATE)]
public function check(Document\Link $link): void
```

To speed up bulk tasks, you can enable and disable the EventSubscriber from anywhere via static functions.

### Examples

#### DataObjects

```php
<?php

namespace App\EventSubscriber;

use EventManagerBundle\Event\Event;
use EventManagerBundle\EventSubscriber\EnabledTrait;
use EventManagerBundle\EventSubscriber\EventSubscriberInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Model\DataObject\MyModel;

/**
 * Class MyModelSubscriber
 *
 * @package App\EventSubscriber
 */
class MyModelSubscriber implements EventSubscriberInterface
{
    use EnabledTrait;

    /**
     * @param MyModel $object
     *
     * @return void
     * @throws \JsonException
     */
    #[Event(DataObjectEvents::PRE_UPDATE)]
    #[Event(DataObjectEvents::PRE_ADD)]
    public function doSomething(MyModel $object): void
    {
        // implementation
    }
}

```

#### Event argument 
Documents with $event example

```php
<?php

namespace App\EventSubscriber;

use EventManagerBundle\Event\Event;
use EventManagerBundle\EventSubscriber\EnabledTrait;
use EventManagerBundle\EventSubscriber\EventSubscriberInterface;
use Pimcore\Event\DocumentEvents;
use Pimcore\Model\Document;
use Pimcore\Event\Model\DocumentEvent;

/**
 * Class DocumentSubscriber
 *
 * @package App\EventSubscriber
 */
class DocumentSubscriber implements EventSubscriberInterface
{
    use EnabledTrait;

    /**
     * @param Document\Link $link
     *
     * @return void
     */
    #[Event(DocumentEvents::PRE_ADD)]
    #[Event(DocumentEvents::PRE_UPDATE)]
    public function doSomething(Document\Link $link, DocumentEvent $event): void
    {
        // implementation
    }
}
```
#### Any event
Basically any event from *Events.php classes

```php
// e.g. vendor/pimcore/pimcore/lib/Event/UserRoleEvents.php
/**
 * @Event("Pimcore\Event\Model\UserRoleEvent")
 *
 * @var string
 */
const PRE_ADD = 'pimcore.user.preAdd';

// from @Event docblock you can see, it will fire a UserRoleEvent
// ...
// inside
\Pimcore\Model\User\AbstractUser::save()
// you will see
$this->dispatchEvent(new UserRoleEvent($this), UserRoleEvents::PRE_UPDATE);
// you can use either User or Role or UserRole or Folder class as function argument
// and optional UserRoleEvent as event argument
```

#### Multiple classes

You can combine logic from multiple objects

```php
#[Event(DocumentEvents::PRE_UPDATE)]
public function doSomething(User|Role|UserRole $object): void
{
    // implementation
}
```


### Event arguments

By default, autosave and save version only events are not handled by the EventSubscriber. 
This is contrary to the default behavior of Pimcore.

If you require one, you must also add the Attribute to the function.

```php
#[IsAutoSave]
#[SaveVersionOnly]
#[Event(DataObjectEvents::PRE_ADD)]
public function doSomething(MyModel $object): void
{
    // implementation
}
```

## Solving problems

### Good practices

- when batch importing or saving, then disable() the subscriber and do a batch update afterwards
- use symfony message queue to start tasks in the background

### General mistakes

- make sure your subscriber function is public

### Debugging

- when you want to debug the bundle, you have to change a class, so that symfony invalidates the cache and does a rebuild

