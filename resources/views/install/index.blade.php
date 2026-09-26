<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install — SEMIZZY ONE</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #020617 0%, #0f172a 50%, #020617 100%);
            min-height: 100vh; color: #f1f5f9; -webkit-font-smoothing: antialiased;
        }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { width: 100%; max-width: 640px; }
        .center { text-align: center; }
        .logo {
            width: 64px; height: 64px; border-radius: 18px; margin: 0 auto 16px;
            background: linear-gradient(135deg, #34d399, #0d9488);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.25);
        }
        .logo svg { width: 36px; height: 36px; color: #fff; }
        h1 { font-size: 30px; font-weight: 700; letter-spacing: -0.02em; }
        .sub { color: #94a3b8; margin-top: 4px; font-size: 15px; }
        .ver { color: #64748b; font-size: 13px; margin-top: 12px; }
        .steps { display: flex; align-items: center; justify-content: center; margin: 32px 0; }
        .step {
            width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 12px; font-weight: 600;
            background: #1e293b; color: #64748b; transition: all .3s;
        }
        .step.active { background: rgba(16,185,129,.2); color: #34d399; box-shadow: 0 0 0 2px #10b981; }
        .step.done { background: #10b981; color: #fff; }
        .step-line { width: 28px; height: 2px; background: #1e293b; margin: 0 6px; transition: background .3s; }
        .step-line.done { background: #10b981; }
        .step svg { width: 15px; height: 15px; }
        .panel {
            background: rgba(15, 23, 42, .8); border: 1px solid #1e293b;
            border-radius: 16px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.5);
        }
        h2 { font-size: 20px; font-weight: 600; margin-bottom: 4px; }
        .lead { color: #94a3b8; font-size: 14px; margin-bottom: 24px; }
        .check-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 14px; border-radius: 10px; background: rgba(30,41,59,.5);
            border: 1px solid #1e293b; margin-bottom: 8px;
        }
        .check-left { display: flex; align-items: center; gap: 12px; }
        .icon { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center; }
        .icon.ok { background: rgba(16,185,129,.2); color: #34d399; }
        .icon.bad { background: rgba(239,68,68,.2); color: #f87171; }
        .icon svg { width: 14px; height: 14px; }
        .check-label { font-size: 14px; }
        .check-detail { font-size: 12px; color: #64748b; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-1 { grid-column: 1 / -1; }
        .mb { margin-bottom: 16px; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        label { display: block; font-size: 12px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; }
        input {
            width: 100%; padding: 10px 12px; background: #1e293b; border: 1px solid #334155;
            border-radius: 8px; color: #f1f5f9; font-size: 14px; outline: none;
        }
        input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 28px; gap: 12px; }
        .btn {
            padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px;
            font-weight: 500; cursor: pointer; transition: all .2s; font-family: inherit;
        }
        .btn-primary { background: #10b981; color: #fff; }
        .btn-primary:hover:not(:disabled) { background: #059669; }
        .btn-ghost { background: transparent; color: #94a3b8; }
        .btn-ghost:hover { color: #f1f5f9; }
        .btn-outline { background: transparent; color: #cbd5e1; border: 1px solid #334155; }
        .btn-outline:hover:not(:disabled) { border-color: #475569; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .alert { margin-top: 16px; padding: 12px 14px; border-radius: 10px; font-size: 13px; }
        .alert-ok { background: rgba(16,185,129,.1); color: #34d399; border: 1px solid rgba(16,185,129,.2); }
        .alert-bad { background: rgba(239,68,68,.1); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .divider { border: none; border-top: 1px solid #1e293b; margin: 24px 0; }
        .done-icon {
            width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 16px;
            background: rgba(16,185,129,.2); color: #34d399;
            display: flex; align-items: center; justify-content: center;
        }
        .done-icon svg { width: 32px; height: 32px; }
        .notice { margin-top: 24px; padding: 14px; border-radius: 10px; text-align: left;
                  background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.2); }
        .notice-title { color: #fbbf24; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
        .notice-text { color: rgba(251,191,36,.8); font-size: 12px; line-height: 1.5; }
        code { padding: 1px 5px; background: rgba(245,158,11,.15); border-radius: 4px; font-size: 11px; }
        .foot { text-align: center; color: #475569; font-size: 12px; margin-top: 24px; }
        a { color: #34d399; text-decoration: none; }
        [hidden] { display: none !important; }
    </style>
</head>
<body>

<div class="wrap">
    <div class="card">

        <div class="center">
            <div class="logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1>SEMIZZY ONE</h1>
            <p class="sub">Everything You Need. One Platform.</p>
            <p class="ver">Installation Wizard &mdash; v2.0.0</p>
        </div>

        <div class="steps" id="steps"></div>

        <div class="panel">

            <!-- Step 1: Requirements -->
            <section data-step="0">
                <h2>Server Requirements</h2>
                <p class="lead">We'll verify your server meets the minimum requirements.</p>

                <div id="req-start" class="center" style="padding:40px 0;">
                    <button class="btn btn-primary" data-action="checkRequirements">Run Server Check</button>
                </div>

                <div id="req-list" hidden></div>

                <div class="actions" id="req-actions" hidden>
                    <span></span>
                    <button class="btn btn-primary" id="req-continue" data-goto="1">Continue</button>
                </div>
            </section>

            <!-- Step 2: Database -->
            <section data-step="1" hidden>
                <h2>Database Configuration</h2>
                <p class="lead">Enter your MySQL database credentials.</p>

                <div class="grid">
                    <div>
                        <label for="db_host">Host</label>
                        <input type="text" id="db_host" value="localhost">
                    </div>
                    <div>
                        <label for="db_port">Port</label>
                        <input type="number" id="db_port" value="3306">
                    </div>
                    <div class="grid-1">
                        <label for="db_database">Database Name</label>
                        <input type="text" id="db_database" placeholder="semizzy_one">
                    </div>
                    <div class="grid-1">
                        <label for="db_username">Username</label>
                        <input type="text" id="db_username" placeholder="db_user">
                    </div>
                    <div class="grid-1">
                        <label for="db_password">Password</label>
                        <input type="password" id="db_password">
                    </div>
                </div>

                <div id="db-alert" class="alert" hidden></div>

                <div class="actions">
                    <button class="btn btn-ghost" data-goto="0">Back</button>
                    <div style="display:flex; gap:12px;">
                        <button class="btn btn-outline" id="db-test" data-action="testDatabase">Test Connection</button>
                        <button class="btn btn-primary" data-goto="2">Continue</button>
                    </div>
                </div>
            </section>

            <!-- Step 3: Application & Admin -->
            <section data-step="2" hidden>
                <h2>Application &amp; Admin</h2>
                <p class="lead">Configure your application and create the administrator account.</p>

                <div class="grid">
                    <div>
                        <label for="app_name">Application Name</label>
                        <input type="text" id="app_name" value="SEMIZZY ONE">
                    </div>
                    <div>
                        <label for="app_url">Application URL</label>
                        <input type="url" id="app_url">
                    </div>
                </div>

                <hr class="divider">

                <div class="mb">
                    <label for="admin_name">Admin Name</label>
                    <input type="text" id="admin_name" placeholder="Administrator">
                </div>
                <div class="mb">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" placeholder="admin@example.com">
                </div>
                <div class="grid">
                    <div>
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password">
                    </div>
                    <div>
                        <label for="admin_password_confirmation">Confirm Password</label>
                        <input type="password" id="admin_password_confirmation">
                    </div>
                </div>

                <div id="install-alert" class="alert" hidden></div>

                <div class="actions">
                    <button class="btn btn-ghost" data-goto="1">Back</button>
                    <button class="btn btn-primary" id="install-btn" data-action="runInstall">Install SEMIZZY ONE</button>
                </div>
            </section>

            <!-- Step 4: Complete -->
            <section data-step="3" hidden>
                <div class="center" style="padding:24px 0;">
                    <div class="done-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2>Installation Complete!</h2>
                    <p class="lead" style="margin-top:8px;">SEMIZZY ONE has been successfully installed.</p>

                    <div class="notice">
                        <p class="notice-title">Security Notice</p>
                        <p class="notice-text">
                            Delete the <code>resources/views/install/</code> directory and remove the
                            <code>/install</code> routes from <code>routes/web.php</code> to prevent
                            unauthorized reinstallation.
                        </p>
                    </div>

                    <div style="margin-top:28px;">
                        <a href="/login" class="btn btn-primary" style="display:inline-block;">Go to Login</a>
                    </div>
                </div>
            </section>

        </div>

        <p class="foot">SEMIZZY ONE CORE v2.0.0</p>
    </div>
</div>

<script>
(function () {
    var STEPS = ['Requirements', 'Database', 'Admin', 'Done'];
    var state = {
        step: 0,
        checked: false,
        requirements: {},
        passed: false,
        testing: false,
        installing: false
    };

    function $(id) { return document.getElementById(id); }
    function el(tag, cls, html) {
        var e = document.createElement(tag);
        if (cls) e.className = cls;
        if (html != null) e.innerHTML = html;
        return e;
    }
    function token() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '';
    }
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }
    function alert(el, ok, msg) {
        el.hidden = !msg;
        el.className = 'alert ' + (ok ? 'alert-ok' : 'alert-bad');
        el.textContent = msg || '';
    }

    /* ---------- Rendering ---------- */
    function render() {
        // Step indicators
        var steps = $('steps');
        steps.innerHTML = '';
        STEPS.forEach(function (label, i) {
            var s = el('div', 'step' + (i < state.step ? ' done' : (i === state.step ? ' active' : '')));
            s.innerHTML = i < state.step
                ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                : String(i + 1);
            steps.appendChild(s);
            if (i < STEPS.length - 1) {
                steps.appendChild(el('div', 'step-line' + (i < state.step ? ' done' : '')));
            }
        });

        // Sections
        document.querySelectorAll('section[data-step]').forEach(function (s) {
            s.hidden = parseInt(s.getAttribute('data-step'), 10) !== state.step;
        });

        // Requirements
        $('req-start').hidden = state.checked;
        $('req-list').hidden = !state.checked;
        $('req-actions').hidden = !state.checked;
        if (state.checked) {
            var list = $('req-list');
            list.innerHTML = '';
            Object.keys(state.requirements).forEach(function (k) {
                var c = state.requirements[k];
                var row = el('div', 'check-row');
                var left = el('div', 'check-left');
                left.appendChild(el('div', 'icon ' + (c.passed ? 'ok' : 'bad')));
                left.lastChild.innerHTML = c.passed
                    ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>';
                left.appendChild(el('span', 'check-label', esc(c.label)));
                row.appendChild(left);
                row.appendChild(el('span', 'check-detail', esc(c.detail)));
                list.appendChild(row);
            });
            $('req-continue').disabled = !state.passed;
        }

        // DB test button
        var dbBtn = $('db-test');
        dbBtn.disabled = state.testing;
        dbBtn.textContent = state.testing ? 'Testing...' : 'Test Connection';

        // Install button
        var inBtn = $('install-btn');
        inBtn.disabled = state.installing;
        inBtn.textContent = state.installing ? 'Installing...' : 'Install SEMIZZY ONE';
    }

    /* ---------- Actions ---------- */
    function checkRequirements() {
        $('req-start').hidden = true;
        $('req-list').hidden = false;
        $('req-list').innerHTML = '<div class="check-row"><span class="check-detail">Checking...</span></div>';
        post('/install/check-requirements', {}).then(function (data) {
            state.requirements = data;
            state.checked = true;
            state.passed = Object.keys(data).every(function (k) { return data[k].passed; });
            render();
        }).catch(function () {
            state.requirements = { error: { label: 'Server check failed', passed: false, detail: 'Could not reach the server' } };
            state.checked = true;
            state.passed = false;
            render();
        });
    }

    function testDatabase() {
        state.testing = true;
        alert($('db-alert'), true, 'Testing connection...');
        render();
        post('/install/test-database', {
            host: $('db_host').value,
            port: $('db_port').value,
            database: $('db_database').value,
            username: $('db_username').value,
            password: $('db_password').value
        }).then(function (data) {
            state.testing = false;
            alert($('db-alert'), data.success, data.message);
            render();
        }).catch(function (e) {
            state.testing = false;
            alert($('db-alert'), false, 'Connection failed: ' + e.message);
            render();
        });
    }

    function runInstall() {
        state.installing = true;
        alert($('install-alert'), true, 'Installing, please wait...');
        render();
        post('/install/run', {
            db_host: $('db_host').value,
            db_port: $('db_port').value,
            db_database: $('db_database').value,
            db_username: $('db_username').value,
            db_password: $('db_password').value,
            app_name: $('app_name').value,
            app_url: $('app_url').value,
            admin_name: $('admin_name').value,
            admin_email: $('admin_email').value,
            admin_password: $('admin_password').value,
            admin_password_confirmation: $('admin_password_confirmation').value
        }).then(function (data) {
            state.installing = false;
            alert($('install-alert'), data.success, data.message);
            if (data.success) {
                state.step = 3;
            }
            render();
        }).catch(function (e) {
            state.installing = false;
            alert($('install-alert'), false, 'Installation failed: ' + e.message);
            render();
        });
    }

    /* ---------- Wire up ---------- */
    var ACTIONS = {
        checkRequirements: checkRequirements,
        testDatabase: testDatabase,
        runInstall: runInstall
    };

    document.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var fn = ACTIONS[btn.getAttribute('data-action')];
            if (fn) fn();
        });
    });

    document.querySelectorAll('[data-goto]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.step = parseInt(btn.getAttribute('data-goto'), 10);
            render();
        });
    });

    // Default the app URL to the current origin
    $('app_url').value = window.location.origin;

    render();
})();
</script>

</body>
</html>