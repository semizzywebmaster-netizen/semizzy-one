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
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 14px; border-radius: 10px; background: rgba(30,41,59,.5);
            border: 1px solid #1e293b; margin-bottom: 8px;
        }
        .check-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .icon { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center; }
        .icon.ok { background: rgba(16,185,129,.2); color: #34d399; }
        .icon.bad { background: rgba(239,68,68,.2); color: #f87171; }
        .icon.warn { background: rgba(245,158,11,.2); color: #fbbf24; }
        .icon svg { width: 14px; height: 14px; }
        .check-label { font-size: 14px; }
        .check-detail { font-size: 12px; color: #64748b; text-align: right; word-break: break-word; }
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
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 28px; gap: 12px; flex-wrap: wrap; }
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
        .alert-warn { background: rgba(245,158,11,.1); color: #fbbf24; border: 1px solid rgba(245,158,11,.2); }
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
        .req-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .req-summary { font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>

@php
    // ── Server-side requirement checks ──────────────────────────────
    // Rendered server-side so the list is ALWAYS visible, even if the
    // JavaScript fetch to /install/check-requirements fails.
    $reqPhpOk    = version_compare(PHP_VERSION, '8.2.0', '>=');
    $reqExts     = ['bcmath','ctype','curl','dom','fileinfo','json','mbstring','openssl',
                    'pdo_mysql','tokenizer','xml','zip','gd','intl','sodium'];
    $reqMissing  = array_values(array_diff($reqExts, get_loaded_extensions()));
    $reqExtsOk   = empty($reqMissing);
    $reqStorOk   = is_writable(storage_path());
    $reqCacheOk  = is_writable(base_path('bootstrap/cache'));
    $reqEnvOk    = file_exists(base_path('.env'));
    $reqKeyOk    = !empty(config('app.key'));
    $reqAllOk    = $reqPhpOk && $reqExtsOk && $reqStorOk && $reqCacheOk && $reqEnvOk && $reqKeyOk;
    $reqRows = [
        ['PHP version >= 8.2',            $reqPhpOk,   'PHP ' . PHP_VERSION],
        ['Required PHP extensions',       $reqExtsOk,  $reqExtsOk ? count($reqExts) . ' loaded' : 'Missing: ' . implode(', ', $reqMissing)],
        ['Storage directory writable',    $reqStorOk,  is_writable(storage_path()) ? 'writable' : 'not writable'],
        ['Bootstrap cache writable',      $reqCacheOk, is_writable(base_path('bootstrap/cache')) ? 'writable' : 'not writable'],
        ['.env file exists',              $reqEnvOk,   $reqEnvOk ? 'found' : 'not found'],
        ['Application key (APP_KEY)',     $reqKeyOk,   $reqKeyOk ? 'set' : 'not set'],
    ];
@endphp

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
                <p class="lead">Your server meets the minimum requirements.</p>

                <div class="req-toolbar">
                    <span class="req-summary" id="req-summary">
                        @if($reqAllOk)
                            All {{ count($reqRows) }} checks passed
                        @else
                            {{ count(array_filter($reqRows, fn($r) => !$r[1])) }} check(s) need attention
                        @endif
                    </span>
                    <button class="btn btn-outline" id="req-recheck" data-action="recheck" style="padding:7px 14px; font-size:13px;">Re-check</button>
                </div>

                <div id="req-list">
                    @foreach($reqRows as $reqRow)
                        <div class="check-row">
                            <div class="check-left">
                                <div class="icon {{ $reqRow[1] ? 'ok' : 'bad' }}">
                                    @if($reqRow[1])
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                </div>
                                <span class="check-label">{{ $reqRow[0] }}</span>
                            </div>
                            <span class="check-detail">{{ $reqRow[2] }}</span>
                        </div>
                    @endforeach
                </div>

                <div id="req-alert" class="alert {{ $reqAllOk ? 'alert-ok' : 'alert-bad' }}">
                    @if($reqAllOk)
                        Your server is ready. You can continue to the next step.
                    @else
                        Some requirements are not met. Fix the items marked in red before continuing.
                    @endif
                </div>

                <div class="actions">
                    <span></span>
                    <button class="btn btn-primary" id="req-continue" data-goto="1" @if(!$reqAllOk) disabled @endif>Continue</button>
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
                        <button class="btn btn-outline" id="db-test-btn" data-action="testDatabase">Test Connection</button>
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
    var state = { step: 0, testing: false, installing: false };

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
    function setAlert(node, kind, msg) {
        node.hidden = !msg;
        node.className = 'alert ' + (kind === 'ok' ? 'alert-ok' : (kind === 'warn' ? 'alert-warn' : 'alert-bad'));
        node.textContent = msg || '';
    }

    /* ---------- Rendering ---------- */
    function render() {
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

        document.querySelectorAll('section[data-step]').forEach(function (s) {
            s.hidden = parseInt(s.getAttribute('data-step'), 10) !== state.step;
        });

        var dbBtn = $('db-test-btn');
        dbBtn.disabled = state.testing;
        dbBtn.textContent = state.testing ? 'Testing...' : 'Test Connection';

        var inBtn = $('install-btn');
        inBtn.disabled = state.installing;
        inBtn.textContent = state.installing ? 'Installing...' : 'Install SEMIZZY ONE';
    }

    /* ---------- Actions ---------- */

    // Re-check requirements against the live server and rebuild the list.
    function recheck() {
        var btn = document.querySelector('[data-action="recheck"]');
        var list = $('req-list');
        var summary = $('req-summary');
        var alertBox = $('req-alert');
        var cont = $('req-continue');

        if (btn) { btn.disabled = true; btn.textContent = 'Checking...'; }
        setAlert(alertBox, 'warn', 'Checking server requirements...');

        post('/install/check-requirements', {}).then(function (data) {
            var keys = (data && typeof data === 'object') ? Object.keys(data) : [];

            // Guard against an empty / unexpected payload — never leave a blank list.
            if (!keys.length) {
                setAlert(alertBox, 'warn',
                    'Could not read the live check results. The server-side results above are still valid.');
                if (btn) { btn.disabled = false; btn.textContent = 'Re-check'; }
                return;
            }

            list.innerHTML = '';
            var failed = 0;

            keys.forEach(function (k) {
                var c = data[k] || {};
                var passed = c.passed === true;
                if (!passed) failed++;

                var row = el('div', 'check-row');
                var left = el('div', 'check-left');
                var icon = el('div', 'icon ' + (passed ? 'ok' : 'bad'));
                icon.innerHTML = passed
                    ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>';
                left.appendChild(icon);
                left.appendChild(el('span', 'check-label', esc(c.label || k)));
                row.appendChild(left);
                row.appendChild(el('span', 'check-detail', esc(c.detail || '')));
                list.appendChild(row);
            });

            var allOk = failed === 0;
            summary.textContent = allOk
                ? 'All ' + keys.length + ' checks passed'
                : failed + ' check(s) need attention';
            setAlert(alertBox, allOk ? 'ok' : 'bad', allOk
                ? 'Your server is ready. You can continue to the next step.'
                : 'Some requirements are not met. Fix the items marked in red before continuing.');
            cont.disabled = !allOk;

            if (btn) { btn.disabled = false; btn.textContent = 'Re-check'; }
        }).catch(function () {
            // Network / non-JSON failure: keep the server-rendered list intact.
            setAlert(alertBox, 'warn',
                'Live check could not be completed. The server-side results above are still valid.');
            if (btn) { btn.disabled = false; btn.textContent = 'Re-check'; }
        });
    }

    function testDatabase() {
        state.testing = true;
        setAlert($('db-alert'), 'warn', 'Testing connection...');
        render();
        post('/install/test-database', {
            host: $('db_host').value,
            port: $('db_port').value,
            database: $('db_database').value,
            username: $('db_username').value,
            password: $('db_password').value
        }).then(function (data) {
            state.testing = false;
            setAlert($('db-alert'), data.success ? 'ok' : 'bad', data.message || 'No response');
            render();
        }).catch(function (e) {
            state.testing = false;
            setAlert($('db-alert'), 'bad', 'Connection test failed: ' + (e && e.message ? e.message : 'unknown error'));
            render();
        });
    }

    function runInstall() {
        state.installing = true;
        setAlert($('install-alert'), 'warn', 'Installing, please wait...');
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
            setAlert($('install-alert'), data.success ? 'ok' : 'bad', data.message || 'No response');
            if (data.success) { state.step = 3; }
            render();
        }).catch(function (e) {
            state.installing = false;
            setAlert($('install-alert'), 'bad', 'Installation failed: ' + (e && e.message ? e.message : 'unknown error'));
            render();
        });
    }

    /* ---------- Wire up ---------- */
    var ACTIONS = { recheck: recheck, testDatabase: testDatabase, runInstall: runInstall };

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

    $('app_url').value = window.location.origin;
    render();
})();
</script>

</body>
</html>