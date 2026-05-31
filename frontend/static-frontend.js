const ALMS_BACKEND_URL = window.ALMS_BACKEND_URL || 'https://fcahptibalms.great-site.net';

const state = {
    csrfToken: '',
    departments: [],
};

function apiUrl(path) {
    return `${ALMS_BACKEND_URL}${path}`;
}

function showAlert(id, message, type = 'error') {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('success', type === 'success');
    el.hidden = false;
}

async function request(path, options = {}) {
    const res = await fetch(apiUrl(path), {
        credentials: 'include',
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': state.csrfToken,
            ...(options.headers || {}),
        },
    });
    const data = await res.json();
    if (!res.ok || data.success === false) {
        throw new Error(data.message || 'Request failed.');
    }
    if (data.csrf_token) state.csrfToken = data.csrf_token;
    return data;
}

async function loadBootstrap() {
    const data = await request('/api/bootstrap.php', { method: 'GET', headers: {} });
    state.csrfToken = data.csrf_token;
    state.departments = data.departments || [];

    const deptSelect = document.getElementById('department');
    deptSelect.innerHTML = '<option value="">Select Department</option>';
    state.departments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.id;
        option.textContent = `${dept.name} (${dept.level_offered})`;
        option.dataset.levels = dept.level_offered;
        deptSelect.appendChild(option);
    });
    updateLevelAvailability();

    document.querySelectorAll('[data-backend-link]').forEach(link => {
        link.href = apiUrl(link.dataset.backendLink);
    });
}

function updateRoleFields() {
    const role = document.getElementById('role').value;
    document.querySelectorAll('.student-only').forEach(el => { el.hidden = role !== 'student'; });
    document.querySelectorAll('.lecturer-only').forEach(el => { el.hidden = role !== 'lecturer'; });
    updateLevelAvailability();
}

function updateLevelAvailability() {
    const role = document.getElementById('role')?.value || 'student';
    const dept = document.getElementById('department');
    const level = document.getElementById('level');
    if (!dept || !level || role !== 'student') return;

    const selected = dept.options[dept.selectedIndex];
    const ndOnly = selected?.dataset.levels === 'ND ONLY';
    [...level.options].forEach(option => {
        option.disabled = ndOnly && Number(option.value) > 2;
    });
    if (ndOnly && Number(level.value) > 2) level.value = '1';
}

function collectForm(form) {
    return Object.fromEntries(new FormData(form).entries());
}

async function handleLogin(event) {
    event.preventDefault();
    const button = event.currentTarget.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Signing in...';
    try {
        const data = await request('/api/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify(collectForm(event.currentTarget)),
        });
        showAlert('login-alert', 'Signed in. Opening your dashboard...', 'success');
        window.location.href = apiUrl(data.redirect || '/student/dashboard.php');
    } catch (error) {
        showAlert('login-alert', error.message);
    } finally {
        button.disabled = false;
        button.textContent = 'Sign In';
    }
}

async function handleRegister(event) {
    event.preventDefault();
    const button = event.currentTarget.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Creating account...';
    try {
        const payload = collectForm(event.currentTarget);
        const data = await request('/api/register.php', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        showAlert('register-alert', data.message, 'success');
        event.currentTarget.reset();
        updateRoleFields();
    } catch (error) {
        showAlert('register-alert', error.message);
    } finally {
        button.disabled = false;
        button.textContent = 'Create Account';
    }
}

function renderLearningArc() {
    const canvas = document.getElementById('learning-arc');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let frame = 0;

    function resize() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.floor(rect.width * window.devicePixelRatio);
        canvas.height = Math.floor(rect.height * window.devicePixelRatio);
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
    }

    function draw() {
        const width = canvas.clientWidth;
        const height = canvas.clientHeight;
        ctx.clearRect(0, 0, width, height);
        ctx.lineWidth = 1;
        for (let i = 0; i < 8; i++) {
            const y = height * (0.22 + i * 0.075);
            const progress = reduceMotion ? 0 : Math.sin(frame * 0.012 + i) * 16;
            ctx.beginPath();
            ctx.moveTo(width * 0.08, y);
            ctx.bezierCurveTo(width * 0.34, y - 140 + progress, width * 0.66, y + 140 - progress, width * 0.92, y);
            ctx.strokeStyle = `rgba(209, 0, 0, ${0.06 + i * 0.012})`;
            ctx.stroke();
        }
        ctx.fillStyle = 'rgba(209,0,0,0.9)';
        for (let i = 0; i < 18; i++) {
            const x = width * (0.1 + ((i * 0.047 + frame * 0.0008) % 0.82));
            const y = height * (0.28 + ((i * 0.091) % 0.48));
            ctx.beginPath();
            ctx.arc(x, y, 2.2, 0, Math.PI * 2);
            ctx.fill();
        }
        frame++;
        if (!reduceMotion) requestAnimationFrame(draw);
    }

    resize();
    draw();
    window.addEventListener('resize', () => {
        resize();
        if (reduceMotion) draw();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadBootstrap().catch(error => {
        showAlert('login-alert', error.message);
        showAlert('register-alert', error.message);
    });
    document.getElementById('login-form')?.addEventListener('submit', handleLogin);
    document.getElementById('register-form')?.addEventListener('submit', handleRegister);
    document.getElementById('role')?.addEventListener('change', updateRoleFields);
    document.getElementById('department')?.addEventListener('change', updateLevelAvailability);
    updateRoleFields();
    renderLearningArc();
});
