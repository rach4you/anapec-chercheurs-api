import axios from 'axios';
import { getToken, clearAuth } from '../auth/auth';

const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

apiClient.interceptors.request.use(function (config) {
  const token = getToken();
  if (token) {
    config.headers.Authorization = 'Bearer ' + token;
  }
  return config;
});

apiClient.interceptors.response.use(
  function (response) {
    return response;
  },
  function (error) {
    const status = error.response ? error.response.status : null;

    if (status === 401) {
      clearAuth();
      window.location.href = '/login';
    }

    // 403: do NOT logout — the page should display a permission error.
    // No other side-effects.

    return Promise.reject(error);
  }
);

export default apiClient;

// Expose the shared, configured client to inline Blade scripts so that
// individual pages can reuse the central Axios instance (with its 401
// interceptor) instead of creating their own.
if (typeof window !== 'undefined') {
  window.apiClient = apiClient;
}

