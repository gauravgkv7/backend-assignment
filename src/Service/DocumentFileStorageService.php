<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class DocumentFileStorageService
{
    private string $storageDirectory;
    private Filesystem $filesystem;

    public function __construct(string $storageDirectory)
    {
        $this->storageDirectory = $storageDirectory;
        $this->filesystem = new Filesystem();
    }

    /**
     * Decode the base64 certificate and store it as a PDF file.
     *
     * @param string $certificateBase64 Base64-encoded certificate.
     * @param string $description Description of the document for naming.
     * @param string $docNo Document number.
     *
     * @return string File path where the document is stored.
     * @throws FileException
     * @throws IOExceptionInterface
     */
    public function storeFile(string $certificateBase64, string $description, string $docNo): string
    {
        // Decode the base64 certificate
        $fileContent = base64_decode($certificateBase64);
        if ($fileContent === false) {
            throw new FileException('Failed to decode base64 certificate.');
        }

        // Generate file name and path
        $fileName = sprintf('%s_%s.pdf', $description, $docNo);
        $filePath = $this->storageDirectory . DIRECTORY_SEPARATOR . $fileName;

        // Ensure the directory exists
        if (!$this->filesystem->exists($this->storageDirectory)) {
            $this->filesystem->mkdir($this->storageDirectory);
        }

        // Save the decoded file
        try {
            $this->filesystem->dumpFile($filePath, $fileContent);
        } catch (IOExceptionInterface $e) {
            throw new FileException('Error writing file to disk: ' . $e->getMessage());
        }

        return $filePath;
    }
}
