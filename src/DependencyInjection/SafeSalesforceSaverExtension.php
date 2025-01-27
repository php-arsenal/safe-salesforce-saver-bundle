<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class PhpArsenalSafeSalesforceSaverExtension
 * @package PhpArsenal\SafeSalesforceSaverBundle\DependencyInjection
 */
class SafeSalesforceSaverExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}