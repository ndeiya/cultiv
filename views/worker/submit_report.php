<?php require __DIR__ . '/../layouts/app_header.php'; ?>

<!-- Main Content -->
<div class="max-w-4xl mx-auto px-4 py-8 mb-24 md:mb-0">
    <!-- Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight mb-2">Submit Daily Report</h2>
            <p class="text-slate-500 dark:text-slate-400">Record your daily activities, observations, and maintenance logs.</p>
        </div>
        <a href="/worker/reports" class="px-4 py-2 text-sm font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
            View History
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow-sm">
            <p class="font-bold flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </p>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="/worker/reports" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <!-- Report Type Selection -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">category</span>
                Report Type
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="category" value="crop" class="peer sr-only" required>
                    <div class="flex flex-col items-center justify-center p-4 border-2 border-slate-100 dark:border-slate-800 rounded-xl peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition-all">
                        <span class="material-symbols-outlined text-3xl mb-1">eco</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Crops</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="category" value="animal" class="peer sr-only">
                    <div class="flex flex-col items-center justify-center p-4 border-2 border-slate-100 dark:border-slate-800 rounded-xl peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition-all">
                        <span class="material-symbols-outlined text-3xl mb-1">pets</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Livestock</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="category" value="equipment" class="peer sr-only">
                    <div class="flex flex-col items-center justify-center p-4 border-2 border-slate-100 dark:border-slate-800 rounded-xl peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition-all">
                        <span class="material-symbols-outlined text-3xl mb-1">construction</span>
                        <span class="text-xs font-bold uppercase tracking-wider">Equipment</span>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="category" value="general" class="peer sr-only" checked>
                    <div class="flex flex-col items-center justify-center p-4 border-2 border-slate-100 dark:border-slate-800 rounded-xl peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition-all">
                        <span class="material-symbols-outlined text-3xl mb-1">assignment</span>
                        <span class="text-xs font-bold uppercase tracking-wider">General</span>
                    </div>
                </label>
            </div>
        </section>

        <!-- Activity Details -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">pending_actions</span>
                Activity Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Related ID (Optional)</label>
                    <input type="number" name="related_id" placeholder="ID of Crop/Animal/Equipment" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Severity</label>
                    <select name="severity" class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary p-3">
                        <option value="low">Low (Normal)</option>
                        <option value="medium">Medium (Issue)</option>
                        <option value="high">High (Critical)</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Description -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">notes</span>
                    Observations & Notes
                </h3>
            </div>
            <textarea name="description" rows="4" required placeholder="Describe the work completed or any issues encountered..." class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary resize-none p-4"></textarea>
        </section>

        <!-- Photo Upload -->
        <section class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-primary/10 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_a_photo</span>
                Attachments
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="photo-preview-container">
                <label class="aspect-square rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 hover:border-primary/50 hover:text-primary transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-3xl">add</span>
                    <span class="text-xs font-bold mt-1 uppercase tracking-wider">Upload Photo</span>
                    <input type="file" name="photos[]" multiple accept="image/*" class="hidden" onchange="previewImages(event)">
                </label>
                <!-- Previews added via JS -->
            </div>
        </section>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pt-4">
            <button type="button" onclick="saveDraft()" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                <span>Save Draft</span>
                <span class="material-symbols-outlined">save</span>
            </button>
            <button type="submit" class="px-8 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:brightness-95 hover:shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                <span>Submit Report</span>
                <span class="material-symbols-outlined">send</span>
            </button>
        </div>
    </form>
</div>

<script>
function previewImages(event) {
    const files = event.target.files;
    const container = document.getElementById('photo-preview-container');
    
    // Remove existing previews
    const previews = container.querySelectorAll('.photo-preview');
    previews.forEach(p => p.remove());

    for (let i = 0; i < files.length; i++) {
        if (i >= 3) break; // limit to 3 previews for UI space
        
        const file = files[i];
        if (!file.type.startsWith('image/')) continue;
        
        const reader = new FileReader();
        reader.readAsDataURL(file);
    }
}

/**
 * Phase 5.1: Client-side photo compression
 */
async function compressImage(file, maxWidth = 800, quality = 0.7) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > maxWidth) {
                    height = (maxWidth / width) * height;
                    width = maxWidth;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const compressedDataUrl = canvas.toDataURL('image/jpeg', quality);
                resolve(compressedDataUrl);
            };
        };
    });
}

async function saveDraft() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    
    // Compress photos if present
    const photos = [];
    const photoInput = form.querySelector('input[type="file"]');
    if (photoInput.files.length > 0) {
        for (let i = 0; i < Math.min(photoInput.files.length, 3); i++) {
            const compressed = await compressImage(photoInput.files[i]);
            photos.push(compressed);
        }
    }

    const report = {
        category: formData.get('category'),
        severity: formData.get('severity'),
        description: formData.get('description'),
        related_id: formData.get('related_id'),
        photos: photos // Base64 compressed strings
    };

    try {
        await CultivStore.addToOutbox('/api/reports/create', 'POST', report, { type: 'report' });
        alert('You are offline. Report saved to outbox and will sync automatically when online.');
        window.location.href = '/worker/reports';
    } catch(e) {
        alert('Failed to save to outbox.');
    }
}

document.querySelector('form').addEventListener('submit', async (e) => {
    if (!navigator.onLine) {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = 'Saving Offline...';
        await saveDraft();
    }
});
</script>

<?php require __DIR__ . '/../layouts/app_footer.php'; ?>
