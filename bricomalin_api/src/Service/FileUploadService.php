<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    private string $uploadDirectory;

    public function __construct(string $uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * Upload un fichier et retourne le chemin relatif
     *
     * @param UploadedFile $file
     * @param string $subdirectory Sous-dossier (ex: 'id-documents')
     * @return string Chemin relatif depuis public/
     * @throws FileException
     */
    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->uploadDirectory;
        if ($subdirectory) {
            $targetDirectory .= '/' . $subdirectory;
        }

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        try {
            $file->move($targetDirectory, $newFilename);
        } catch (FileException $e) {
            throw new FileException('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
        }

        $relativePath = 'uploads';
        if ($subdirectory) {
            $relativePath .= '/' . $subdirectory;
        }
        $relativePath .= '/' . $newFilename;

        return $relativePath;
    }

    /**
     * Supprime un fichier
     */
    public function delete(string $relativePath): bool
    {
        $fullPath = $this->uploadDirectory . '/' . str_replace('uploads/', '', $relativePath);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}

