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
        if (isset($config['password'])) {
            $container->setParameter('behat.github_extension.password', $config['password']);
        }
        if (isset($config['repository'])) {
            $container->setParameter('behat.github_extension.repository', $config['repository']);
        }
        if (isset($config['auth'])) {
            $auth = [
                'username' => $config['auth']['username'],
                'password' => $config['auth']['password'],
                'token'    => $config['auth']['token'],
            ];
            $container->setParameter('behat.github_extension.auth', $auth);
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
                scalarNode('repository')->
                    defaultNull()->
                end()->
                scalarNode('user')->
                    defaultNull()->
                end()->
                scalarNode('password')->
                    defaultNull()->
                end()->
                arrayNode('auth')->
                    children()->
                        scalarNode('username')->
                            defaultNull()->
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
        return [];
    }
}
