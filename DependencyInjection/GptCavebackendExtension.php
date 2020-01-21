<?php
namespace App\GptCavebackendBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class GptCavebackendExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Is GptCaveBundle loaded?
        if (!isset($container->getParameter('kernel.bundles')['GptCaveBundle'])) {
            throw new \Exception(sprintf('%s must be registered in "kernel.bundles".', 'GptCaveBundle'));
        }


        $Yamlloader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $Yamlloader->load('parameters.yml');
        $Yamlloader->load('services.yml');
    }
}