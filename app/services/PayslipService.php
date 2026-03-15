<?php
/**
 * Payslip Service
 * Handles PDF payslip generation using mPDF.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

class PayslipService
{
    private ?\Mpdf\Mpdf $mpdf = null;

    public function __construct()
    {
        // No eager instantiation to prevent crash if class is missing
    }

    /**
     * Get or instantiate mPDF.
     */
    private function getMpdf(): ?\Mpdf\Mpdf
    {
        if ($this->mpdf !== null) {
            return $this->mpdf;
        }

        try {
            if (!class_exists('\Mpdf\Mpdf')) {
                return null;
            }

            $this->mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);
            
            $this->mpdf->SetTitle('Payslip');
            $this->mpdf->SetAuthor('Cultiv Farm Management');
            
            return $this->mpdf;
        } catch (Throwable $e) {
            error_log("Failed to initialize mPDF: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a PDF payslip for a payroll record.
     */
    public function generate(array $record): string
    {
        $mpdf = $this->getMpdf();
        
        if (!$mpdf) {
            throw new Exception("PDF generation is currently unavailable. Please contact your administrator to ensure the 'mpdf/mpdf' package is correctly installed.");
        }

        // Data preparation
        $data = $this->prepareData($record);
        
        // Load template
        ob_start();
        include dirname(__DIR__, 2) . '/views/shared/payslip_template.php';
        $html = ob_get_clean();
        
        $mpdf->WriteHTML($html);
        
        // Ensure storage directory exists
        $storagePath = dirname(__DIR__, 2) . '/storage/payslips';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        $fileName = 'payslip_' . $record['id'] . '_' . time() . '.pdf';
        $fullPath = $storagePath . '/' . $fileName;
        
        $mpdf->Output($fullPath, \Mpdf\Output\Destination::FILE);
        
        return 'storage/payslips/' . $fileName;
    }

    /**
     * Prepare data for the template.
     */
    private function prepareData(array $record): array
    {
        // Enrich data if needed (e.g., fetch farm details, user details)
        $db = Database::getInstance();
        
        // Fetch User and Farm details
        $stmt = $db->prepare('
            SELECT u.name, u.phone, u.employee_id, f.name as farm_name, f.address as farm_address, f.logo_path
            FROM users u
            JOIN farms f ON u.farm_id = f.id
            WHERE u.id = :user_id
        ');
        $stmt->execute(['user_id' => $record['user_id']]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch Period details
        $stmt = $db->prepare('SELECT * FROM payroll_periods WHERE id = :id');
        $stmt->execute(['id' => $record['payroll_period_id']]);
        $period = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array_merge($record, $details, [
            'period_start' => $period['period_start'],
            'period_end' => $period['period_end']
        ]);
    }
}
