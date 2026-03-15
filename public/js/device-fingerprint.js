/**
 * Device Fingerprinting
 * Generates a unique device fingerprint to prevent 'buddy punching'.
 * Combines multiple device characteristics for uniqueness.
 */

class DeviceFingerprint {
    /**
     * Generate a device fingerprint.
     * Combines browser, screen, timezone, and hardware characteristics.
     */
    static generate() {
        const components = {
            // Browser characteristics
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            
            // Screen characteristics
            screenWidth: screen.width,
            screenHeight: screen.height,
            colorDepth: screen.colorDepth,
            pixelRatio: window.devicePixelRatio || 1,
            
            // Timezone
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            timezoneOffset: new Date().getTimezoneOffset(),
            
            // Canvas fingerprint (browser rendering differences)
            canvas: this.getCanvasFingerprint(),
            
            // WebGL fingerprint
            webgl: this.getWebGLFingerprint(),
            
            // Audio context fingerprint
            audio: this.getAudioFingerprint(),
            
            // Hardware concurrency
            hardwareConcurrency: navigator.hardwareConcurrency || 0,
            
            // Device memory (if available)
            deviceMemory: navigator.deviceMemory || 0
        };
        
        // Create a hash of all components
        const fingerprintString = JSON.stringify(components);
        return this.hashString(fingerprintString);
    }
    
    /**
     * Get canvas fingerprint.
     */
    static getCanvasFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 200;
            canvas.height = 50;
            
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.textBaseline = 'alphabetic';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('Cultiv Device ID', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('Cultiv Device ID', 4, 17);
            
            return canvas.toDataURL();
        } catch (e) {
            return 'canvas_not_available';
        }
    }
    
    /**
     * Get WebGL fingerprint.
     */
    static getWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            
            if (!gl) {
                return 'webgl_not_available';
            }
            
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (debugInfo) {
                return {
                    vendor: gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL),
                    renderer: gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL)
                };
            }
            
            return {
                vendor: gl.getParameter(gl.VENDOR),
                renderer: gl.getParameter(gl.RENDERER)
            };
        } catch (e) {
            return 'webgl_error';
        }
    }
    
    /**
     * Get audio context fingerprint.
     */
    static getAudioFingerprint() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) {
                return 'audio_not_available';
            }
            
            const context = new AudioContext();
            const oscillator = context.createOscillator();
            const analyser = context.createAnalyser();
            const gainNode = context.createGain();
            const scriptProcessor = context.createScriptProcessor(4096, 1, 1);
            
            gainNode.gain.value = 0; // Mute
            oscillator.type = 'triangle';
            oscillator.connect(analyser);
            analyser.connect(scriptProcessor);
            scriptProcessor.connect(gainNode);
            gainNode.connect(context.destination);
            
            oscillator.start(0);
            
            return 'audio_available';
        } catch (e) {
            return 'audio_error';
        }
    }
    
    /**
     * Hash a string using a simple hash function.
     * For production, consider using a more robust hashing library.
     */
    static hashString(str) {
        let hash = 0;
        if (str.length === 0) return hash.toString();
        
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        
        return Math.abs(hash).toString(16);
    }
    
    /**
     * Store fingerprint in sessionStorage for the session.
     */
    static store() {
        const fingerprint = this.generate();
        try {
            sessionStorage.setItem('device_fingerprint', fingerprint);
        } catch (e) {
            // SessionStorage not available
        }
        return fingerprint;
    }
    
    /**
     * Get stored fingerprint or generate new one.
     */
    static get() {
        try {
            return sessionStorage.getItem('device_fingerprint') || this.store();
        } catch (e) {
            return this.generate();
        }
    }
}

// Auto-generate and store fingerprint on page load
if (typeof window !== 'undefined') {
    DeviceFingerprint.store();
}
