<?php
/**
 * Dashboard Controller
 * Renders role-specific dashboards with access control.
 */

require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Worker Dashboard
     */
    public function workerDashboard(): void
    {
        role_gate(['worker']);
        $user = current_user();

        $stats = $this->dashboardService->getWorkerStats($user['id']);

        view('worker/dashboard', [
            'user'       => $user,
            'page_title' => 'Dashboard',
            'stats'      => $stats
        ]);
    }

    /**
     * Supervisor Dashboard
     */
    public function supervisorDashboard(): void
    {
        role_gate(['supervisor', 'owner']);
        $user = current_user();

        $stats = $this->dashboardService->getSupervisorStats($user['farm_id']);

        view('supervisor/dashboard', [
            'user'       => $user,
            'page_title' => 'Dashboard',
            'stats'      => $stats
        ]);
    }

    /**
     * Owner Dashboard
     */
    public function ownerDashboard(): void
    {
        role_gate(['owner']);
        $user = current_user();

        $stats = $this->dashboardService->getOwnerStats($user['farm_id']);

        view('owner/dashboard', [
            'user'       => $user,
            'page_title' => 'Dashboard',
            'stats'      => $stats
        ]);
    }

    /**
     * Accountant Dashboard
     */
    public function accountantDashboard(): void
    {
        role_gate(['accountant', 'owner']);
        $user = current_user();

        $stats = $this->dashboardService->getAccountantStats($user['farm_id']);

        view('accountant/dashboard', [
            'user'       => $user,
            'page_title' => 'Dashboard',
            'stats'      => $stats
        ]);
    }
}
