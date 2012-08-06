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

    public function tearDown()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->tmpDir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if (basename($path->__toString()) !== '.' && basename($path->__toString()) !== '..') {
                if ($path->isDir()) {
                    rmdir($path->__toString());
                } else {
                    unlink($path->__toString());
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider getNames
     */
    public function should_get_cache_path($input, $expected)
    {
        $cache = new FeatureSuiteCache($this->tmpDir);
        $feature = new FeatureNode();
        $feature->setFile($input);
        $path = $cache->getPathFor($feature);

        $this->assertEquals($this->tmpDir.$expected, $path, 'Path is well caclulcated');
    }

    public static function getNames()
    {
        return array(
            array('test1'          , '/features/5a105e8b9d40e1329780d62ea2265d8a')          ,
            array('/test1'         , '/features/c919a18cd1e1254f560bb64acc581574')          ,
            array('/test2'         , '/features/bdbc5e82b59e2d430975cdd123931580')          ,
            array('test/test/test' , '/features/68da1fb7d3d47095d0140bb8ea457c62') ,
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

        return $cache;
    }

    /**
     * @test
     * @depends should_write_array_of_scenario_nodes_to_filsesystem
     */
    public function should_read_array_of_scenario_nodes(FeatureSuiteCache $cache)
    {
        $features = $cache->all();
        $this->assertInternalType('array', $features, 'Reads an array of FeatureNode objects');
        $this->assertInstanceOf('Behat\Gherkin\Node\FeatureNode', $features[0], 'Contains FeatureNode object');
    }
}

