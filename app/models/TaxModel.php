<?php
/**
 * Tax Model
 * Database operations for tax configurations (PAYE, SSNIT).
 */

class TaxModel extends BaseModel
{
    protected string $table = 'tax_configurations';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get tax bands for a specific country, tax type, and year.
     * 
     * @param string $countryCode Country code (e.g., 'GH')
     * @param string $taxType Tax type ('PAYE', 'SSNIT_EMPLOYEE', 'SSNIT_EMPLOYER')
     * @param int $year Tax year
     * @return array Array of tax bands
     */
    public function getBands(string $countryCode, string $taxType, int $year): array
    {
        $stmt = $this->scopedQuery('
            SELECT band_from, band_to, rate 
            FROM tax_configurations 
            WHERE country_code = :country_code 
            AND tax_type = :tax_type 
            AND year = :year 
            AND is_active = 1 
            AND tenant_id = :tenant_id
            ORDER BY band_from ASC
        ', [
            'country_code' => $countryCode,
            'tax_type' => $taxType,
            'year' => $year
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a flat rate for tax types that don't use bands (e.g., SSNIT).
     * 
     * @param string $countryCode Country code
     * @param string $taxType Tax type
     * @param int $year Tax year
     * @return float Tax rate (0.0 to 1.0)
     */
    public function getRate(string $countryCode, string $taxType, int $year): float
    {
        $bands = $this->getBands($countryCode, $taxType, $year);
        if (empty($bands)) {
            return 0.0;
        }
        // For flat rates, return the first band's rate
        return (float)$bands[0]['rate'];
    }

    /**
     * Create or update a tax configuration.
     */
    public function saveConfiguration(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        
        // Check if configuration already exists
        $stmt = $this->scopedQuery('
            SELECT id FROM tax_configurations 
            WHERE country_code = :country_code 
            AND tax_type = :tax_type 
            AND year = :year 
            AND band_from = :band_from 
            AND tenant_id = :tenant_id
            LIMIT 1
        ', [
            'country_code' => $data['country_code'],
            'tax_type' => $data['tax_type'],
            'year' => $data['year'],
            'band_from' => $data['band_from']
        ]);
        
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing
            $stmt = $this->scopedQuery('
                UPDATE tax_configurations 
                SET band_to = :band_to, rate = :rate, is_active = :is_active, updated_at = NOW()
                WHERE id = :id AND tenant_id = :tenant_id
            ', [
                'id' => $existing['id'],
                'band_to' => $data['band_to'] ?? null,
                'rate' => $data['rate'],
                'is_active' => $data['is_active'] ?? true
            ]);
            return $existing['id'];
        } else {
            // Insert new
            $stmt = $this->db->prepare('
                INSERT INTO tax_configurations (tenant_id, country_code, tax_type, year, band_from, band_to, rate, is_active)
                VALUES (:tenant_id, :country_code, :tax_type, :year, :band_from, :band_to, :rate, :is_active)
            ');
            $stmt->execute([
                'tenant_id' => $tenantId,
                'country_code' => $data['country_code'],
                'tax_type' => $data['tax_type'],
                'year' => $data['year'],
                'band_from' => $data['band_from'],
                'band_to' => $data['band_to'] ?? null,
                'rate' => $data['rate'],
                'is_active' => $data['is_active'] ?? true
            ]);
            return (int) $this->db->lastInsertId();
        }
    }
}
