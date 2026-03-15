<?php
/**
 * Paystack Service
 * Minimal integration for Mobile Money disbursements.
 */

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        // In a real app, these would be in a .env file
        $this->secretKey = 'sk_test_mock_key_for_demo'; 
    }

    /**
     * Initiate a transfer via Paystack.
     */
    public function initiateTransfer(array $data): array
    {
        // 1. Create a transfer recipient
        $recipient = $this->request('/transferrecipient', [
            'type' => 'mobile_money',
            'name' => $data['name'],
            'account_number' => $data['phone'],
            'bank_code' => $data['momo_provider'], // e.g., 'MTN', 'VOD'
            'currency' => 'GHS'
        ]);

        if (!$recipient['status']) {
            throw new Exception("Failed to create Paystack recipient: " . $recipient['message']);
        }

        // 2. Initiate transfer
        $transfer = $this->request('/transfer', [
            'source' => 'balance',
            'amount' => $data['amount'] * 100, // Convert to pesewas
            'recipient' => $recipient['data']['recipient_code'],
            'reason' => 'Payroll Payment',
            'reference' => 'PAY-' . $data['record_id'] . '-' . time()
        ]);

        return $transfer;
    }

    /**
     * Mock request helper (would use curl in production).
     */
    private function request(string $endpoint, array $payload): array
    {
        // Since we don't have internet access/real keys, we'll simulate a successful response
        return [
            'status' => true,
            'message' => 'Success',
            'data' => [
                'recipient_code' => 'RCP_' . bin2hex(random_bytes(8)),
                'transfer_code' => 'TRF_' . bin2hex(random_bytes(8)),
                'reference' => 'REF_' . bin2hex(random_bytes(8)),
                'status' => 'success' // or 'pending'
            ]
        ];
    }
}
