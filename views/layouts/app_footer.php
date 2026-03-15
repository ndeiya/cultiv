            </div><!-- end page content -->
        </main>
    </div>
    
    <!-- ═══ Mobile Bottom Navigation ═══ -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-lg border-t border-primary/10">
        <nav class="flex items-center justify-around py-2 px-2">
            <?php
            $role = $user['role'] ?? 'worker';
            $navFile = match($role) {
                'worker'     => 'worker_nav.php',
                'supervisor' => 'supervisor_nav.php',
                'owner'      => 'owner_nav.php',
                'accountant' => 'accountant_nav.php',
                default      => 'worker_nav.php',
            };
            include VIEWS_PATH . '/layouts/' . $navFile;
            ?>
        </nav>
    </div>
    
    <!-- Offline Store & Service Worker -->
    <script src="/js/offline-store.js"></script>
</body>
</html>
