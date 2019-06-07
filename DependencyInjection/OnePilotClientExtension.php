<?php

namespace OnePilot\ClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @see \Symfony\Component\HttpKernel\DependencyInjection\Extension
 */
class OnePilotClientExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $definition = $container->getDefinition('one_pilot_client.service.authentication');
        $definition->replaceArgument(0, $configs[0]['private_key']);
        $definition->replaceArgument(1, $configs[0]['skip_timestamp_validation'] ?? false);

        $container->setParameter($this->getAlias() . '.mail_from_address', $configs[0]['mail_from_address'] ?? '');
    }
}
