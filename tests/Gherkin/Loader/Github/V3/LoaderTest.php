<?php

namespace Gherkin\Loader\Github\V3;

use Behat\GithubExtension\Gherkin\Loader\Github\V3\Loader;

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

    public static function getResourceNames()
    {
        return array(
            array('test', true),
            array('github:', true),
            array('github://', true),
            array('test-github://', true),
            array('test-github.com', true),
        );
    }

    private function getLoader()
    {
        return new Loader($this->getFetcher());
    }

    private function getFetcher()
    {
        $mock = $this->getMockBuilder('Behat\GithubExtension\Issue\GithubFetcher')
            ->setConstructorArgs(array(
                $this->getClient(),
                $this->getParser(),
                'test',
                'test',
                new FeatureSuiteCache('/tmp'),
            ))
            ->setMethods(array('fetchIssues'))
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('fetchIssues')
            ->will($this->returnValue(json_decode(file_get_contents(__DIR__.'/mock.json'), true)))
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
