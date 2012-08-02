<?php

namespace Cache;

use Behat\Gherkin\Node\FeatureNode;

use Behat\GithubExtension\Cache\FeatureSuiteCache;

/**
 * Class documentation
 *
 * @author     Florian Klein <florian.klein@free.fr>
 */
class FeatureSuiteCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider getNames
     */
    public function should_get_cache_path($input, $expected)
    {
        $cache = new FeatureSuiteCache(__DIR__.'/tmp');
        $path = $cache->getPathFor($input);

        $this->assertEquals($expected, $path, 'Path is well caclulcated');
    }

    public function getNames()
    {
        return array(
            array('test1'          , __DIR__.'/tmp/'.'test1')          ,
            array('/test1'         , __DIR__.'/tmp/'.'test1')          ,
            array('/test2'         , __DIR__.'/tmp/'.'test2')          ,
            array('test/test/test' , __DIR__.'/tmp/'.'test/test/test') ,
        );
    }

    /**
     * @test
     */
    public function should_say_if_fresh()
    {
        $cache = new FeatureSuiteCache(__DIR__.'/tmp');

        touch(__DIR__.'/tmp/existant');

        $this->assertFalse($cache->isFresh('inexistant', time()), 'inexistant cache file is not fresh');
        $this->assertTrue($cache->isFresh('existant', time() - 10), 'existant cache file is fresh');
        $this->assertFalse($cache->isFresh('existant', time() + 10), 'existant cache file which reprensenting a modified resource since is not fresh');
    }

    /**
     * @test
     */
    public function should_read_array_of_scenario_nodes()
    {
        $cache = new FeatureSuiteCache(__DIR__.'/tmp');

        $features = $cache->read('cache');
        $this->assertInternalType('array', $features, 'Reads an array of FeatureNode objects');
        $this->assertInstanceOf('Behat\Gherkin\Node\FeatureNode', $features[0], 'Contains FeatureNode object');
    }

    /**
     * @test
     */
    public function should_write_array_of_scenario_nodes_to_filsesystem()
    {
        $cache = new FeatureSuiteCache(__DIR__.'/tmp');

        $features = array(new FeatureNode);

        $bytes = $cache->write('written_cache', $features);
        $this->assertEquals(strlen(serialize($features)), $bytes, 'writes to filesystem');
    }
}

