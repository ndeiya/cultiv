<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Cultiv</title>
    <meta name="description" content="Login to Cultiv Farm Workforce & Operations Management System">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
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
        
        /* Animated gradient background */
        .login-bg {
            background: linear-gradient(135deg, #f6f8f6 0%, #e8f5e8 50%, #f6f8f6 100%);
            animation: gradientShift 8s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Subtle float animation for the logo icon */
        .logo-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        
        /* Input focus glow */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(19, 236, 19, 0.15);
        }
        
        /* Button hover effect */
        .btn-primary {
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 25px rgba(19, 236, 19, 0.3);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0px);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center px-4 font-display">

    <div class="w-full max-w-md">
        
        <!-- Logo & App Name -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/20 mb-4 logo-float">
                <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1">eco</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Cultiv</h1>
            <p class="text-sm text-slate-500 mt-1">Farm Workforce & Operations Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl border border-primary/10 shadow-sm p-8">
            
            <h2 class="text-lg font-bold text-slate-900 mb-1">Welcome back</h2>
            <p class="text-sm text-slate-500 mb-6">Sign in to your account to continue</p>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500 text-sm">error</span>
                    <span class="text-sm text-red-600"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="/login" autocomplete="on">
                <!-- CSRF Token -->
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Email Field -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-lg">mail</span>
                        </div>
                        <input 
                            id="email"
                            type="email" 
                            name="email" 
                            required 
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="input-glow w-full pl-10 pr-4 py-3 bg-background-light border-none rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary transition-all"
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-lg">lock</span>
                        </div>
                        <input 
                            id="password"
                            type="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="input-glow w-full pl-10 pr-12 py-3 bg-background-light border-none rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary transition-all"
                        >
                        <button 
                            type="button" 
                            id="toggle-password"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                            onclick="togglePasswordVisibility()"
                        >
                            <span id="eye-icon" class="material-symbols-outlined text-lg">visibility_off</span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    id="login-button"
                    type="submit" 
                    class="btn-primary w-full py-3 bg-primary text-slate-900 font-bold rounded-lg text-sm flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined text-lg">login</span>
                    Sign In
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; <?= date('Y') ?> Cultiv — Farm Management System
        </p>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = 'visibility_off';
            }
        }

        // Prevent double-submit
        document.getElementById('login-form').addEventListener('submit', function() {
            const btn = document.getElementById('login-button');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">progress_activity</span> Signing in...';
        });
    </script>

</body>
</html>
