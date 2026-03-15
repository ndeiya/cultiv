<?php
/**
 * Production Controller
 * Handles production record management for piece-rate pay.
 */

class ProductionController extends BaseController
{
    private ProductionModel $productionModel;
    private CropModel $cropModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->productionModel = new ProductionModel();
        $this->cropModel = new CropModel();
        $this->userModel = new UserModel();
    }

    /**
     * View: Show production recording form
     */
    public function create(): void
    {
        require_role(['supervisor', 'owner']);
        $user = current_user();
        
        // Get workers and crops for dropdowns
        $workers = $this->userModel->getAllByFarm($user['farm_id']);
        $crops = $this->cropModel->getAllByFarm($user['farm_id']);
        
        view('supervisor/production_form', [
            'user' => $user,
            'page_title' => 'Record Production',
            'workers' => $workers,
            'crops' => $crops
        ]);
    }

    /**
     * Store: Save production record
     */
    public function store(): void
    {
        require_role(['supervisor', 'owner']);
        require_csrf();
        $user = current_user();
        
        $data = [
            'farm_id' => $user['farm_id'],
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'crop_id' => !empty($_POST['crop_id']) ? (int)$_POST['crop_id'] : null,
            'record_date' => $_POST['record_date'] ?? date('Y-m-d'),
            'unit_type' => $_POST['unit_type'] ?? 'kg',
            'quantity' => (float)($_POST['quantity'] ?? 0),
            'unit_rate' => (float)($_POST['unit_rate'] ?? 0),
            'notes' => sanitize_input($_POST['notes'] ?? ''),
            'recorded_by' => $user['id']
        ];
        
        if ($data['user_id'] <= 0 || $data['quantity'] <= 0 || $data['unit_rate'] <= 0) {
            flash('error', 'Please fill in all required fields with valid values.');
            redirect('/supervisor/production/create');
            return;
        }
        
        $this->productionModel->create($data);
        AuditService::logAction('create', 'production_record');
        
        flash('success', 'Production record saved successfully.');
        redirect('/supervisor/production');
    }

    /**
     * View: List production records
     */
    public function index(): void
    {
        require_role(['supervisor', 'owner']);
        $user = current_user();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $records = $this->productionModel->getFarmProductionByDate($user['farm_id'], $date);
        
        view('supervisor/production_list', [
            'user' => $user,
            'page_title' => 'Production Records',
            'records' => $records,
            'current_date' => $date
        ]);
    }

    /**
     * API: Get production records for a worker
     */
    public function apiGetWorkerProduction(): void
    {
        require_role(['supervisor', 'owner', 'worker']);
        $user = current_user();
        
        $userId = (int)($_GET['user_id'] ?? $user['id']);
        
        // Workers can only see their own records
        if ($user['role'] === 'worker' && $userId !== $user['id']) {
            json_response(['error' => true, 'message' => 'Access denied'], 403);
            return;
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $records = $this->productionModel->getUserProduction($userId, $startDate, $endDate);
        $total = $this->productionModel->getTotalProductionAmount($userId, $startDate, $endDate);
        
        json_response([
            'success' => true,
            'records' => $records,
            'total_amount' => $total
        ]);
    }

    /**
     * API: Create production record
     */
    public function apiStore(): void
    {
        require_role(['supervisor', 'owner']);
        require_csrf();
        $user = current_user();
        
        $input = get_json_body();
        
        $data = [
            'farm_id' => $user['farm_id'],
            'user_id' => (int)($input['user_id'] ?? 0),
            'crop_id' => !empty($input['crop_id']) ? (int)$input['crop_id'] : null,
            'record_date' => $input['record_date'] ?? date('Y-m-d'),
            'unit_type' => $input['unit_type'] ?? 'kg',
            'quantity' => (float)($input['quantity'] ?? 0),
            'unit_rate' => (float)($input['unit_rate'] ?? 0),
            'notes' => $input['notes'] ?? '',
            'recorded_by' => $user['id']
        ];
        
        if ($data['user_id'] <= 0 || $data['quantity'] <= 0 || $data['unit_rate'] <= 0) {
            json_response(['error' => true, 'message' => 'Invalid data provided'], 400);
            return;
        }
        
        $id = $this->productionModel->create($data);
        AuditService::logAction('create', 'production_record', $id);
        
        json_response([
            'success' => true,
            'id' => $id,
            'message' => 'Production record created successfully'
        ]);
    }
}
