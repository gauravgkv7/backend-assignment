<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

class DocumentProcessorService
{
    private HttpClientInterface $client;
    private DocumentFileStorageService $fileStorageService;
    private LoggerInterface $logger;
    private string $storageDirectory;

    public function __construct(
        HttpClientInterface $client,
        DocumentFileStorageService $fileStorageService,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->client = $client;
        $this->fileStorageService = $fileStorageService;
        $this->logger = $logger;
        $this->storageDirectory = $params->get('document_storage_directory'); // Configurable directory for storage
    }

    /**
     * Fetch documents from the API, decode and store them.
     *
     * @return string Success message or error.
     */
    public function processDocuments(): string
    {
        try {
            // Fetch data from API
            $response = $this->client->request('GET', 'https://raw.githubusercontent.com/RashitKhamidullin/Educhain-Assignment/refs/heads/main/get-documents');
            $data = $response->toArray();  // Convert JSON response to PHP array
            foreach ($data as $document) {
                $this->processDocument($document);
            }

            return "Certificate generated and stored successfully.";
        } catch (Exception $e) {
            // Log error and throw an exception
            $this->logger->error('Error fetching documents from API: ' . $e->getMessage());
            throw new HttpException(500, 'Failed to fetch documents.');
        }
    }

    /**
     * Process a single document, decode certificate and store the file.
     *
     * @param array $document Document details from the API.
     * @throws HttpException
     */
    private function processDocument(array $document): void
    {
        // Validate document fields
        if (!isset($document['certificate'], $document['description'], $document['doc_no'])) {
            $this->logger->warning('Missing required fields for document', ['doc_no' => $document['doc_no'] ?? 'N/A']);
            return;
        }

        $description = $document['description'];
        $docNo = $document['doc_no'];
        $certificateBase64 = $document['certificate'];

        try {
            // Decode the certificate and store the file
            $filePath = $this->fileStorageService->storeFile($certificateBase64, $description, $docNo);
            $this->logger->info('File stored successfully.', ['file_path' => $filePath]);
        } catch (Exception $e) {
            // Log the error if file storage fails
            $this->logger->error('Error storing file for document ' . $docNo . ': ' . $e->getMessage());
            throw new HttpException(500, 'Failed to store file for document ' . $docNo);
        }
    }
}
