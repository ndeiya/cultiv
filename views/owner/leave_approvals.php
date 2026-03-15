<?php include 'views/layouts/app_header.php'; ?>

<main class="container py-4">
    <div class="row items-center mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Leave Approvals</h1>
            <p class="text-muted">Review and manage pending leave requests from your team.</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center p-4">No pending leave requests.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($req['worker_name']); ?></div>
                                    <div class="text-muted text-xs text-uppercase"><?php echo e($req['role']); ?></div>
                                </td>
                                <td class="text-capitalize"><?php echo str_replace('_', ' ', e($req['leave_type'])); ?></td>
                                <td>
                                    <?php echo date('M d', strtotime($req['start_date'])); ?> - <?php echo date('M d, Y', strtotime($req['end_date'])); ?>
                                </td>
                                <td><?php echo e($req['total_days']); ?></td>
                                <td class="text-sm max-w-xs overflow-hidden"><?php echo e($req['reason']); ?></td>
                                <td>
                                    <div class="row gap-2">
                                        <form action="/owner/leave/update-status" method="POST" class="d-inline">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo e($req['id']); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="toggleRejectForm(<?php echo $req['id']; ?>)">Reject</button>
                                    </div>
                                    
                                    <!-- Simple hidden rejection form -->
                                    <div id="reject-form-<?php echo $req['id']; ?>" class="mt-2" style="display: none;">
                                        <form action="/owner/leave/update-status" method="POST">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo e($req['id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <textarea name="rejection_reason" class="form-control mb-2" placeholder="Rejection reason..." required></textarea>
                                            <div class="row gap-2">
                                                <button type="submit" class="btn btn-sm btn-danger">Confirm Reject</button>
                                                <button type="button" class="btn btn-sm btn-ghost" onclick="toggleRejectForm(<?php echo $req['id']; ?>)">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function toggleRejectForm(id) {
    const form = document.getElementById('reject-form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include 'views/layouts/app_footer.php'; ?>
