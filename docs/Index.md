# SamsonFramework Behat extension official documentation

## Installation
* Create ```FeatureContext.php``` class in ```features/bootstrap``` with namespace ```yourproject/behat```:

```php
#features/bootstrap/FeatureContext.php
declare(strict_types=1);

namespace yourproject\behat;

class FeatureContext extends \samsonframework\behatextension\GenericFeatureContext {

}
```
> You can actually use any classname and namespace

* Add autoload section for PSR-4 into your projects ```composer.json```:
#composer.json
```json
"psr-4": {
  "yourproject\\behat\\": "features/bootstrap"
}
```

* Create ```behat.yml``` in project root folder with:
```yml
# behat.yml
imports:
    - vendor/samsonframework/behat-extension/src/behat.symfony.yml

default:
  suites:
    default:
      contexts:
        - yourproject\behat\FeatureContext:
          session: '@session'
  extensions:
    Behat\MinkExtension:
      base_url: 'http://yoururl'
```

Where you need to specify:
 * Your context class(es) name under ```default->suites->default->contexts``` section, in our example this is
 ```yourproject\behat\FeatureContext```. ```session``` parameter with ```'@session'``` is for passing Symfony session object and needed only if your
 ```FeatureContext``` class extends ```\samsonframework\behatextension\GenericFeatureContext```
 
 * Your ```base_url``` of configured domain for testing
 
# Creating other project related contexts
For creating separate project related feature context to have a beautiful classes structure you should create 
new feature context classes in the same namespace to give ability Behat to use composer PSR-4 autoloading feature,
for example if you want to create OrderFeatureContext class then you need to repeat same steps as described in
installation section of this document for new FeatureContext file except changing ```composer.json```.

> You should decide if you need step definitions from ```\samsonframework\behatextension\GenericFeatureContext``` and
if yes then you can just extend you FeatureContext class in other created FeatureContext classes, this will give you ability to create generic project related steps definition in one place.

# Generic functions and step definitions

## Automatic after step screenshot creation
Special Behat after step hook for screenshot creation after failed step. ```takeScreenShotAfterFailedStep()````

## Find all DOM elements by CSS selector
```findAllByCssSelector(string $selector):NodeElement[]```

## Find DOM element by CSS selector
```findByCssSelector(string $selector):NodeElement```

## Change browser window size
> I set browser window size to $width x $height

```iSetBrowserWindowSizeToX($width, $height)```

## Click on any element
> I click on the element $selector

```iClickOnTheElement(string $selector)```

## Wait X milliseconds for response
Usually when creating automated tests we need a delay for loading and updating, this step defenition can take ```$delay```
argument in milliseconds but by default it uses ```GenericFeatureContext::DELAY``` constant.
> I wait $delay milliseconds for response

```iWaitMillisecondsForResponse($delay = self::DELAY)```

