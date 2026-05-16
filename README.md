# CaptJM Image Browser Bundle

A reusable Symfony ^6.4 / ^7.0 bundle that provides:

- **`CaptJMImageType`** — Symfony form type (hidden field + CIF Manager modal)
- **`CaptJMImageField`** — EasyAdmin 4 CRUD field (requires `easycorp/easyadmin-bundle ^4.0`)
- **`FileBrowserController`** — secured REST API for listing folders and uploading images

The file browser UI is powered by **CIF Manager** from
[captjm/ckeditor-five-editor](https://github.com/captjm/ckeditor-five-editor) —
a React 18 + TypeScript application. No other frontend dependencies are needed.

---

## Prerequisites — build CIF Manager



---

## Installation

### 1. Require via Composer

```bash
composer require captjm/image-browser-bundle
```

### 2. Register the bundle

If Symfony Flex auto-discovery is not enabled, add it manually:

```php
// config/bundles.php
return [
    CaptJM\ImageBrowserBundle\CaptJMImageBrowserBundle::class => ['all' => true],
];
```

### 3. Import routes

```yaml
# config/routes.yaml
captjm_image_browser:
    resource: '@CaptJMImageBrowserBundle/config/routes.yaml'
```

### 4. Register the form theme

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@CaptJMImageBrowser/form/captjm_image_widget.html.twig'
```

---

## Configuration

```yaml
# config/packages/captjm_image_browser.yaml
captjm_image_browser:

    # URL to the compiled CIF Manager JS (required)
    # Build it from captjm/ckeditor-five-editor → npm run build → dist/cif.js
    cif_dist_url: '/build/cif/cif.js'

    # Absolute server path to the uploads root
    uploads_dir: '%kernel.project_dir%/public/uploads'

    # Public URL prefix for served images (no trailing slash)
    uploads_web_path: '/uploads'

    # Permitted image extensions (lowercase, no dot)
    allowed_extensions: [jpg, jpeg, png, gif, webp]

    # Maximum upload size in bytes (default 5 MB)
    max_file_size: 5242880
```

---

## How the integration works

```
Browser                      Symfony                      CIF Manager (React)
──────                       ───────                      ───────────────────
Form renders hidden input
+ "Browse" button
           │
           │ click
           ▼
CIF script lazy-loaded ──► window.CifManager init
                                    │
                                    │  window.cifOpen(callback, { rootPath, ... })
                                    ▼
                           React modal opens
                                    │
                           GET /admin/file-browser/browse?path=...
                                    │ ◄─── FileBrowserController
                           { folders, files, breadcrumb }
                                    │
                     User double-clicks a file
                                    │
                           callback({ name, path, url })
                                    │
           ◄───────────────────────┘
hidden input.value = file.url
preview <img> updated

---

## Usage

### Standard Symfony form

```php
use CaptJM\ImageBrowserBundle\Form\CaptJMImageType;

$builder->add('coverImage', CaptJMImageType::class, [
    'label'        => 'Cover image',
    'browser_root' => 'uploads/articles',   // optional sub-folder shown on open
    'required'     => false,
]);
```

### EasyAdmin CRUD controller

```php
use CaptJM\ImageBrowserBundle\Field\CaptJMImageField;

public function configureFields(string $pageName): iterable
{
    yield CaptJMImageField::new('coverImage', 'Cover image')
        ->setBrowserRoot('uploads/articles');
}
```

---

## `window.cifOpen` — callback signature

The form widget calls:

```js
window.cifOpen(onFileSelected, {
    rootPath:  'uploads/articles',   // from browser_root
    browseUrl: '/admin/file-browser/browse',
    uploadUrl: '/admin/file-browser/upload',
});
```

`onFileSelected` receives the file object:

```js
function onFileSelected(file) {
    // file = { name: 'hero.jpg', path: 'uploads/articles/hero.jpg', url: '/uploads/articles/hero.jpg' }
    hiddenInput.value = file.url;
}
```

> **Note:** if the exact signature of `window.cifOpen` changes in a future
> release of `captjm/ckeditor-five-editor`, update the `openBtn` click handler
> in `captjm_image_widget.html.twig` accordingly.

---

## API endpoint (FileBrowserController)

The CIF Manager API format matches the controller responses out of the box.

| Route | Method | Description |
|---|---|---|
| `/admin/file-browser/browse` | GET | List folders & files. Query param: `?path=sub/folder` |
| `/admin/file-browser/upload` | POST | Upload an image. Body: `file` (multipart) + `folder` |

**Browse response:**
```json
{
  "path": "uploads/articles",
  "breadcrumb": [
    { "label": "uploads", "path": "" },
    { "label": "articles", "path": "uploads/articles" }
  ],
  "folders": [{ "name": "2025", "path": "uploads/articles/2025" }],
  "files":   [{ "name": "hero.jpg", "path": "uploads/articles/hero.jpg", "url": "/uploads/articles/hero.jpg" }]
}
```

---

## Security

- All routes require `ROLE_ADMIN`.
- Path traversal blocked via `realpath()` comparison.
- Only extensions from `allowed_extensions` are accepted on upload.
- Uploaded filenames are sanitized (alphanumeric, dash, underscore, dot only).

---

## Bundle structure

```
captjm-image-browser-bundle/
├── config/
│   ├── routes.yaml
│   └── services.yaml
├── src/
│   ├── CaptJMImageBrowserBundle.php
│   ├── Controller/FileBrowserController.php
│   ├── DependencyInjection/
│   │   ├── CaptJMImageBrowserExtension.php
│   │   └── Configuration.php
│   ├── Field/CaptJMImageField.php         # EasyAdmin (optional)
│   └── Form/CaptJMImageType.php
├── templates/
│   ├── field/captjm_image.html.twig       # EasyAdmin list/detail view
│   └── form/captjm_image_widget.html.twig # Form theme + CIF bootstrap
├── tests/
├── composer.json
└── README.md
```

---

## License

MIT
