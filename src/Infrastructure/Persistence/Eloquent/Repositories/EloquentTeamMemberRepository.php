<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Repositories;

use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Domain\Team\Entities\TeamMember;
use AGC\Domain\Team\Repositories\TeamMemberRepository;
use AGC\Infrastructure\Persistence\Eloquent\Models\TeamMemberModel;
use Awcodes\Curator\Models\Media;

final class EloquentTeamMemberRepository implements TeamMemberRepository
{
    public function findById(int $id): ?TeamMember
    {
        $model = TeamMemberModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findAllActive(): array
    {
        return TeamMemberModel::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function save(TeamMember $member): void
    {
        $data = [
            'name'       => $member->name(),
            'email'      => $member->email(),
            'role'       => $member->role()->toArray(),
            'bio'        => $member->bio()->toArray(),
            'sort_order' => $member->sortOrder(),
            'active'     => $member->isActive(),
        ];

        if ($member->id()) {
            TeamMemberModel::where('id', $member->id())->update($data);
        } else {
            TeamMemberModel::create($data);
        }
    }

    public function delete(int $id): void
    {
        TeamMemberModel::destroy($id);
    }

    private function toDomain(TeamMemberModel $model): TeamMember
    {
        return new TeamMember(
            id: $model->id,
            name: $model->name,
            role: new TranslatableString($model->getTranslations('role')),
            bio: new TranslatableString($model->getTranslations('bio') ?: []),
            email: $model->email,
            sortOrder: (int) $model->sort_order,
            active: (bool) $model->active,
            photoUrl: $model->photo_media_id
                ? Media::find($model->photo_media_id)?->url
                : null,
        );
    }
}
