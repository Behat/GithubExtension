<?php

namespace Behat\GithubExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Behat\Extension\ExtensionInterface;

class Extension implements ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
        $loader->load('core.xml');

        if (isset($config['user'])) {
            $container->setParameter('behat.github_extension.user', $config['user']);
        }

        if (isset($config['repository'])) {
            $container->setParameter('behat.github_extension.repository', $config['repository']);
        }

        if (isset($config['github_issue_url_pattern'])) {
            $container->setParameter('behat.github_extension.github_issue_url_pattern', $config['github_issue_url_pattern']);
        }

        if (isset($config['cache_path'])) {
            $container->setParameter('behat.github_extension.cache.feature_suite.path', $config['cache_path']);
        }

        if (isset($config['auth'])) {
            $container->setParameter('behat.github_extension.auth', $config['auth']);
        }

        if ($config['write_comments']) {
            $loader->load('listener.xml');
        }

    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder->
            children()->
                scalarNode('user')->
                    defaultNull()->
                end()->
                scalarNode('repository')->
                    defaultNull()->
                end()->
                scalarNode('github_issue_url_pattern')->
                    defaultValue('#https?://github.com/(.*)/(.*)/issues/(\d+)#')->
                end()->
                scalarNode('cache_path')->
                    defaultValue('.git')->
                end()->
                booleanNode('write_comments')->
                    defaultValue(false)->
                end()->
                arrayNode('auth')->
                    children()->
                        scalarNode('username')->
                            defaultNull()->
                        end()->
                        booleanNode('always')->
                            defaultFalse()->
                        end()->
                        scalarNode('password')->
                            defaultNull()->
                        end()->
                        scalarNode('token')->
                            defaultNull()->
                        end()->
                    end()->
                end()->
            end()->
        end();
    }

    public function getCompilerPasses()
    {
        return array();
    }
}
