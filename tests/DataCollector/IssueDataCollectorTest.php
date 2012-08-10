<?php

namespace DataCollector;

use Behat\GithubExtension\DataCollector\IssueDataCollector;
use Behat\Behat\Event\StepEvent;

class IssueDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getScenarioResult
     */
    public function should_collect_informations_on_scenario_result($scenarios, $results)
    {
        $dataCollector = new IssueDataCollector();
        foreach ($scenarios as $scenario) {
            $dataCollector->afterScenario($this->getScenarioEvent($scenario['title'], $scenario['result']));
        }

        $this->assertCount(count($scenarios), $dataCollector->getScenarioResult());
        $this->assertEquals($results, $dataCollector->getScenarioResult());
    }

    /**
     * @test
     * @dataProvider getFeatureResult
     */
    public function should_collect_informations_on_feature_result($result)
    {
        $dataCollector = new IssueDataCollector();
        $dataCollector->afterFeature($this->getFeatureEvent($result));

        $this->assertEquals($result, $dataCollector->getFeatureResult());
    }

    public static function getScenarioResult()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array(
                    array('result' => StepEvent::PASSED,    'title' => 'The first feature'),
                    array('result' => StepEvent::SKIPPED,   'title' => 'The second feature'),
                    array('result' => StepEvent::PENDING,   'title' => 'The third feature'),
                    array('result' => StepEvent::UNDEFINED, 'title' => 'The fourth feature'),
                    array('result' => StepEvent::FAILED,    'title' => 'The fifth feature'),
                ),
                array(
                    'The first feature'  => StepEvent::PASSED,
                    'The second feature' => StepEvent::SKIPPED,
                    'The third feature'  => StepEvent::PENDING,
                    'The fourth feature' => StepEvent::UNDEFINED,
                    'The fifth feature'  => StepEvent::FAILED,
                ),
            ),
        );
    }

    public static function getFeatureResult()
    {
        return array(
            array(StepEvent::PASSED),
            array(StepEvent::SKIPPED),
            array(StepEvent::PENDING),
            array(StepEvent::UNDEFINED),
            array(StepEvent::FAILED),
        );
    }

    private function getScenarioEvent($title, $result)
    {
        $mock = $this->getMockBuilder('Behat\Behat\Event\ScenarioEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getScenario')
            ->will($this->returnValue($this->getScenario($title)))
        ;

        $mock
            ->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($result))
        ;

        return $mock;
    }

    private function getFeatureEvent($result)
    {
        $mock = $this->getMockBuilder('Behat\Behat\Event\FeatureEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mock
            ->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($result))
        ;

        return $mock;
    }

    public function getScenario($title)
    {
        $mock = $this->getMockBuilder('Behat\Gherkin\Node\ScenarioNode')
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($title))
        ;

        return $mock;
    }
}
