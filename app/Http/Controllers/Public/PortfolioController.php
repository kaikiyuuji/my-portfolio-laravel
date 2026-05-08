<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ExperienceService;
use App\Services\ProfileService;
use App\Services\ProjectService;
use App\Services\SocialLinkService;
use App\Services\StackService;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private StackService $stackService,
        private ExperienceService $experienceService,
        private ProjectService $projectService,
        private SocialLinkService $socialLinkService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Public/Portfolio', [
            'profile' => $this->profileService->get(),
            'stacks' => $this->stackService->featured(),
            'experiences' => $this->experienceService->all(),
            'projects' => $this->projectService->featured(),
            'socialLinks' => $this->socialLinkService->all(),
        ]);
    }
}
