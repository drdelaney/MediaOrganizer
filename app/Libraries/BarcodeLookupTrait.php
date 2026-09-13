<?php

namespace App\Libraries;

use App\Models\ConfigurationModel;

/**
 * Trait to provide barcode lookup functionality via UPCItemDB
 */
trait BarcodeLookupTrait
{
    /**
     * Find media details by barcode using UPCItemDB
     *
     * @param string $barcode
     * @return array|null
     */
    public function lookupBarcode(string $barcode): ?array
    {
        $configModel = new ConfigurationModel();
        $apiKey = $configModel->getParam('UPCITEMDB_API_KEY');
        $userAgent = $configModel->getParam('user_agent', 'MediaOrganizer/1.0');

        $url = "https://api.upcitemdb.com/prod/trial/lookup?upc=" . urlencode($barcode);
        
        $client = \Config\Services::curlrequest([], null, null, false);
        $headers = [
            'User-Agent' => $userAgent,
            'Accept' => 'application/json',
        ];

        if (!empty($apiKey)) {
            $headers['user_key'] = $apiKey;
            // Use production URL if API key is provided
            $url = "https://api.upcitemdb.com/prod/v1/lookup?upc=" . urlencode($barcode);
        }

        try {
            $response = $client->request('GET', $url, [
                'headers' => $headers,
                'http_errors' => false
            ]);

            if ($response->getStatusCode() !== 200) {
                if ($response->getStatusCode() === 429) {
                    return ['error' => 'EXCEED_LIMIT', 'message' => 'Request limit exceeded. Please try again later.'];
                }
                log_message('error', 'UPCItemDB lookup failed for barcode {barcode}. Status: {status}. Response: {response}', [
                    'barcode' => $barcode,
                    'status' => $response->getStatusCode(),
                    'response' => $response->getBody()
                ]);
                return null;
            }

            $data = json_decode($response->getBody(), true);

            if (empty($data['items'])) {
                return null;
            }

            return $data['items'][0];
        } catch (\Exception $e) {
            log_message('error', 'UPCItemDB lookup exception for barcode {barcode}: {message}', [
                'barcode' => $barcode,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
