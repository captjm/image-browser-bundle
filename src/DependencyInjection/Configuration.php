<?php

// src/DependencyInjection/Configuration.php

namespace CaptJM\ImageBrowserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('captjm_image_browser');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('uploads_dir')
                    ->info('Absolute path to the uploads directory. Defaults to %kernel.project_dir%/public/uploads.')
                    ->defaultValue('%kernel.project_dir%/public/uploads')
                ->end()
                ->scalarNode('uploads_web_path')
                    ->info('Public URL prefix for uploaded files (no trailing slash).')
                    ->defaultValue('/uploads')
                ->end()
                ->arrayNode('allowed_extensions')
                    ->info('Permitted image file extensions (lowercase, without dot).')
                    ->scalarPrototype()->end()
                    ->defaultValue(['jpg', 'jpeg', 'png', 'gif', 'webp'])
                ->end()
                ->integerNode('max_file_size')
                    ->info('Maximum upload size in bytes. Default: 5 MB.')
                    ->defaultValue(5 * 1024 * 1024)
                    ->min(1)
                ->end()
                ->scalarNode('cif_dist_url')
                    ->info(
                        'URL to the compiled CIF Manager JS file (dist/editor.js from captjm/ckeditor-five-editor). ' .
                        'Example: "/build/cif/editor.js" or a CDN URL. ' .
                        'The script must be loadable from the browser.'
                    )
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
