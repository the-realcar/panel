# Deployment Checklist

## Goal
Move the system from testing to production deployment with repeatable, safe steps.

## 1. Environment and secrets
- Set APP_ENV=production.
- Set BASE_URL to final HTTPS domain.
- Set DB credentials from secret manager.
- Set SMTP credentials from secret manager.
- Set SHOW_TEST_CREDENTIALS=false.
- Configure optional OAuth client ids/secrets if used.

## 2. Web server and TLS
- Enable HTTPS with valid TLS certificate.
- Configure virtual host/root to project entrypoint.
- Deny access to sensitive files and folders.
- Enable HTTP security headers at web server level.

## 3. Database rollout
- Run schema only: database/schema.sql.
- Do not import database/seeds.sql on production.
- Create production admin account manually with unique password.
- Verify settings table contains base_url and session_timeout values.

## 4. Application hardening
- Verify display_errors is disabled in production.
- Verify session cookies are HttpOnly, Secure (under HTTPS), SameSite=Strict.
- Verify health endpoint does not expose detailed database errors.
- Verify login page does not expose test credentials.

## 5. Smoke tests after deploy
- Open /health.php and verify status=ok.
- Test login for admin and role based access paths.
- Test password reset email flow.
- Test one create/edit/delete action in admin modules.
- Test driver and dispatcher dashboards.

## 6. Operations readiness
- Configure backup policy for PostgreSQL.
- Configure log rotation for PHP/app logs.
- Configure uptime monitor against /health.php.
- Configure alerting for repeated login failures and app errors.

## 7. Release closure
- Remove/disable all test credentials and test-only docs shown to users.
- Tag release in git and keep deployment notes.
- Record rollback plan (previous release tag + db backup).
