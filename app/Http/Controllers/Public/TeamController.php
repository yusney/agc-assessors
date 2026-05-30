<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use AGC\Domain\Team\Repositories\TeamMemberRepository;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class TeamController extends Controller
{
    public function __construct(
        private readonly TeamMemberRepository $team,
    ) {}

    public function __invoke(): View
    {
        return view('public.team.index', [
            'members' => $this->team->findAllActive(),
        ]);
    }
}
