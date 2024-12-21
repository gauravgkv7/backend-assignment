<?php

namespace App\Controller;

use App\Service\DocumentProcessorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    private DocumentProcessorService $documentProcessorService;

    public function __construct(DocumentProcessorService $documentProcessorService)
    {
        $this->documentProcessorService = $documentProcessorService;
    }

    /**
    * @Route("/process-documents", name="process_documents")
    */
    public function processDocuments(): Response
    {
        try {
            $message = $this->documentProcessorService->processDocuments();
            return new Response($message);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }
}
