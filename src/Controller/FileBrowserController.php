<?php

// src/Controller/FileBrowserController.php

namespace CaptJM\ImageBrowserBundle\Controller;

use DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/file-browser', name: 'captjm_file_browser_')]
class FileBrowserController extends AbstractController
{
    public function __construct(
        #[Autowire('%captjm_image_browser.uploads_dir%')]
        private readonly string $uploadsDir,

        #[Autowire('%captjm_image_browser.uploads_web_path%')]
        private readonly string $uploadsWebPath,

        #[Autowire('%captjm_image_browser.allowed_extensions%')]
        private readonly array $allowedExtensions,

        #[Autowire('%captjm_image_browser.max_file_size%')]
        private readonly int $maxFileSize,
    ) {}

    #[Route('/browse', name: 'browse', methods: ['GET'])]
    public function browse(Request $request): JsonResponse
    {
        $subPath  = trim($request->query->getString('path'), '/');
        $realBase = realpath($this->uploadsDir);

        if (false === $realBase) {
            return $this->json(['error' => 'Uploads directory does not exist.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $targetDir  = $subPath ? $this->uploadsDir . '/' . $subPath : $this->uploadsDir;
        $realTarget = realpath($targetDir);

        if (!$realTarget || !str_starts_with($realTarget, $realBase)) {
            return $this->json(['error' => 'Invalid path.'], Response::HTTP_BAD_REQUEST);
        }

        $folders = [];
        $files   = [];

        foreach (new DirectoryIterator($realTarget) as $item) {
            if ($item->isDot()) {
                continue;
            }

            $itemPath = $subPath ? $subPath . '/' . $item->getFilename() : $item->getFilename();

            if ($item->isDir()) {
                $folders[] = [
                    'name' => $item->getFilename(),
                    'path' => $itemPath,
                ];
            } elseif ($this->isAllowedImage($item->getFilename())) {
                $files[] = [
                    'name' => $item->getFilename(),
                    'path' => $itemPath,
                    'url'  => rtrim($this->uploadsWebPath, '/') . '/' . $itemPath,
                ];
            }
        }

        usort($folders, static fn($a, $b) => strcmp($a['name'], $b['name']));
        usort($files,   static fn($a, $b) => strcmp($a['name'], $b['name']));

        // Build breadcrumb
        $breadcrumb = [['label' => 'uploads', 'path' => '']];
        if ($subPath) {
            $accumulated = '';
            foreach (explode('/', $subPath) as $part) {
                $accumulated  = $accumulated ? $accumulated . '/' . $part : $part;
                $breadcrumb[] = ['label' => $part, 'path' => $accumulated];
            }
        }

        return $this->json([
            'path'       => $subPath,
            'breadcrumb' => $breadcrumb,
            'folders'    => $folders,
            'files'      => $files,
        ]);
    }

    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file    = $request->files->get('file');
        $subPath = trim($request->request->getString('folder'), '/');

        if (!$file || !$file->isValid()) {
            return $this->json(['error' => 'Invalid or missing file.'], Response::HTTP_BAD_REQUEST);
        }

        if ($file->getSize() > $this->maxFileSize) {
            return $this->json(
                ['error' => sprintf('File exceeds the maximum allowed size (%d bytes).', $this->maxFileSize)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $originalName = $file->getClientOriginalName();

        if (!$this->isAllowedImage($originalName)) {
            return $this->json(
                ['error' => 'Only the following image types are allowed: ' . implode(', ', $this->allowedExtensions)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $realBase  = realpath($this->uploadsDir);
        $targetDir = $subPath ? $this->uploadsDir . '/' . $subPath : $this->uploadsDir;

        if (!str_starts_with($this->normalizePath($targetDir), (string) $realBase)) {
            return $this->json(['error' => 'Invalid target folder.'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0o775, true);
        }

        // Sanitize filename to prevent path traversal / bad chars
        $filename = $this->sanitizeFilename($originalName);
        $file->move($targetDir, $filename);

        $filePath = $subPath ? $subPath . '/' . $filename : $filename;

        return $this->json([
            'name' => $filename,
            'path' => $filePath,
            'url'  => rtrim($this->uploadsWebPath, '/') . '/' . $filePath,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function isAllowedImage(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, \PATHINFO_EXTENSION));
        return \in_array($ext, $this->allowedExtensions, true);
    }

    private function sanitizeFilename(string $filename): string
    {
        // Keep only safe characters; replace spaces with underscores
        $name = pathinfo($filename, \PATHINFO_FILENAME);
        $ext  = strtolower(pathinfo($filename, \PATHINFO_EXTENSION));
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?? 'file';
        return $name . '.' . $ext;
    }

    private function normalizePath(string $path): string
    {
        $parts  = explode('/', str_replace('\\', '/', $path));
        $result = [];
        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($result);
            } elseif ($part !== '.') {
                $result[] = $part;
            }
        }
        return implode('/', $result);
    }
}
