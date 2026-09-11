<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ResumeService;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ResumeController extends Controller
{
    public function download(ResumeService $resumeService): Response
    {
        $data = $resumeService->data();
        $filename = 'curriculo-'.(Str::slug($data['name']) ?: 'portfolio').'.pdf';

        return response($resumeService->pdf($data), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
