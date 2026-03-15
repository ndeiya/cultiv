<?php include 'views/layouts/app_header.php'; ?>

<main class="container py-4">
    <div class="row items-center mb-4">
        <div class="col">
            <h1 class="h2 mb-0">My Leave History</h1>
            <p class="text-muted">Tracking your leave balances and requests.</p>
        </div>
        <div class="col-auto">
            <a href="/worker/leave/request" class="btn btn-primary">Request New Leave</a>
        </div>
    </div>

    <!-- Balances Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <?php foreach ($balances as $balance): ?>
            <div class="card p-3">
                <div class="text-muted text-sm text-uppercase fw-bold"><?php echo str_replace('_', ' ', e($balance['leave_type'])); ?></div>
                <div class="h3 mb-0"><?php echo e($balance['total_days'] - $balance['used_days']); ?> / <?php echo e($balance['total_days']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center p-4">No leave requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td class="text-capitalize"><?php echo str_replace('_', ' ', e($req['leave_type'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($req['start_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($req['end_date'])); ?></td>
                                <td><?php echo e($req['total_days']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $req['status'] === 'approved' ? 'success' : 
                                            ($req['status'] === 'rejected' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(e($req['status'])); ?>
                                    </span>
                                </td>
                                <td class="text-sm">
                                    <?php echo e($req['reason']); ?>
                                    <?php if ($req['status'] === 'rejected' && $req['rejection_reason']): ?>
                                        <div class="text-danger mt-1"><strong>Reason:</strong> <?php echo e($req['rejection_reason']); ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'views/layouts/app_footer.php'; ?>
