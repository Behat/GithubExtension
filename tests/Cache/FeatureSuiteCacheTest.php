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
    private $feature;

    public function setUp()
    {
        $this->tmpDir = sprintf('%s/%s', __DIR__, self::TMP_DIR);
        $this->feature = new FeatureNode;

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
            array('test1'          , '/features/test1')          ,
            array('/test1'         , '/features/test1')          ,
            array('/test2'         , '/features/test2')          ,
            array('test/test/test' , '/features/test/test/test') ,
        );
    }

    /**
     * @test
     */
    public function should_write_array_of_scenario_nodes_to_filsesystem()
    {
        $cache = new FeatureSuiteCache($this->tmpDir);

        $bytes = $cache->write($this->feature);
        $this->assertEquals(strlen(serialize($this->feature)), $bytes, 'writes to filesystem');
    }

    /**
     * @test
     * @depends should_write_array_of_scenario_nodes_to_filsesystem
     */
    public function should_read_array_of_scenario_nodes()
    {
        $cache = new FeatureSuiteCache($this->tmpDir);

        $features = $cache->all();
        $this->assertInternalType('array', $features, 'Reads an array of FeatureNode objects');
        $this->assertInstanceOf('Behat\Gherkin\Node\FeatureNode', $features[0], 'Contains FeatureNode object');
    }
}

