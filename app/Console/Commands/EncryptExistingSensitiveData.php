<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Re-saves users + employees so their `national_id` and `phone_number`
 * columns are written through the new `encrypted` cast. Already-encrypted
 * values are detected and skipped.
 *
 * Usage:
 *   php artisan sensitive-data:encrypt-existing            # writes changes
 *   php artisan sensitive-data:encrypt-existing --dry-run  # report only
 */
class EncryptExistingSensitiveData extends Command
{
    /** @var string */
    protected $signature = 'sensitive-data:encrypt-existing {--dry-run : Report counts without writing changes}';

    /** @var string */
    protected $description = 'Encrypt existing national_id and phone fields on users and employees in place';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'DRY RUN — no changes will be persisted.' : 'Encrypting existing PII columns…');

        $userStats = $this->processUsers($dryRun);
        $employeeStats = $this->processEmployees($dryRun);

        $this->newLine();
        $this->table(
            ['Scope', 'Scanned', 'Already encrypted', 'Encrypted now', 'Errors'],
            [
                ['users',     $userStats['scanned'],     $userStats['encrypted'],     $userStats['written'],     $userStats['errors']],
                ['employees', $employeeStats['scanned'], $employeeStats['encrypted'], $employeeStats['written'], $employeeStats['errors']],
            ],
        );

        return self::SUCCESS;
    }

    /**
     * @return array{scanned:int, encrypted:int, written:int, errors:int}
     */
    private function processUsers(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'encrypted' => 0, 'written' => 0, 'errors' => 0];

        // Use the raw query builder to read the underlying ciphertext/plaintext
        // bytes; the encrypted cast on the model would automatically attempt
        // to decrypt them and would mask whether the value is already encrypted.
        DB::table('users')
            ->select(['id', 'national_id', 'phone_number'])
            ->whereNotNull('national_id')
            ->orWhereNotNull('phone_number')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$stats, $dryRun): void {
                foreach ($rows as $row) {
                    $stats['scanned']++;

                    $nidPlain = $this->coerceToPlaintext($row->national_id, $alreadyNid);
                    $phonePlain = $this->coerceToPlaintext($row->phone_number, $alreadyPhone);

                    if ($alreadyNid && $alreadyPhone) {
                        $stats['encrypted']++;
                        continue;
                    }

                    if ($dryRun) {
                        $stats['written']++;
                        continue;
                    }

                    try {
                        $user = User::query()->whereKey($row->id)->first();
                        if (! $user) {
                            $stats['errors']++;
                            continue;
                        }
                        // Re-assign so the encrypted cast kicks in on save; also
                        // refreshes the national_id_hash via the model boot.
                        $user->national_id = $nidPlain;
                        $user->phone_number = $phonePlain;
                        $user->saveQuietly();
                        $stats['written']++;
                    } catch (\Throwable $e) {
                        $stats['errors']++;
                    }
                }
            });

        return $stats;
    }

    /**
     * @return array{scanned:int, encrypted:int, written:int, errors:int}
     */
    private function processEmployees(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'encrypted' => 0, 'written' => 0, 'errors' => 0];

        // Only national_id is encrypted on Employee — phone is left plain so
        // duplicate-detection lookups continue to work without a hash column.
        DB::table('employees')
            ->select(['id', 'national_id'])
            ->whereNotNull('national_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$stats, $dryRun): void {
                foreach ($rows as $row) {
                    $stats['scanned']++;

                    $nidPlain = $this->coerceToPlaintext($row->national_id, $alreadyNid);

                    if ($alreadyNid) {
                        $stats['encrypted']++;
                        continue;
                    }

                    if ($dryRun) {
                        $stats['written']++;
                        continue;
                    }

                    try {
                        $employee = Employee::query()->whereKey($row->id)->first();
                        if (! $employee) {
                            $stats['errors']++;
                            continue;
                        }
                        $employee->national_id = $nidPlain;
                        $employee->saveQuietly();
                        $stats['written']++;
                    } catch (\Throwable $e) {
                        $stats['errors']++;
                    }
                }
            });

        return $stats;
    }

    /**
     * Returns the plaintext for a stored column value. If the column already
     * contains a Laravel-encrypted payload, decrypts it; otherwise treats it
     * as plaintext. Sets `$alreadyEncrypted` by reference so callers can skip
     * rows whose columns are already encrypted.
     */
    private function coerceToPlaintext(mixed $stored, ?bool &$alreadyEncrypted): ?string
    {
        if ($stored === null) {
            $alreadyEncrypted = true; // nothing to do

            return null;
        }

        $stored = (string) $stored;

        // Laravel's encryptString produces base64(JSON{iv,value,mac}), which
        // always begins with "eyJ" after base64-encoding `{"iv":...}`.
        if (str_starts_with($stored, 'eyJ')) {
            try {
                $alreadyEncrypted = true;

                return Crypt::decryptString($stored);
            } catch (DecryptException) {
                // Not actually encrypted — fall through and treat as plaintext.
            }
        }

        $alreadyEncrypted = false;

        return $stored;
    }
}
