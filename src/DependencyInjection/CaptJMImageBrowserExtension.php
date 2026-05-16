<?php

// src/DependencyInjection/CaptJMImageBrowserExtension.php

namespace CaptJM\ImageBrowserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CaptJMImageBrowserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Pass resolved config values as container parameters
        $container->setParameter('captjm_image_browser.uploads_dir', $config['uploads_dir']);
        $container->setParameter('captjm_image_browser.uploads_web_path', $config['uploads_web_path']);
        $container->setParameter('captjm_image_browser.allowed_extensions', $config['allowed_extensions']);
        $container->setParameter('captjm_image_browser.max_file_size', $config['max_file_size']);
        $container->setParameter('captjm_image_browser.cif_dist_url', $config['cif_dist_url']);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');
    }

    public function getAlias(): string
    {
        return 'captjm_image_browser';
    }
}
