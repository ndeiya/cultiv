<?php
/**
 * Base Layout — App Shell
 * Shared HTML head, sidebar, and mobile nav structure.
 * 
 * Variables available:
 *   $page_title  - Page title
 *   $user        - Current user array
 *   $active_nav  - Active nav item identifier
 *   $content     - (use sections below to inject content)
 */

$user = $user ?? current_user();
$role = $user['role'] ?? 'worker';
$page_title = $page_title ?? 'Dashboard';
$active_nav = $active_nav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Cultiv</title>
    <meta name="description" content="Cultiv Farm Workforce & Operations Management System">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#13ec13">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#13ec13",
                        "background-light": "#f6f8f6",
                        "background-dark": "#102210",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(19, 236, 19, 0.2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(19, 236, 19, 0.4); }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
    
    <div class="flex min-h-screen">
        
        <!-- ═══ Desktop Sidebar ═══ -->
        <aside class="hidden md:flex md:flex-col md:w-64 md:fixed md:inset-y-0 border-r border-primary/20 bg-white dark:bg-background-dark z-40">
            
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-primary/10">
                <div class="w-9 h-9 rounded-lg bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1">eco</span>
                </div>
                <span class="text-lg font-bold text-slate-900 dark:text-white">Cultiv</span>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <?php include VIEWS_PATH . '/layouts/sidebar.php'; ?>
            </nav>
            
            <!-- User Profile -->
            <div class="border-t border-primary/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-primary/20 flex items-center justify-center">
                        <span class="text-sm font-bold text-primary"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate"><?= htmlspecialchars($user['name'] ?? 'User') ?></p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-primary"><?= htmlspecialchars($user['role'] ?? '') ?></p>
                    </div>
                    <a href="/logout" class="text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                        <span class="material-symbols-outlined text-lg">logout</span>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- ═══ Main Content Area ═══ -->
        <main class="flex-1 md:ml-64">
            
            <!-- Sticky Header -->
            <header class="sticky top-0 z-30 backdrop-blur-md bg-white/50 dark:bg-background-dark/50 border-b border-primary/10 px-4 md:px-6 py-3">
                <div class="flex items-center justify-between">
                    <!-- Mobile: Profile + Greeting -->
                    <div class="flex items-center gap-3">
                        <div class="md:hidden w-9 h-9 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="text-sm font-bold text-primary"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold"><?= htmlspecialchars($page_title) ?></h1>
                            <p class="text-xs text-slate-500 md:hidden">Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?></p>
                        </div>
                    </div>
                    
                    <!-- Right side: notification bell -->
                    <a href="/<?= htmlspecialchars($role) ?>/notifications" class="relative p-2 rounded-lg hover:bg-primary/5 transition-colors">
                        <span class="material-symbols-outlined text-slate-500">notifications</span>
                        <!-- Badge dot -->
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </a>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="px-4 md:px-6 py-6 pb-24 md:pb-6">
