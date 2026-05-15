import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Read CSRF token from the meta tag (set in app.blade.php) for axios requests.
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta instanceof HTMLMetaElement && csrfMeta.content) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
}
