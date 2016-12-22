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
installation section of this document for new FeatureContextFile except changing ```composer.json```