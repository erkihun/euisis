# EUISIS Backup and Disaster Recovery Plan

**Project:** EUISIS — Addis Ababa City Administration Employee Unified ID & Service Integration System  
**Last updated:** 2026-05-25

---

## Recovery Objectives

| Objective | Target |
|---|---|
| Recovery Point Objective (RPO) | 1 hour (maximum data loss acceptable) |
| Recovery Time Objective (RTO) | 4 hours (maximum time to restore service) |

---

## 1. PostgreSQL Database Backup

### Backup Schedule

| Backup Type | Frequency | Retention | Storage Location |
|---|---|---|---|
| Full dump (`pg_dump`) | Daily at 02:00 EAT | 30 days | Encrypted offsite storage |
| WAL archiving / continuous | Every 5 minutes | 7 days | Local archive volume |
| Weekly full dump | Sunday 01:00 EAT | 90 days | Encrypted cold storage |

### Backup Command

```bash
# Daily full dump — run as postgres user or via cron
PGPASSWORD="$DB_PASSWORD" pg_dump \
  -h "$DB_HOST" \
  -U "$DB_USERNAME" \
  -d "$DB_DATABASE" \
  -F c \
  -f "/backups/euisis_$(date +%Y%m%d_%H%M%S).dump"

# Encrypt the dump with GPG before transfer
gpg --recipient backup@euisis.addisababa.gov.et \
  --encrypt /backups/euisis_$(date +%Y%m%d_%H%M%S).dump
```

### Backup Verification

After every backup completes, the following must be verified:

1. Dump file is non-empty and the size is within 10% of the previous backup.
2. Restore the dump to a test PostgreSQL instance weekly:

```bash
pg_restore -h test-db-host -U postgres -d euisis_restore_test \
  /backups/euisis_YYYYMMDD.dump
```

3. Run `php artisan migrate:status` against the restored database to verify schema integrity.

### cron Example

```cron
0 2 * * * /opt/euisis/scripts/backup-db.sh >> /var/log/euisis-backup.log 2>&1
```

---

## 2. File Storage Backup

### What to Back Up

| Path | Contents | Priority |
|---|---|---|
| `storage/app/public/` | Employee photos, organisation logos | High |
| `storage/app/private/` | Any private uploads | High |
| `.env` (encrypted) | Application secrets | Critical |

### Backup Method

```bash
# Sync to a secondary volume or object storage bucket
rsync -avz --delete /var/www/euisis/storage/app/ \
  backup-user@backup-host:/backups/euisis/storage/

# Or if using MinIO / S3-compatible storage:
aws s3 sync /var/www/euisis/storage/app/ \
  s3://euisis-backup-bucket/storage/ --sse AES256
```

### Retention

- File storage backups: 30 days rolling.
- Deleted files (recycle-bin soft-deletes): retained in the database for 90 days.

---

## 3. Redis Backup (if used in production)

If `CACHE_STORE=redis` or `QUEUE_CONNECTION=redis` is used:

- Enable Redis persistence: `appendonly yes` in `redis.conf`.
- Schedule `BGSAVE` or use `redis-cli BGSAVE` before taking snapshots.
- Copy `dump.rdb` and `appendonly.aof` to backup storage daily.

For queue jobs: failed jobs are stored in the database (`failed_jobs` table) and do not require Redis persistence.

---

## 4. Backup Encryption

All backup files leaving the primary server must be encrypted:

- **Method:** GPG asymmetric encryption using the designated backup key.
- **Key management:** The GPG private key is stored in a hardware security module (HSM) or a physically secured offline medium. It must not be present on the production server.
- **Key rotation:** Backup encryption keys rotated annually.

---

## 5. Backup Testing & Restore Procedure

### Monthly Restore Test

1. Spin up a clean PostgreSQL instance on a test server.
2. Copy the most recent encrypted backup from offsite storage.
3. Decrypt: `gpg --decrypt euisis_backup.dump.gpg > euisis_backup.dump`
4. Restore: `pg_restore -h test-host -U postgres -d euisis_test euisis_backup.dump`
5. Stand up a test Laravel instance pointing to the restored database.
6. Run: `php artisan migrate:status` — all migrations must show `Ran`.
7. Log in as a test admin account and verify employee records, audit logs, and ID card records are intact.
8. Record the restore time. Must be within the 4-hour RTO.
9. Tear down the test instance.

### Restore Log

Maintain a log at `/docs/runbook/restore-test-log.md` with columns: Date, Backup Age, Restore Duration, Issues Found, Signed Off By.

---

## 6. Disaster Recovery Steps

### Scenario: Primary Database Server Failure

1. Declare incident — notify incident response contacts (`docs/infrastructure-security.md`).
2. Promote PostgreSQL replica to primary (if streaming replication is configured) — estimated recovery: 15 minutes.
3. If no replica: restore from the most recent daily backup to a standby server — estimated time: 1–3 hours depending on database size.
4. Update `DB_HOST` in production `.env` to point to the new server.
5. Restart PHP-FPM and queue workers: `systemctl restart php-fpm euisis-worker`.
6. Run `php artisan queue:restart`.
7. Verify application is responding: `curl -I https://euisis.addisababa.gov.et/up`.
8. Post incident report within 24 hours.

### Scenario: Application Server Failure

1. Provision a replacement server from the infrastructure template (Ansible playbook or image snapshot).
2. Deploy the latest application release: `git pull && composer install --no-dev && npm run build && php artisan migrate`.
3. Restore `.env` from the encrypted vault.
4. Run `php artisan config:cache route:cache event:cache`.
5. Update the load balancer or DNS to point to the new server.
6. Verify all services: login, card issuance, API verification endpoint.

### Scenario: Data Corruption

1. Identify the time of corruption from audit logs.
2. Restore from the last clean backup (point-in-time recovery if WAL archiving is active).
3. Review audit log entries between the restore point and the corruption event.
4. Manually re-apply legitimate changes if possible.
5. Notify affected users and the Data Protection Officer.

---

## 7. Contacts

See `docs/infrastructure-security.md` for incident response contacts and escalation path.
