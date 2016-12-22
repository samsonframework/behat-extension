# SamsonFramework Behat extension official documentation

## Installation
* Create ```FeatureContext.php``` class in ```features/bootstrap``` with namespace ```yourproject/behat```:

```php
declare(strict_types=1);

namespace yourproject\behat;

class FeatureContext extends \samsonframework\behatextension\GenericFeatureContext {

}
```
> You can actually use any classname and namespace

* Add autoload section for PSR-4 into your projects ```composer.json```:
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
 * Your context class(es) under ```default->suites->default->contexts``` section, in our example this is
 ```yourproject\behat\FeatureContext```
 > ```session``` parameter with ```'@session'`` is for passing Symfony session object and needed only if your
  ```FeatureContext``` class extends ```\samsonframework\behatextension\GenericFeatureContext```
 
 * Your ```base_url``` of configured domain for testing