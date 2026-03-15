/**
 * Attendance Manager
 * Captures GPS and sends clock-in / clock-out requests.
 */

function updateMessage(msg, isError = false) {
    const msgEl = document.getElementById('attendance-message');
    if (!msgEl) return;
    
    msgEl.textContent = msg;
    msgEl.className = `text-xs font-bold text-center mt-3 ${isError ? 'text-red-500' : 'text-green-500'}`;
    msgEl.classList.remove('hidden');
    
    setTimeout(() => {
        msgEl.classList.add('hidden');
    }, 5000);
}

function getLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error("Geolocation is not supported by your browser."));
        } else {
            navigator.geolocation.getCurrentPosition(
                position => resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                }),
                error => {
                    let msg = "Failed to get location.";
                    switch(error.code) {
                        case error.PERMISSION_DENIED: msg = "Location permission denied."; break;
                        case error.POSITION_UNAVAILABLE: msg = "Location information unavailable."; break;
                        case error.TIMEOUT: msg = "Location request timed out."; break;
                    }
                    reject(new Error(msg));
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    });
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

window.addEventListener('cultiv-sync', async () => {
    try {
        const queue = await CultivStore.getAttendanceQueue();
        if (!queue || queue.length === 0) return;
        
        console.log('Syncing attendance records...', queue.length);
        for (const record of queue) {
            try {
                const endpoint = record.type === 'clock-in' ? '/api/attendance/clock-in' : '/api/attendance/clock-out';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify(record.coords)
                });
                if (response.ok) {
                    await CultivStore.removeAttendanceRecord(record.id);
                }
            } catch (e) {
                console.error('Failed to sync record', record, e);
            }
        }
        updateMessage("Offline attendance synced!", false);
    } catch(err) {
        console.error("Sync error", err);
    }
});

async function clockIn() {
    try {
        updateMessage("Acquiring GPS location...", false);
        const btn = document.getElementById('btn-clock-in');
        if(btn) btn.disabled = true;

        const coords = await getLocation();
        
        if (!navigator.onLine) {
            await CultivStore.addAttendanceRecord({ type: 'clock-in', coords });
            updateMessage("Offline: Clock-in saved locally.", false);
            setTimeout(() => window.location.reload(), 1500);
            return;
        }
        
        // Include device fingerprint for security
        const deviceFingerprint = typeof DeviceFingerprint !== 'undefined' ? DeviceFingerprint.get() : null;
        
        const response = await fetch('/api/attendance/clock-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                ...coords,
                device_fingerprint: deviceFingerprint
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || "Failed to clock in.");
        }

        updateMessage("Clocked in successfully!", false);
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (err) {
        updateMessage(err.message, true);
        const btn = document.getElementById('btn-clock-in');
        if(btn) btn.disabled = false;
    }
}

async function clockOut() {
    try {
        updateMessage("Acquiring GPS location...", false);
        const btn = document.getElementById('btn-clock-out');
        if(btn) btn.disabled = true;

        const coords = await getLocation();
        
        if (!navigator.onLine) {
            await CultivStore.addAttendanceRecord({ type: 'clock-out', coords });
            updateMessage("Offline: Clock-out saved locally.", false);
            setTimeout(() => window.location.reload(), 1500);
            return;
        }

        // Include device fingerprint for security
        const deviceFingerprint = typeof DeviceFingerprint !== 'undefined' ? DeviceFingerprint.get() : null;
        
        const response = await fetch('/api/attendance/clock-out', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                ...coords,
                device_fingerprint: deviceFingerprint
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || "Failed to clock out.");
        }

        updateMessage("Clocked out successfully!", false);
        setTimeout(() => window.location.reload(), 1500);
        
    } catch (err) {
        updateMessage(err.message, true);
        const btn = document.getElementById('btn-clock-out');
        if(btn) btn.disabled = false;
    }
}
