<?php

// src/CaptJMImageBrowserBundle.php

namespace CaptJM\ImageBrowserBundle;

use CaptJM\ImageBrowserBundle\DependencyInjection\CaptJMImageBrowserExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class CaptJMImageBrowserBundle extends AbstractBundle
{
    /**
     * Using AbstractBundle (Symfony 6.1+) instead of the older Bundle base class.
     * This gives us the cleaner loadExtension() / prependExtension() API.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new CaptJMImageBrowserExtension();
        }
        return $this->extension;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
