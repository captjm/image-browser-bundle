<?php

// src/Form/CaptJMImageType.php

namespace CaptJM\ImageBrowserBundle\Form;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A Symfony form type that stores an image path as a hidden field
 * and opens the CIF Manager modal to pick or upload images.
 *
 * Usage in a standard form:
 *
 *   $builder->add('image', CaptJMImageType::class, [
 *       'browser_root' => 'uploads/products',  // optional sub-folder
 *   ]);
 */
class CaptJMImageType extends AbstractType
{
    public function __construct(
        #[Autowire('%captjm_image_browser.cif_dist_url%')]
        private readonly string $cifDistUrl,

        #[Autowire('%captjm_image_browser.uploads_web_path%')]
        private readonly string $uploadsWebPath,
    ) {}

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['value']        = $form->getData() ?? '';
        $view->vars['browser_root'] = $options['browser_root'];
        $view->vars['cif_dist_url'] = $this->cifDistUrl;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'browser_root' => 'uploads',
        ]);

        $resolver->setAllowedTypes('browser_root', 'string');
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'captjm_image';
    }
}
