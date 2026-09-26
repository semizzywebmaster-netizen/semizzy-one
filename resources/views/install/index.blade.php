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
        .wrap { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 24px; }
        .card { width: 100%; max-width: 720px; }
        .center { text-align: center; }
        .logo {
            width: 60px; height: 60px; border-radius: 16px; margin: 0 auto 14px;
            background: linear-gradient(135deg, #155EEF, #00B8A9);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 40px rgba(21, 94, 239, 0.25);
        }
        .logo svg { width: 34px; height: 34px; color: #fff; }
        h1 { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; }
        .sub { color: #94a3b8; margin-top: 4px; font-size: 15px; }
        .ver { color: #64748b; font-size: 13px; margin-top: 10px; }
        .bar { height: 4px; background: #1e293b; border-radius: 2px; margin: 24px 0 8px; overflow: hidden; }
        .bar > div { height: 100%; background: linear-gradient(90deg, #155EEF, #00B8A9); transition: width .35s ease; }
        .stepmeta { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 20px; }
        .panel {
            background: rgba(15, 23, 42, .8); border: 1px solid #1e293b;
            border-radius: 16px; padding: 28px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.5);
        }
        h2 { font-size: 19px; font-weight: 600; margin-bottom: 4px; }
        .lead { color: #94a3b8; font-size: 13px; margin-bottom: 22px; }
        .check-row {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 11px 13px; border-radius: 9px; background: rgba(30,41,59,.5);
            border: 1px solid #1e293b; margin-bottom: 7px;
        }
        .check-left { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .icon { width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center; }
        .icon.ok { background: rgba(16,185,129,.2); color: #34d399; }
        .icon.bad { background: rgba(239,68,68,.2); color: #f87171; }
        .icon svg { width: 13px; height: 13px; }
        .check-label { font-size: 13px; }
        .check-detail { font-size: 11px; color: #64748b; text-align: right; word-break: break-word; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .grid-1 { grid-column: 1 / -1; }
        .mb { margin-bottom: 14px; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
        label { display: block; font-size: 12px; font-weight: 500; color: #94a3b8; margin-bottom: 5px; }
        input, select {
            width: 100%; padding: 9px 11px; background: #1e293b; border: 1px solid #334155;
            border-radius: 8px; color: #f1f5f9; font-size: 13px; outline: none; font-family: inherit;
        }
        input:focus, select:focus { border-color: #155EEF; box-shadow: 0 0 0 3px rgba(21,94,239,.15); }
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 24px; gap: 10px; flex-wrap: wrap; }
        .btn {
            padding: 9px 20px; border: none; border-radius: 9px; font-size: 13px;
            font-weight: 500; cursor: pointer; transition: all .2s; font-family: inherit;
        }
        .btn-primary { background: linear-gradient(135deg, #155EEF, #00B8A9); color: #fff; }
        .btn-primary:hover:not(:disabled) { opacity: .9; }
        .btn-ghost { background: transparent; color: #94a3b8; }
        .btn-ghost:hover { color: #f1f5f9; }
        .btn-outline { background: transparent; color: #cbd5e1; border: 1px solid #334155; }
        .btn-outline:hover:not(:disabled) { border-color: #475569; }
        .btn:disabled { opacity: .45; cursor: not-allowed; }
        .alert { margin-top: 14px; padding: 11px 13px; border-radius: 9px; font-size: 12px; line-height: 1.5; }
        .alert-ok { background: rgba(16,185,129,.1); color: #34d399; border: 1px solid rgba(16,185,129,.2); }
        .alert-bad { background: rgba(239,68,68,.1); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .alert-warn { background: rgba(245,158,11,.1); color: #fbbf24; border: 1px solid rgba(245,158,11,.2); }
        .divider { border: none; border-top: 1px solid #1e293b; margin: 20px 0; }
        .done-icon { width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 14px;
                     background: rgba(16,185,129,.2); color: #34d399;
                     display: flex; align-items: center; justify-content: center; }
        .done-icon svg { width: 30px; height: 30px; }
        .notice { margin-top: 20px; padding: 13px; border-radius: 9px; text-align: left;
                  background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.2); }
        .notice-title { color: #fbbf24; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
        .notice-text { color: rgba(251,191,36,.8); font-size: 12px; line-height: 1.5; }
        code { padding: 1px 5px; background: rgba(245,158,11,.15); border-radius: 4px; font-size: 11px; }
        .foot { text-align: center; color: #475569; font-size: 11px; margin-top: 20px; }
        a { color: #34d399; text-decoration: none; }
        [hidden] { display: none !important; }
        .kv { display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; font-size: 12px; }
        .kv dt { color: #64748b; }
        .kv dd { color: #e2e8f0; text-align: right; word-break: break-all; }
        .hint { font-size: 11px; color: #64748b; margin-top: 5px; line-height: 1.5; }
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
            <p class="ver">Installation Wizard &mdash; CORE v2.0.0</p>
        </div>

        <div class="bar"><div id="progress" style="width:6%"></div></div>
        <div class="stepmeta">
            <span id="step-label">Step 1 of 16 &mdash; Welcome</span>
            <span id="step-pct">6%</span>
        </div>

        <div class="panel">

            <!-- ═══ 1 WELCOME ═══ -->
            <section data-step="1">
                <h2>Welcome</h2>
                <p class="lead">SEMIZZY ONE CORE &mdash; a 16-step guided installation.</p>
                <div class="alert alert-warn">
                    Before you begin, make sure you have: a MySQL database and user with
                    <strong>ALL PRIVILEGES</strong> on that database, and your web server document root
                    pointing at <code>public/</code>.
                </div>
                <dl class="kv" style="margin-top:18px;">
                    <dt>Product</dt><dd>SEMIZZY ONE CORE</dd>
                    <dt>Version</dt><dd>2.0.0</dd>
                    <dt>Database</dt><dd>MySQL 8.0+</dd>
                    <dt>Parent company</dt><dd>SEMIZZY WEBMASTER</dd>
                </dl>
                <div class="actions">
                    <span></span>
                    <button class="btn btn-primary" data-goto="2">Begin Installation</button>
                </div>
            </section>

            <!-- ═══ 2 SERVER REQUIREMENTS ═══ -->
            <section data-step="2" hidden>
                <h2>Server Requirements</h2>
                <p class="lead">Verifying the host environment.</p>
                <div id="list-2"></div>
                <div id="alert-2" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="1">Back</button>
                    <button class="btn btn-primary" data-action="run:2" data-url="/install/check/server">Run Check</button>
                    <button class="btn btn-primary" data-goto="3" data-continue="2" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 3 PHP REQUIREMENTS ═══ -->
            <section data-step="3" hidden>
                <h2>PHP Requirements</h2>
                <p class="lead">Verifying the PHP runtime and required extensions.</p>
                <div id="list-3"></div>
                <div id="alert-3" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="2">Back</button>
                    <button class="btn btn-primary" data-action="run:3" data-url="/install/check/php">Run Check</button>
                    <button class="btn btn-primary" data-goto="4" data-continue="3" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 4 MYSQL REQUIREMENTS ═══ -->
            <section data-step="4" hidden>
                <h2>MySQL Requirements</h2>
                <p class="lead">Verifying the MySQL client drivers are available.</p>
                <div id="list-4"></div>
                <div id="alert-4" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="3">Back</button>
                    <button class="btn btn-primary" data-action="run:4" data-url="/install/check/mysql">Run Check</button>
                    <button class="btn btn-primary" data-goto="5" data-continue="4" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 5 ENVIRONMENT ═══ -->
            <section data-step="5" hidden>
                <h2>Environment</h2>
                <p class="lead">Writing the application environment configuration.</p>
                <div class="grid">
                    <div class="grid-1">
                        <label for="app_name">Application Name</label>
                        <input type="text" id="app_name" value="SEMIZZY ONE">
                    </div>
                    <div class="grid-1">
                        <label for="app_url">Application URL</label>
                        <input type="url" id="app_url">
                        <p class="hint">Include the scheme, e.g. https://vtu.oyejogas.com</p>
                    </div>
                    <div>
                        <label for="app_env">Environment</label>
                        <select id="app_env">
                            <option value="production">production</option>
                            <option value="local">local</option>
                        </select>
                    </div>
                    <div>
                        <label for="timezone">Timezone</label>
                        <select id="timezone"></select>
                    </div>
                </div>
                <div id="alert-5" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="4">Back</button>
                    <button class="btn btn-primary" data-action="save:5">Save &amp; Continue</button>
                </div>
            </section>

            <!-- ═══ 6 MYSQL CONNECTION ═══ -->
            <section data-step="6" hidden>
                <h2>MySQL Connection</h2>
                <p class="lead">Enter your MySQL database credentials. Nothing is written yet.</p>
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
                <div id="alert-6" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="5">Back</button>
                    <div style="display:flex; gap:10px;">
                        <button class="btn btn-outline" data-action="post:6" data-url="/install/database/connect">Test Connection</button>
                        <button class="btn btn-primary" data-goto="7">Continue</button>
                    </div>
                </div>
            </section>

            <!-- ═══ 7 DATABASE VALIDATION ═══ -->
            <section data-step="7" hidden>
                <h2>Database Validation</h2>
                <p class="lead">Verifying MySQL version, charset and required privileges.</p>
                <div id="list-7"></div>
                <div id="alert-7" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="6">Back</button>
                    <button class="btn btn-primary" data-action="run:7" data-url="/install/database/validate">Validate Database</button>
                    <button class="btn btn-primary" data-goto="8" data-continue="7" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 8 APPLICATION SETTINGS ═══ -->
            <section data-step="8" hidden>
                <h2>Application Settings</h2>
                <p class="lead">Regional and display configuration.</p>
                <div class="grid">
                    <div>
                        <label for="locale">Locale</label>
                        <select id="locale"><option value="en" selected>English (en)</option></select>
                    </div>
                    <div>
                        <label for="date_format">Date Format</label>
                        <select id="date_format">
                            <option value="Y-m-d" selected>Y-m-d (2026-09-26)</option>
                            <option value="d/m/Y">d/m/Y (26/09/2026)</option>
                            <option value="m/d/Y">m/d/Y (09/26/2026)</option>
                        </select>
                    </div>
                </div>
                <div id="alert-8" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="7">Back</button>
                    <button class="btn btn-primary" data-action="save:8">Save &amp; Continue</button>
                </div>
            </section>

            <!-- ═══ 9 ADMIN ACCOUNT ═══ -->
            <section data-step="9" hidden>
                <h2>Administrator Account</h2>
                <p class="lead">This account will have the ADMIN role.</p>
                <div class="mb">
                    <label for="admin_name">Full Name</label>
                    <input type="text" id="admin_name" placeholder="Administrator">
                </div>
                <div class="mb">
                    <label for="admin_email">Email Address</label>
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
                <p class="hint">Minimum 8 characters. Avoid common passwords.</p>
                <div id="alert-9" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="8">Back</button>
                    <button class="btn btn-primary" data-action="save:9">Validate &amp; Continue</button>
                </div>
            </section>

            <!-- ═══ 10 MIGRATIONS ═══ -->
            <section data-step="10" hidden>
                <h2>Database Migrations</h2>
                <p class="lead">Creating tables, indexes and foreign keys in MySQL.</p>
                <div id="list-10"></div>
                <div id="alert-10" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="9">Back</button>
                    <button class="btn btn-primary" data-action="post:10" data-url="/install/migrate">Run Migrations</button>
                </div>
            </section>

            <!-- ═══ 11 CORE DATA ═══ -->
            <section data-step="11" hidden>
                <h2>Core Data</h2>
                <p class="lead">Seeding roles, permissions and core settings.</p>
                <div id="list-11"></div>
                <div id="alert-11" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="10">Back</button>
                    <button class="btn btn-primary" data-action="post:11" data-url="/install/seed">Seed Core Data</button>
                </div>
            </section>

            <!-- ═══ 12 STORAGE ═══ -->
            <section data-step="12" hidden>
                <h2>Storage Checks</h2>
                <p class="lead">Verifying filesystem permissions and the storage symlink.</p>
                <div id="list-12"></div>
                <div id="alert-12" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="11">Back</button>
                    <button class="btn btn-primary" data-action="run:12" data-url="/install/check/storage">Run Check</button>
                    <button class="btn btn-primary" data-goto="13" data-continue="12" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 13 CACHE ═══ -->
            <section data-step="13" hidden>
                <h2>Cache Checks</h2>
                <p class="lead">Verifying the cache store. Redis is not required.</p>
                <div id="list-13"></div>
                <div id="alert-13" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="12">Back</button>
                    <button class="btn btn-primary" data-action="run:13" data-url="/install/check/cache">Run Check</button>
                    <button class="btn btn-primary" data-goto="14" data-continue="13" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 14 PWA ═══ -->
            <section data-step="14" hidden>
                <h2>PWA Checks</h2>
                <p class="lead">Verifying the manifest, service worker and offline fallback.</p>
                <div id="list-14"></div>
                <div id="alert-14" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="13">Back</button>
                    <button class="btn btn-primary" data-action="run:14" data-url="/install/check/pwa">Run Check</button>
                    <button class="btn btn-primary" data-goto="15" data-continue="14" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 15 SECURITY ═══ -->
            <section data-step="15" hidden>
                <h2>Security Checks</h2>
                <p class="lead">Verifying production security posture.</p>
                <div id="list-15"></div>
                <div id="alert-15" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="14">Back</button>
                    <button class="btn btn-primary" data-action="run:15" data-url="/install/check/security">Run Check</button>
                    <button class="btn btn-primary" data-goto="16" data-continue="15" disabled>Continue</button>
                </div>
            </section>

            <!-- ═══ 16 FINALIZATION ═══ -->
            <section data-step="16" hidden>
                <h2>Finalization</h2>
                <p class="lead">Writing configuration, creating the administrator and locking the installer.</p>
                <dl class="kv" id="summary-16"></dl>
                <div id="alert-16" class="alert" hidden></div>
                <div class="actions">
                    <button class="btn btn-ghost" data-goto="15">Back</button>
                    <button class="btn btn-primary" id="finalize-btn" data-action="post:16" data-url="/install/finalize">Complete Installation</button>
                </div>
            </section>

            <!-- ═══ DONE ═══ -->
            <section data-step="17" hidden>
                <div class="center" style="padding:20px 0;">
                    <div class="done-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2>Installation Complete</h2>
                    <p class="lead" style="margin-top:8px;">SEMIZZY ONE CORE v2.0.0 is ready.</p>
                    <div class="notice">
                        <p class="notice-title">Security Notice</p>
                        <p class="notice-text">
                            Delete the <code>resources/views/install/</code> directory and remove the
                            <code>/install</code> routes from <code>routes/web.php</code> to prevent
                            unauthorized reinstallation.
                        </p>
                    </div>
                    <div style="margin-top:24px;">
                        <a href="/login" class="btn btn-primary" style="display:inline-block;">Go to Login</a>
                    </div>
                </div>
            </section>

        </div>

        <p class="foot">SEMIZZY ONE CORE v2.0.0 &mdash; SEMIZZY WEBMASTER</p>
    </div>
</div>

<script>
(function () {
    'use strict';
    var TOTAL = 16;
    var LABELS = {
        1:'Welcome', 2:'Server Requirements', 3:'PHP Requirements', 4:'MySQL Requirements',
        5:'Environment', 6:'MySQL Connection', 7:'Database Validation', 8:'Application Settings',
        9:'Admin Account', 10:'Migrations', 11:'Core Data', 12:'Storage Checks',
        13:'Cache Checks', 14:'PWA Checks', 15:'Security Checks', 16:'Finalization'
    };
    var state = { step: 1, busy: false, passed: {} };

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
    function request(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token(), 'Accept': 'application/json' },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            if (r.status === 404) {
                throw new Error(
                    'Route not found (404): ' + url + '. ' +
                    'This almost always means a stale route cache. Run: php artisan route:clear'
                );
            }
            if (r.status === 403) {
                throw new Error(
                    'Access denied (403). The installer may already be marked as installed. ' +
                    'Delete storage/app/.installed to re-run it.'
                );
            }
            if (r.status === 422) {
                // Use the two-argument form of then(). A .catch() chained after
                // this .then() would also swallow the error thrown inside it and
                // replace the useful message with the generic fallback.
                return r.json().then(
                    function (data) {
                        var fields = data && data.errors
                            ? Object.keys(data.errors)
                            : [];
                        throw new Error(
                            (data && data.message ? data.message : 'Validation failed.') +
                            (fields.length
                                ? ' Check these fields: ' + fields.join(', ')
                                : '')
                        );
                    },
                    function () {
                        throw new Error('Validation failed (HTTP 422). Check the fields on this step.');
                    }
                );
            }
            if (r.status === 419) {
                throw new Error(
                    'CSRF token mismatch (419). Reload the page and try again.'
                );
            }
            if (r.status >= 500) {
                throw new Error(
                    'Server error (HTTP ' + r.status + '). Check storage/logs/laravel.log ' +
                    'and confirm vendor/ is installed (composer install).'
                );
            }
            return r.json().catch(function () {
                throw new Error('Server returned a non-JSON response (HTTP ' + r.status + ').');
            });
        });
    }
    function setAlert(node, kind, msg) {
        node.hidden = !msg;
        node.className = 'alert ' + (kind === 'ok' ? 'alert-ok' : (kind === 'warn' ? 'alert-warn' : 'alert-bad'));
        node.textContent = msg || '';
    }
    function renderList(container, checks) {
        container.innerHTML = '';
        if (!checks || !checks.length) {
            container.appendChild(el('div', 'check-row',
                '<div class="check-left"><span class="check-label">No checks returned.</span></div>'));
            return;
        }
        checks.forEach(function (c) {
            var row = el('div', 'check-row');
            var left = el('div', 'check-left');
            var icon = el('div', 'icon ' + (c.passed ? 'ok' : 'bad'));
            icon.innerHTML = c.passed
                ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                : '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>';
            left.appendChild(icon);
            left.appendChild(el('span', 'check-label', esc(c.label)));
            row.appendChild(left);
            row.appendChild(el('span', 'check-detail', esc(c.detail || '')));
            container.appendChild(row);
        });
    }

    /* ── form model ── */
    function form() {
        return {
            app_name: $('app_name').value,
            app_url: $('app_url').value,
            app_env: $('app_env').value,
            timezone: $('timezone').value,
            locale: $('locale').value,
            date_format: $('date_format').value,
            db_host: $('db_host').value,
            db_port: $('db_port').value,
            db_database: $('db_database').value,
            db_username: $('db_username').value,
            db_password: $('db_password').value,
            admin_name: $('admin_name').value,
            admin_email: $('admin_email').value,
            admin_password: $('admin_password').value,
            admin_password_confirmation: $('admin_password_confirmation').value
        };
    }

    /* ── rendering ── */
    function render() {
        var pct = Math.round((state.step / TOTAL) * 100);
        $('progress').style.width = pct + '%';
        $('step-label').textContent = 'Step ' + state.step + ' of ' + TOTAL + ' — ' + (LABELS[state.step] || 'Complete');
        $('step-pct').textContent = pct + '%';

        document.querySelectorAll('section[data-step]').forEach(function (s) {
            s.hidden = parseInt(s.getAttribute('data-step'), 10) !== state.step;
        });

        syncContinue();

        if (state.step === 16) { renderSummary(); }
    }

    function renderSummary() {
        var f = form();
        var rows = [
            ['Application', f.app_name],
            ['URL', f.app_url],
            ['Environment', f.app_env],
            ['Database', f.db_database + ' @ ' + f.db_host + ':' + f.db_port],
            ['DB user', f.db_username],
            ['Timezone', f.timezone],
            ['Administrator', f.admin_name + ' <' + f.admin_email + '>']
        ];
        var dl = $('summary-16');
        dl.innerHTML = '';
        dl.className = 'kv';
        rows.forEach(function (r) {
            dl.appendChild(el('dt', null, esc(r[0])));
            dl.appendChild(el('dd', null, esc(r[1])));
        });
    }

    /* ── actions ── */
    /**
     * Enable a step's Continue button only once that step has passed.
     * Check steps (2, 3, 4, 7, 12-15) have no auto-advance: the operator must
     * be able to read the requirement list first, then choose to continue.
     */
    function syncContinue() {
        document.querySelectorAll('[data-continue]').forEach(function (btn) {
            var step = parseInt(btn.getAttribute('data-continue'), 10);
            btn.disabled = state.passed[step] !== true;
        });
    }

    function runCheck(btn) {
        var step = parseInt(btn.getAttribute('data-action').split(':')[1], 10);
        var url = btn.getAttribute('data-url');
        var list = $('list-' + step), alertBox = $('alert-' + step);

        btn.disabled = true;
        btn.textContent = 'Checking...';
        setAlert(alertBox, 'warn', 'Running checks...');
        renderList(list, null);

        // Send the form: step 7 (Validate Database) requires the db_* fields
        // entered on step 6. An empty body made it fail validation with 422,
        // which surfaced as an empty check list plus a validation message.
        // The other check steps ignore request input, so sending it is safe.
        request(url, form()).then(function (data) {
            btn.disabled = false;
            btn.textContent = 'Run Check';
            if (data.checks) { renderList(list, data.checks); }
            else { list.innerHTML = ''; }
            state.passed[step] = data.success === true;
            setAlert(alertBox, data.success ? 'ok' : 'bad', data.message);
            syncContinue();
        }).catch(function (e) {
            btn.disabled = false;
            btn.textContent = 'Run Check';
            list.innerHTML = '';
            state.passed[step] = false;
            setAlert(alertBox, 'bad', e.message);
            syncContinue();
        });
    }

    function saveStep(btn) {
        var step = parseInt(btn.getAttribute('data-action').split(':')[1], 10);
        var alertBox = $('alert-' + step);
        var url = step === 5 ? '/install/environment'
                : step === 8 ? '/install/application'
                : '/install/admin';

        btn.disabled = true;
        setAlert(alertBox, 'warn', 'Saving...');

        var f = form();
        var body = step === 5
            ? { app_name: f.app_name, app_url: f.app_url, app_env: f.app_env }
            : step === 8
                ? { app_name: f.app_name, app_url: f.app_url, timezone: f.timezone,
                    locale: f.locale, date_format: f.date_format }
                : { admin_name: f.admin_name, admin_email: f.admin_email,
                    admin_password: f.admin_password,
                    admin_password_confirmation: f.admin_password_confirmation };

        request(url, body).then(function (data) {
            btn.disabled = false;
            state.passed[step] = data.success === true;
            setAlert(alertBox, data.success ? 'ok' : 'bad', data.message);
            if (data.success) { state.step = step + 1; render(); }
        }).catch(function (e) {
            btn.disabled = false;
            state.passed[step] = false;
            setAlert(alertBox, 'bad', e.message);
        });
    }

    function postStep(btn) {
        var step = parseInt(btn.getAttribute('data-action').split(':')[1], 10);
        var url = btn.getAttribute('data-url');
        var alertBox = $('alert-' + step), list = $('list-' + step);

        btn.disabled = true;
        var original = btn.textContent;
        btn.textContent = 'Working...';
        setAlert(alertBox, 'warn', 'Please wait...');

        request(url, form()).then(function (data) {
            btn.disabled = false;
            btn.textContent = original;
            if (data.checks) { renderList(list, data.checks); }
            state.passed[step] = data.success === true;
            setAlert(alertBox, data.success ? 'ok' : 'bad', data.message);
            if (data.success && step < 16) { state.step = step + 1; render(); }
            if (data.success && step === 16) { state.step = 17; render(); }
        }).catch(function (e) {
            btn.disabled = false;
            btn.textContent = original;
            state.passed[step] = false;
            setAlert(alertBox, 'bad', e.message);
        });
    }

    /* ── wiring ── */
    document.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (state.busy) { return; }
            var a = btn.getAttribute('data-action');
            if (a.indexOf('run:') === 0) { runCheck(btn); }
            else if (a.indexOf('save:') === 0) { saveStep(btn); }
            else if (a.indexOf('post:') === 0) { postStep(btn); }
        });
    });

    document.querySelectorAll('[data-goto]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.step = parseInt(btn.getAttribute('data-goto'), 10);
            render();
        });
    });

    /* timezone list */
    var ZONES = ['Africa/Lagos','UTC','Africa/Accra','Africa/Nairobi','Africa/Johannesburg',
                 'Europe/London','Europe/Berlin','America/New_York','America/Chicago',
                 'America/Los_Angeles','Asia/Dubai','Asia/Kolkata','Asia/Singapore','Australia/Sydney'];
    var tz = $('timezone');
    ZONES.forEach(function (z) {
        var o = document.createElement('option');
        o.value = z; o.textContent = z;
        if (z === 'Africa/Lagos') { o.selected = true; }
        tz.appendChild(o);
    });

    $('app_url').value = window.location.origin;
    render();
})();
</script>

</body>
</html>
