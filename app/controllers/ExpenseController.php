<?php
/**
 * Expense Controller
 * Handles CRUD for farm expenses.
 */

class ExpenseController
{
    private ExpenseModel $expenseModel;
    private CropModel $cropModel;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->cropModel = new CropModel();
    }

    /**
     * View: List all expenses for a farm.
     */
    public function index()
    {
        $user = current_user();
        role_gate(['owner', 'accountant']);

        $page = (int) ($_GET['page'] ?? 1);
        $filters = [
            'category'   => $_GET['category'] ?? null,
            'crop_id'    => $_GET['crop_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date'   => $_GET['end_date'] ?? null,
        ];

        $result = $this->expenseModel->getByFarm($user['farm_id'], $filters, $page);
        $crops = $this->cropModel->getAll(); // For the dropdown filter/form

        view('owner/expenses/index', [
            'title'      => 'Farm Expenses',
            'expenses'   => $result['data'],
            'pagination' => $result,
            'filters'    => $filters,
            'crops'      => $crops,
            'categories' => ['seed', 'fertilizer', 'pesticide', 'labor', 'equipment', 'fuel', 'utilties', 'other']
        ]);
    }

    /**
     * Web: Store a new expense.
     */
    public function store()
    {
        $user = current_user();
        role_gate(['owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/owner/expenses');
        }

        require_csrf();

        $data = [
            'farm_id'      => $user['farm_id'],
            'crop_id'      => !empty($_POST['crop_id']) ? (int) $_POST['crop_id'] : null,
            'category'     => $_POST['category'] ?? 'other',
            'amount'       => (float) ($_POST['amount'] ?? 0),
            'currency'     => $_POST['currency'] ?? 'GHS',
            'description'  => $_POST['description'] ?? '',
            'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
        ];

        if ($data['amount'] <= 0) {
            $_SESSION['flash_error'] = 'Amount must be greater than zero.';
            redirect('/owner/expenses');
        }

        try {
            $expenseId = $this->expenseModel->insert($data);
            AuditService::logAction('create', 'expense', $expenseId);
            $_SESSION['flash_success'] = 'Expense recorded successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to save expense: ' . $e->getMessage();
        }

        redirect('/owner/expenses');
    }

    /**
     * Web: Delete an expense.
     */
    public function delete()
    {
        $user = current_user();
        role_gate(['owner']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/owner/expenses');
        }

        require_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($this->expenseModel->delete($id)) {
            AuditService::logAction('delete', 'expense', $id);
            $_SESSION['flash_success'] = 'Expense deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete expense or expense not found.';
        }

        redirect('/owner/expenses');
    }
}
