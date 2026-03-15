<?php
/**
 * User Model
 * Database operations for the users table.
 */

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find a user by email address.
     * Note: Email lookup may need to work across tenants for login, so we use rawQuery.
     * After login, tenant_id is enforced via session.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->rawQuery('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user by ID (scoped to current tenant).
     */
    public function findById(int $id): ?array
    {
        return parent::findById($id);
    }

    /**
     * Find a user by ID without tenant scoping.
     * Used for session initialization/patching.
     */
    public function findByIdUnscoped(int $id): ?array
    {
        $stmt = $this->rawQuery('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Get all users for a specific farm (scoped to current tenant).
     */
    public function getAllByFarm(int $farmId): array
    {
        $stmt = $this->scopedQuery(
            'SELECT id, name, email, phone, role, status, created_at FROM users WHERE farm_id = :farm_id AND tenant_id = :tenant_id ORDER BY name ASC',
            ['farm_id' => $farmId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new user (automatically includes tenant_id).
     */
    public function create(array $data): int
    {
        $tenantId = $this->getCurrentTenantId();
        $stmt = $this->db->prepare('
            INSERT INTO users (tenant_id, farm_id, name, email, phone, password_hash, role, status)
            VALUES (:tenant_id, :farm_id, :name, :email, :phone, :password_hash, :role, :status)
        ');
        $stmt->execute([
            'tenant_id' => $tenantId,
            'farm_id' => $data['farm_id'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update an existing user (scoped to current tenant).
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE users 
            SET name = :name, email = :email, phone = :phone, role = :role
            WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id
        ', [
            'id' => $id,
            'farm_id' => $data['farm_id'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role']
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Set a user's status (scoped to current tenant).
     */
    public function setStatus(int $id, int $farmId, string $status): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE users SET status = :status WHERE id = :id AND farm_id = :farm_id AND tenant_id = :tenant_id',
            ['id' => $id, 'farm_id' => $farmId, 'status' => $status]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Update a user's profile (name, email, phone) - scoped to current tenant.
     */
    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->scopedQuery('
            UPDATE users 
            SET name = :name, email = :email, phone = :phone, updated_at = NOW()
            WHERE id = :id AND tenant_id = :tenant_id
        ', [
            'id'    => $id,
            'name'  => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update a user's password - scoped to current tenant.
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->scopedQuery(
            'UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id',
            ['id' => $id, 'password_hash' => $hashedPassword]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Get the supervisor (or owner) for a specific farm.
     */
    public function getSupervisorForFarm(int $farmId): ?array
    {
        $stmt = $this->scopedQuery(
            'SELECT * FROM users WHERE farm_id = :farm_id AND role IN ("supervisor", "owner") AND status = "active" AND tenant_id = :tenant_id LIMIT 1',
            ['farm_id' => $farmId]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
