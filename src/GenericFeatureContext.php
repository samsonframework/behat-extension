<?php

declare(strict_types=1);

namespace samsonframework\behatextension;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines generic feature steps.
 */
class GenericFeatureContext extends MinkContext
{
    /** @var int UI generic delay duration in milliseconds */
    const DELAY = 1000;
    /** @var 0.1 sec spin delay */
    const SPIN_DELAY = 100000;
    /** @var int UI javascript generic delay duration in milliseconds */
    const JS_DELAY = self::DELAY / 5;
    /** @var int UI spin function timeout for ex 30*0.1s = 15 sec timeout */
    const SPIN_TIMEOUT = 150;

    /** @var mixed */
    protected $session;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @param mixed $session
     */
    public function __construct($session = null)
    {
        $this->session = $session;

        ini_set('xdebug.max_nesting_level', '1000');
    }

    /**
     * Get Symfony service instance.
     *
     * @param string $serviceName Service identifier
     * @param string $session     Behat Symfony session name
     *
     * @return object Symfony service instance
     */
    public function getSymfonyService(string $serviceName, string $session = 'symfony2')
    {
        return $this->getSession($session)
            ->getDriver()
            ->getClient()
            ->getContainer()
            ->get($serviceName);
    }

    /**
     * Spin function to avoid Selenium fails.
     *
     * @param callable $lambda
     * @param null     $data
     * @param int      $delay
     * @param int      $timeout
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function spin(callable $lambda, $data = null, $delay = self::SPIN_DELAY, $timeout = self::SPIN_TIMEOUT)
    {
        $failedExceptions = [];
        for ($i = 0; $i < $timeout; $i++) {
            try {
                if ($lambda($this, $data)) {
                    return true;
                }
            } catch (\Exception $e) { // Gather unique exceptions
                $failedExceptions[$e->getMessage()] = $e->getMessage();
            }

            usleep($delay);
        }

        $backtrace = debug_backtrace();

        throw new \Exception(
            'Timeout thrown by '.$backtrace[1]['class'].'::'.$backtrace[1]['function']."()\n"
            .(array_key_exists('file', $backtrace[1]) ? $backtrace[1]['file'].', line '.$backtrace[1]['line'] : '')."\n"
            .implode("\n", $failedExceptions)
        );
    }

    /**
     * @AfterStep
     *
     * @param AfterStepScope $scope
     */
    public function takeScreenShotAfterFailedStep(AfterStepScope $scope)
    {
        if (99 === $scope->getTestResult()->getResultCode()) {
            $driver = $this->getSession()->getDriver();

            if (!($driver instanceof Selenium2Driver)) {
                return;
            }

            $step = $scope->getStep();
            $fileName = 'Fail.'.preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $scope->getName().'-'.$step->getText()).'.jpg';
            file_put_contents($fileName, $driver->getScreenshot());
        }
    }

    /**
     * Find all elements by CSS selector.
     *
     * @param string $selector CSS selector
     *
     * @throws \InvalidArgumentException If element not found
     *
     * @return \Behat\Mink\Element\NodeElement[]
     */
    protected function findAllByCssSelector(string $selector)
    {
        $session = $this->getSession();

        $elements = $session->getPage()->findAll('css', $this->fixStepArgument($selector));

        // If element with current selector is not found then print error
        if (count($elements) === 0) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $selector));
        }

        return $elements;
    }

    /**
     * Find element by CSS selector.
     *
     * @param string $selector CSS selector
     *
     * @throws \InvalidArgumentException If element not found
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findByCssSelector(string $selector) : NodeElement
    {
        return $this->findAllByCssSelector($selector)[0];
    }

    /**
     * @Given /^I set browser window size to "([^"]*)" x "([^"]*)"$/
     *
     * @param int $width  Browser window width
     * @param int $height Browser window height
     */
    public function iSetBrowserWindowSizeToX($width, $height)
    {
        $this->getSession()->resizeWindow((int) $width, (int) $height, 'current');
    }

    /**
     * @Given /^I wait "([^"]*)" milliseconds for response$/
     *
     * @param int $delay Amount of milliseconds to wait
     */
    public function iWaitMillisecondsForResponse($delay = self::DELAY)
    {
        $this->getSession()->wait((int) $delay);
    }

    /**
     * Click on the element with the provided xpath query.
     *
     * @When I click on the element :arg1
     *
     * @param string $selector CSS element selector
     *
     * @throws \InvalidArgumentException
     */
    public function iClickOnTheElement(string $selector)
    {
        // Click on the founded element
        $this->findByCssSelector($selector)->click();
    }

    /**
     * @When /^I hover over the element "([^"]*)"$/
     *
     * @param string $selector CSS element selector
     *
     * @throws \InvalidArgumentException
     */
    public function iHoverOverTheElement(string $selector)
    {
        $this->findByCssSelector($selector)->mouseOver();
    }

    /**
     * Fill in input with the provided info.
     *
     * @When I fill in the input :arg1 with :arg2
     *
     * @param string $selector CSS element selector
     * @param string $value    Element value for filling in
     *
     * @throws \InvalidArgumentException
     */
    public function iFillInTheElement(string $selector, string $value)
    {
        $this->findByCssSelector($selector)->setValue($this->fixStepArgument($value));
    }

    /**
     * @When I scroll vertically to :arg1 px
     *
     * @param mixed $yPos Vertical scrolling position in pixels
     */
    public function iScrollVerticallyToPx($yPos)
    {
        $this->getSession()->executeScript('window.scrollTo(0, Math.min(document.documentElement.scrollHeight, document.body.scrollHeight, '.((int) $yPos).'));');
    }

    /**
     * @When I scroll horizontally to :arg1 px
     *
     * @param mixed $xPos Horizontal scrolling position in pixels
     */
    public function iScrollHorizontallyToPx($xPos)
    {
        $this->getSession()->executeScript('window.scrollTo('.((int) $xPos).', 0);');
    }

    /**
     * @Given /^I fill hidden field "([^"]*)" with "([^"]*)"$/
     *
     * @param string $field Field name
     * @param string $value Field value
     */
    public function iFillHiddenFieldWith(string $field, string $value)
    {
        // TODO: Change to Mink implementation
        $this->getSession()->executeScript("
            $('input[name=".$field."]').val('".$value."');
        ");
    }

    /**
     * @Then I check custom checkbox with :id
     *
     * @param string $id Checkbox identifier
     *
     * @throws \InvalidArgumentException If checkbox with provided identifier does not exists
     */
    public function iCheckCustomCheckboxWith(string $id)
    {
        // Find label for checkbox by chekbox identifier
        $element = null;
        foreach ($this->findAllByCssSelector('label') as $label) {
            if ($label->getAttribute('for') === $id) {
                $element = $label;
            }
        }

        // Imitate checkbox checking by clicking its label
        $element->click();
    }

    /**
     * @Then I drag element :selector to :target
     *
     * @param string $selector Source element for dragging
     * @param string $target   Target element to drag to
     *
     * @throws \InvalidArgumentException
     */
    public function dragElementTo(string $selector, string $target)
    {
        $this->findByCssSelector($selector)->dragTo($this->findByCssSelector($target));

        $this->iWaitMillisecondsForResponse(self::JS_DELAY);
    }

    /**
     * Fill in input with the provided info.
     *
     * @When I fill in the element :arg1 with value :arg2 using js
     *
     * @param string $selector CSS element selector
     * @param string $value    Element value for filling in
     */
    public function iFillInTheElementUsingJs(string $selector, string $value)
    {
        $this->getSession()->executeScript('document.querySelectorAll("'.$selector.'")[0].value="'.$value.'";');
    }
}
