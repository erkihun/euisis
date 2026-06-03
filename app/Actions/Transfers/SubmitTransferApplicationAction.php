<?php

declare(strict_types=1);

namespace App\Actions\Transfers;

use App\Enums\TransferDocumentVerificationStatus;
use App\Models\Employee;
use App\Models\TransferAnnouncement;
use App\Models\TransferApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;

readonly class SubmitTransferApplicationAction
{
    public function __construct(private CreateTransferApplicationAction $createAction) {}

    /**
     * @param  array{cover_letter?: string|null, documents?: UploadedFile[]|null}  $data
     */
    public function execute(
        TransferAnnouncement $announcement,
        Employee $employee,
        User $actor,
        array $data = [],
    ): TransferApplication {
        $application = $this->createAction->execute(
            $announcement,
            $employee,
            $actor,
            ['applicant_notes' => $data['cover_letter'] ?? null],
        );

        if (! empty($data['documents'])) {
            foreach ($data['documents'] as $file) {
                /** @var UploadedFile $file */
                $path = $file->store('transfer-documents/'.$application->id, 'local');

                $application->documents()->create([
                    'document_type' => 'applicant_upload',
                    'original_name' => $file->getClientOriginalName(),
                    'file_name' => basename((string) $path),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'verification_status' => TransferDocumentVerificationStatus::Pending,
                ]);
            }
        }

        return $application;
    }
}
