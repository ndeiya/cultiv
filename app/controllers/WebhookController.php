<?php
/**
 * Webhook Controller
 * Handles incoming webhooks from Paystack.
 */

class WebhookController
{
    private PayrollModel $payrollModel;

    public function __construct()
    {
        $this->payrollModel = new PayrollModel();
    }

    public function handlePaystack(): void
    {
        // Verify signature (skipped for demo)
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!$payload) return;

        $event = $payload['event'];
        $data = $payload['data'];

        switch ($event) {
            case 'transfer.success':
                $this->updatePaymentStatus($data['reference'], 'completed');
                break;
            case 'transfer.failed':
            case 'transfer.reversed':
                $this->updatePaymentStatus($data['reference'], 'failed');
                break;
        }

        http_response_code(200);
    }

    private function updatePaymentStatus(string $reference, string $status): void
    {
        // Logic to find transaction by reference and update
        // This would involve a mapping table between paystack reference and local payment ID
    }
}
