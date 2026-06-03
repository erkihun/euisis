<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PublicSupportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Support');
    }
}
