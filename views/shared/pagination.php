<?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
<div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 sm:px-6 mt-4 rounded-xl">
    <div class="flex flex-1 justify-between sm:hidden">
        <?php if ($pagination['page'] > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])); ?>" class="relative inline-flex items-center rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Previous</a>
        <?php endif; ?>
        <?php if ($pagination['page'] < $pagination['total_pages']): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])); ?>" class="relative ml-3 inline-flex items-center rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Next</a>
        <?php endif; ?>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-700 dark:text-slate-300">
                Showing
                <span class="font-medium"><?php echo ($pagination['page'] - 1) * $pagination['per_page'] + 1; ?></span>
                to
                <span class="font-medium"><?php echo min($pagination['page'] * $pagination['per_page'], $pagination['total']); ?></span>
                of
                <span class="font-medium"><?php echo $pagination['total']; ?></span>
                results
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])); ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 focus:z-20 focus:outline-offset-0">
                        <span class="sr-only">Previous</span>
                        <span class="material-symbols-outlined">chevron_left</span>
                    </a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $pagination['page'] - 2);
                $end = min($pagination['total_pages'], $pagination['page'] + 2);
                
                if ($start > 1) echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:outline-offset-0">...</span>';
                
                for ($i = $start; $i <= $end; $i++): 
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i === $pagination['page'] ? 'bg-primary text-slate-900 focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary' : 'text-slate-900 dark:text-slate-100 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 focus:z-20 focus:outline-offset-0'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end < $pagination['total_pages']) echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 focus:outline-offset-0">...</span>'; ?>

                <?php if ($pagination['page'] < $pagination['total_pages']): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])); ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-400 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 focus:z-20 focus:outline-offset-0">
                        <span class="sr-only">Next</span>
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>
<?php endif; ?>
