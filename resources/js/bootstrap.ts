import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Use the XSRF-TOKEN cookie (set by Laravel on every response) rather than
// the meta-tag token. The meta tag is only rendered on a full page load, so
// it goes stale after Inertia SPA navigations that regenerate the session.
// Axios reads XSRF-TOKEN automatically via xsrfCookieName / xsrfHeaderName.
