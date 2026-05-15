<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('AuditLogs/Index', [
            'auditLogs' => AuditLog::query()->orderByDesc('created_at')->limit(200)->get(),
        ]);
    }
}
