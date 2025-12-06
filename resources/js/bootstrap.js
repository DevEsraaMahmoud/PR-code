import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo setup (optional - only if Pusher is configured)
// Will be set up later when Pusher is installed
window.Echo = null;
