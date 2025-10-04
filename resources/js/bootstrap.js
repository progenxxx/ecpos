import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add interceptor to handle 419 errors (CSRF token expiration) automatically
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            // Silently refresh CSRF token and retry the request
            return axios.get('/sanctum/csrf-cookie').then(() => {
                // Get the original request config
                const config = error.config;
                // Retry the original request
                return axios.request(config);
            }).catch(() => {
                // If refresh fails, just reject silently
                return Promise.reject(error);
            });
        }
        return Promise.reject(error);
    }
);

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    cluster:import.meta.env.VITE_PUSHER_APP_CLUSTER,
});
