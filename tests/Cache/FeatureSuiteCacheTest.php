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

    const TMP_DIR = 'tmp';

    private $tmpDir;

    public function setUp()
    {
        $this->tmpDir = sprintf('%s/%s', __DIR__, self::TMP_DIR);

        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir);
        }
    }

    /**
     * @test
     * @dataProvider getNames
     */
    public function should_get_cache_path($input, $expected)
    {
        $cache = new FeatureSuiteCache($this->tmpDir);
        $path = $cache->getPathFor($input);

        $this->assertEquals($this->tmpDir.$expected, $path, 'Path is well caclulcated');
    }

    public static function getNames()
    {
        return array(
            array('test1'          , '/test1')          ,
            array('/test1'         , '/test1')          ,
            array('/test2'         , '/test2')          ,
            array('test/test/test' , '/test/test/test') ,
        );
    }

    /**
     * @test
     */
    public function should_say_if_fresh()
    {
        $cache = new FeatureSuiteCache($this->tmpDir);

        touch($this->tmpDir.'/existant');

        $this->assertFalse($cache->isFresh('inexistant', time()), 'inexistant cache file is not fresh');
        $this->assertTrue($cache->isFresh('existant', time() - 10), 'existant cache file is fresh');
        $this->assertFalse($cache->isFresh('existant', time() + 10), 'existant cache file which reprensenting a modified resource since is not fresh');
    }

    /**
     * @test
     */
    public function should_write_array_of_scenario_nodes_to_filsesystem()
    {
        $cache = new FeatureSuiteCache($this->tmpDir);

        $features = array(new FeatureNode);

        $bytes = $cache->write('written_cache', $features);
        $this->assertEquals(strlen(serialize($features)), $bytes, 'writes to filesystem');
    }

    /**
     * @test
     * @depends should_write_array_of_scenario_nodes_to_filsesystem
     */
    public function should_read_array_of_scenario_nodes()
    {
        $cache = new FeatureSuiteCache($this->tmpDir);

        $features = $cache->read('written_cache');
        $this->assertInternalType('array', $features, 'Reads an array of FeatureNode objects');
        $this->assertInstanceOf('Behat\Gherkin\Node\FeatureNode', $features[0], 'Contains FeatureNode object');
    }
}

