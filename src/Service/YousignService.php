<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YousignService
{
    private const PATHFILE = __DIR__ . '/../../var/storage/';

    public function __construct(private readonly HttpClientInterface $yousignClient)
    {
    }

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function signatureRequest(): array
    {
        $response = $this->yousignClient->request(
            'POST',
            'signature_requests',
            [
                'body' => json_encode([
                    'name' => 'Contrat de location',
                    'delivery_mode' => 'email',
                    'timezone' => 'Europe/Paris'
                ], JSON_THROW_ON_ERROR),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 201) {
            throw new RuntimeException('Error while creating signature request');
        }

        return $response->toArray();
    }

    /**
     * @param string $signatureRequestId
     * @param string $filename
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function uploadDocument(string $signatureRequestId, string $filename): array
    {
        $formFields = [
            'nature' => 'signable_document',
            'file' => DataPart::fromPath(self::PATHFILE . $filename, $filename, 'application/pdf')
        ];

        $formData = new FormDataPart($formFields);
        $headers = $formData->getPreparedHeaders()->toArray();

        $response = $this->yousignClient->request(
            'POST',
            sprintf('signature_requests/%s/documents', $signatureRequestId),
            [
                'headers' => $headers,
                'body' => $formData->bodyToIterable()
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 201) {
            throw new RuntimeException('Error while uploading document');
        }

        return $response->toArray();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function addSigner(
        string $signatureRequestId,
        string $documentId,
        string $email,
        string $firstname,
        string $lastname
    ): array {
        $response = $this->yousignClient->request(
            'POST',
            sprintf('signature_requests/%s/signers', $signatureRequestId),
            [
                'body' => json_encode([
                    'info' => [
                        'first_name' => $firstname,
                        'last_name' => $lastname,
                        'email' => $email,
                        'locale' => 'fr'
                    ],
                    'fields' => [
                        [
                            'type' => 'signature',
                            'document_id' => $documentId,
                            'page' => 1,
                            'x' => 77,
                            'y' => 581
                        ]
                    ],
                    'signature_level' => 'electronic_signature',
                    'signature_authentication_mode' => 'no_otp'
                ], JSON_THROW_ON_ERROR),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 201) {
            throw new RuntimeException('Error while adding signer');
        }

        return $response->toArray();
    }

    /**
     * @param string $email
     * @param string $signatureRequestId
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function addFollower(string $email, string $signatureRequestId): array
    {
        $response = $this->yousignClient->request(
            'POST',
            sprintf('signature_requests/%s/followers', $signatureRequestId),
            [
                'body' => json_encode([
                    [
                        'email' => $email,
                        'locale' => 'fr'
                    ]
                ], JSON_THROW_ON_ERROR),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 201) {
            throw new RuntimeException('Error while adding follower');
        }

        return $response->toArray();
    }

    /**
     * @param string $signatureRequestId
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function activateSignatureRequest(string $signatureRequestId): array
    {
        $response = $this->yousignClient->request(
            'POST',
            sprintf('signature_requests/%s/activate', $signatureRequestId)
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 201) {
            throw new RuntimeException('Error while activating signature request');
        }

        return $response->toArray();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function downloadDocuments(string $signaturerequestId): string
    {
        $response = $this->yousignClient->request(
            'GET',
            sprintf('signature_requests/%s/documents/download?version=completed&archive=false', $signaturerequestId),
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Error while download documents');
        }

        return $response->getContent();
    }
}
