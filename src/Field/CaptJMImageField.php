<?php

// src/Field/CaptJMImageField.php

namespace CaptJM\ImageBrowserBundle\Field;

use CaptJM\ImageBrowserBundle\Form\CaptJMImageType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

/**
 * EasyAdmin 4.x field for picking/uploading images via the file browser.
 *
 * Requires easycorp/easyadmin-bundle ^4.0 (listed as a "suggest" in composer.json,
 * not a hard dependency, so the bundle can be used in projects without EasyAdmin).
 *
 * Usage in a CrudController:
 *
 *   public function configureFields(string $pageName): iterable
 *   {
 *       yield CaptJMImageField::new('image')
 *           ->setBrowserRoot('uploads/products');
 *   }
 */
final class CaptJMImageField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param string      $propertyName  Entity property that holds the image path.
     * @param string|null $label         Column/field label shown in the UI.
     */
    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(CaptJMImageType::class)
            ->setFormTypeOption('browser_root', 'uploads')
            ->setCustomOption('browserRoot', 'uploads')
            ->setTemplatePath('@CaptJMImageBrowser/field/captjm_image.html.twig');
    }

    /**
     * Override the root directory shown in the file browser for this field.
     *
     * @param string $path Relative path inside the uploads directory, e.g. 'products'.
     */
    public function setBrowserRoot(string $path): self
    {
        $normalized = trim($path, '/');
        $this->dto->setCustomOption('browserRoot', $normalized);
        $this->dto->setFormTypeOption('browser_root', $normalized);
        return $this;
    }
}
