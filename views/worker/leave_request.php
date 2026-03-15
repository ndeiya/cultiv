<?php include 'views/layouts/app_header.php'; ?>

<main class="container py-4">
    <div class="row items-center mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Request Leave</h1>
            <p class="text-muted">Submit a new leave request for approval.</p>
        </div>
        <div class="col-auto">
            <a href="/worker/leave/history" class="btn btn-outline">View My History</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <?php foreach ($balances as $balance): ?>
            <div class="card p-4">
                <h3 class="h4 text-capitalize"><?php echo str_replace('_', ' ', e($balance['leave_type'])); ?></h3>
                <div class="display-4 fw-bold mb-1">
                    <?php echo e($balance['total_days'] - $balance['used_days']); ?>
                </div>
                <div class="text-muted">Days Available (<?php echo e($balance['year']); ?>)</div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card p-4">
        <form action="/worker/leave/request" method="POST">
            <?php csrf_field(); ?>
            
            <div class="row gap-4 mb-4">
                <div class="col">
                    <label for="leave_type" class="form-label">Leave Type</label>
                    <select name="leave_type" id="leave_type" class="form-control" required>
                        <option value="annual">Annual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="unpaid">Unpaid Leave</option>
                        <option value="casual">Casual Leave</option>
                    </select>
                </div>
            </div>

            <div class="row grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="col">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="reason" class="form-label">Reason / Notes</label>
                <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Optional: Provide more context about your request..."></textarea>
            </div>

            <div class="row justify-end">
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
</main>

<?php include 'views/layouts/app_footer.php'; ?>
