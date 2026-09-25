# SEMIZZY ONE — Architecture Documentation

## Overview

SEMIZZY ONE is a single integrated application (no separate frontend/backend). Laravel serves both the API and the web interface. React components enhance the UI where needed, compiled by Vite.

## Database Schema (MySQL)

### Core Tables

| Table | Purpose |
|-------|---------|
| users | User accounts with soft deletes |
| roles | System and custom roles |
| permissions | Granular permissions by module |
| role_user | User-role pivot |
| permission_role | Role-permission pivot (NO user_id) |
| permission_overrides | Per-user permission grants/denials |
| sessions | Laravel session storage |
| password_reset_tokens | Password reset tokens |
| personal_access_tokens | Sanctum API tokens |
| audit_logs | Security and action audit trail |
| settings | Key-value settings by group |
| notifications | Database notifications |
| push_subscriptions | Web push subscriptions |
| providers | External provider configuration |
| provider_health_logs | Provider health check history |
| addons | Installed addon registry |
| addon_versions | Addon version history |
| cache / cache_locks | Database cache |
| jobs / job_batches / failed_jobs | Database queue |

### Entity Relationships

```
User ─┬─ belongsToMany ─── Role ─── belongsToMany ─── Permission
      ├─ hasMany ───────── PermissionOverride ─── belongsTo ─── Permission
      ├─ hasMany ───────── AuditLog
      └─ hasMany ───────── PushSubscription

Provider ─── hasMany ─── ProviderHealthLog
Addon ─── hasMany ─── AddonVersion
```

## RBAC Architecture

- **4 system roles:** admin, staff, support, user
- **30 permissions** across 10 modules (dashboard, users, roles, permissions, settings, notifications, audit, security, health, backups, addons, providers, support, profile)
- **No super admin bypass** — `Gate::before()` and `['*']` are forbidden
- **permission_role** contains role_id + permission_id (never user_id)
- **permission_overrides** allows per-user grant/revoke

## Middleware Stack

1. `CheckInstallation` — redirects to installer if not installed
2. `CheckRole` — role-based authorization
3. `CheckPermission` — permission-based authorization
4. `AuditAction` — logs POST/PUT/PATCH/DELETE requests

## API Design

All API responses follow:
```json
{
    "success": true|false,
    "message": "Human-readable message",
    "data": {},
    "meta": {},
    "request_id": "unique-id"
}
```

## Addon Architecture

Each addon lives in `addons/{slug}/` with:
- `addon.json` — manifest with name, version, permissions, settings
- `src/` — PHP source with ServiceProvider
- `routes/` — web and/or API routes
- `migrations/` — database migrations
- `resources/` — views and assets

The addon engine discovers, validates, installs, activates, deactivates, and uninstalls addons. Active addons' service providers are auto-registered.

## PWA Architecture

- **manifest.webmanifest** — app metadata and icons
- **sw.js** — service worker with cache strategies
  - Network-first for navigation and API
  - Cache-first for static assets
  - Offline fallback for navigation failures
- **offline.html** — branded offline page with retry
- **IndexedDB** — ready for offline data storage (Phase 18+)

## Security Model

- All admin routes require `role:admin` middleware
- CSRF protection on all web forms
- Rate limiting on login (5 attempts / 15 min)
- Encrypted provider credentials
- No debug mode in production
- Security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- Path traversal protection in file operations
- Mass assignment protection via `$fillable` on all models