# ALMS Static Frontend

This folder is the framework-free frontend that can be deployed to Vercel while the PHP backend and MySQL database remain on InfinityFree.

## Deploy

1. Upload the repository to Vercel and set the project root to `frontend`.
2. In Vercel, add an environment variable or inline script value for the backend origin if it changes:

```html
<script>window.ALMS_BACKEND_URL = 'https://fcahptibalms.great-site.net';</script>
```

3. On InfinityFree, keep the PHP app deployed at the backend domain and set `ALMS_FRONTEND_URL` to the Vercel URL when possible. Vercel preview domains ending in `.vercel.app` are also accepted by the API CORS guard.

The static frontend talks to:

- `/api/bootstrap.php`
- `/api/session.php`
- `/api/auth.php`
- `/api/register.php`

All requests use cookies and CSRF headers, so the backend domain must allow the configured frontend origin.
