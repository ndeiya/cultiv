<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #22c55e; padding-bottom: 10px; margin-bottom: 20px; }
        .farm-info { margin-bottom: 20px; }
        .payslip-title { font-size: 20px; font-weight: bold; color: #22c55e; text-transform: uppercase; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 10px; padding-bottom: 5px; text-uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 8px; background-color: #f8fafc; border-bottom: 1px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .summary-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 4px; }
        .net-pay { font-size: 18px; font-weight: bold; color: #166534; }
        .footer { margin-top: 40px; font-size: 10px; text-align: center; color: #777; font-style: italic; }
        .row { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="col">
            <div class="payslip-title">Payslip</div>
            <div style="margin-top: 5px;">Period: <?php echo date('M d', strtotime($data['period_start'])); ?> - <?php echo date('M d, Y', strtotime($data['period_end'])); ?></div>
        </div>
        <div class="col text-right">
            <strong><?php echo e($data['farm_name']); ?></strong><br>
            <?php echo e($data['farm_address']); ?>
        </div>
    </div>

    <div class="section">
        <div class="row">
            <div class="col">
                <div class="section-title">Employee Details</div>
                <strong><?php echo e($data['name']); ?></strong><br>
                Emp ID: <?php echo e($data['employee_id'] ?: 'N/A'); ?><br>
                Phone: <?php echo e($data['phone']); ?>
            </div>
            <div class="col text-right">
                <div class="section-title">Payment Info</div>
                Payment Method: <?php echo e(ucfirst($data['payment_method'] ?? 'N/A')); ?><br>
                Date: <?php echo date('M d, Y'); ?>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Earnings</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Regular Pay (<?php echo number_format($data['total_hours'], 1); ?> Hrs)</td>
                    <td class="text-right"><?php echo number_format($data['regular_pay'], 2); ?></td>
                </tr>
                <?php if ($data['overtime_pay'] > 0): ?>
                <tr>
                    <td>Overtime Pay</td>
                    <td class="text-right"><?php echo number_format($data['overtime_pay'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($data['bonus_amount'] > 0): ?>
                <tr>
                    <td>Bonus</td>
                    <td class="text-right"><?php echo number_format($data['bonus_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Gross Earnings</strong></td>
                    <td class="text-right"><strong><?php echo number_format($data['gross_pay'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Deductions</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data['paye_deduction'] > 0): ?>
                <tr>
                    <td>PAYE (Income Tax)</td>
                    <td class="text-right"><?php echo number_format($data['paye_deduction'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($data['ssnit_employee'] > 0): ?>
                <tr>
                    <td>SSNIT (Employee Contribution)</td>
                    <td class="text-right"><?php echo number_format($data['ssnit_employee'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($data['other_deductions'] > 0): ?>
                <tr>
                    <td>Other Deductions (Advance/Leave)</td>
                    <td class="text-right"><?php echo number_format($data['other_deductions'], 2); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total Deductions</strong></td>
                    <td class="text-right"><strong><?php echo number_format($data['paye_deduction'] + $data['ssnit_employee'] + $data['other_deductions'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="summary-box">
        <div class="row">
            <div class="col">
                <div class="net-pay">NET PAY</div>
            </div>
            <div class="col text-right">
                <div class="net-pay">GHS <?php echo number_format($data['net_pay'], 2); ?></div>
            </div>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated document and does not require a signature.<br>
        Generated by Cultiv Farm Management System — <?php echo date('Y-m-d H:i:s'); ?>
    </div>
</body>
</html>
