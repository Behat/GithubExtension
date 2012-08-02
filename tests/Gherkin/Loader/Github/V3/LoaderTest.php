<?php

namespace Gherkin\Loader\Github\V3;

use Behat\Gherkin\Node\FeatureNode;

use Behat\GithubExtension\Cache\FeatureSuiteCache;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getResourceNames
     */
    public function should_verify_if_supports($input, $expected)
    {
        $loader = $this->getLoader();

        $this->assertEquals($expected, $loader->supports($input), 'Path is well caclulcated');
    }

    /**
     * @test
     */
    public function should_load_from_github()
    {
        $loader = $this->getLoader();

        $this->assertInternalType('array', $loader->load('github'), 'Loads an array');
    }

    public function getResourceNames()
    {
        return array(
            array('test', false),
            array('github:', true),
            array('github://', true),
            array('test-github://', false),
            array('test-github.com', true),
        );
    }

    private function getLoader()
    {
        $mock = $this->getMockBuilder('Behat\GithubExtension\Gherkin\Loader\Github\V3\Loader')
            ->setConstructorArgs(array(
                $this->getParser(),
                $this->getClient(),
                'test',
                'test',
                new FeatureSuiteCache('/tmp')
            ))
            ->setMethods(array('getLastModifiedIssuesTimestamp', 'getIssues'))
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getLastModifiedIssuesTimestamp')
            ->will($this->returnValue(time()))
        ;

        $mock->expects($this->any())
            ->method('getIssues')
            ->will($this->returnValue(array()))
        ;

        return $mock;
    }

    private function getParser()
    {
        $mock = $this->getMockBuilder('Behat\Gherkin\Parser')
            ->disableOriginalConstructor()
            ->setMethods(array('parse'))
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('parse')
            ->will($this->returnValue(new FeatureNode))
        ;

        return $mock;
    }

    private function getClient()
    {
        $mock = $this->getMockBuilder('Github\Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return $mock;
    }
}
