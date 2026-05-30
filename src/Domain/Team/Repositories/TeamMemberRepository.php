<?php

declare(strict_types=1);

namespace AGC\Domain\Team\Repositories;

use AGC\Domain\Team\Entities\TeamMember;

interface TeamMemberRepository
{
    public function findById(int $id): ?TeamMember;
    /** @return TeamMember[] */
    public function findAllActive(): array;
    public function save(TeamMember $member): void;
    public function delete(int $id): void;
}
