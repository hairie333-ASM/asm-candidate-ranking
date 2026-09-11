/**
 * API Client Helper for ASM Candidate Ranking & Due Diligence System
 */

const API = {
  baseUrl: '',

  async request(endpoint, options = {}) {
    const defaultHeaders = {
      'Accept': 'application/json'
    };

    if (options.body && !(options.body instanceof FormData)) {
      defaultHeaders['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }

    const config = {
      ...options,
      headers: {
        ...defaultHeaders,
        ...(options.headers || {})
      },
      credentials: 'same-origin' // Ensures HttpOnly cookies are included
    };

    try {
      const response = await fetch(endpoint, config);
      
      // Handle Unauthorized
      if (response.status === 401 && !endpoint.includes('/api/auth/')) {
        window.dispatchEvent(new CustomEvent('asm:unauthorized'));
        return { success: false, error: 'Session expired. Please log in again.' };
      }

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const data = await response.json();
        return { ...data, status: response.status, ok: response.ok };
      } else {
        return { ok: response.ok, status: response.status, response };
      }
    } catch (err) {
      console.error('API Request Error:', err);
      return { success: false, error: 'Network communication failure. Please check connection.' };
    }
  },

  get(endpoint) {
    return this.request(endpoint, { method: 'GET' });
  },

  post(endpoint, body) {
    return this.request(endpoint, { method: 'POST', body });
  },

  put(endpoint, body) {
    return this.request(endpoint, { method: 'PUT', body });
  },

  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  },

  upload(endpoint, formData) {
    return fetch(endpoint, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    }).then(res => res.json());
  }
};
