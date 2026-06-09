<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator\Actions;

use Awcodes\Curator\Actions\MultiUploadAction;
use Awcodes\Curator\Components\Forms\Uploader;
use Awcodes\Curator\Facades\Curator;
use Awcodes\Curator\Models\Media;
use Closure;
use Exception;
use Illuminate\Support\Facades\App;

class CustomMultiUploadAction extends MultiUploadAction
{
    protected string|Closure|null $directory = null;

    /** @throws Exception */
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->schema([
                Uploader::make('files')
                    ->acceptedFileTypes(Curator::getAcceptedFileTypes())
                    ->directory(fn () => $this->getDirectory() ?? Curator::getDirectory())
                    ->disk(Curator::getDiskName())
                    ->label(trans('curator::forms.multi_upload.modal_file_label'))
                    ->minSize(Curator::getMinSize())
                    ->maxSize(Curator::getMaxSize())
                    ->multiple()
                    ->panelLayout('grid')
                    ->preserveFilenames(Curator::shouldPreserveFilenames())
                    ->required()
                    ->visibility(Curator::getVisibility())
                    ->storeFileNamesIn('originalFilename')
                    ->imageCropAspectRatio(Curator::getImageCropAspectRatio())
                    ->imageResizeMode(Curator::getImageResizeMode())
                    ->imageResizeTargetWidth(Curator::getImageResizeTargetWidth())
                    ->imageResizeTargetHeight(Curator::getImageResizeTargetHeight()),
            ])
            ->action(function (array $data): void {
                foreach ($data['files'] as $item) {
                    $item['exif'] = empty($item['exif']) ? null : Curator::sanitizeExif($item['exif']);
                    $item['title'] = pathinfo((string) ($data['originalFilename'][$item['path']] ?? null), PATHINFO_FILENAME);
                    $item['directory'] = $this->getDirectory() ?? null;

                    tap(
                        App::make(Media::class)->create($item),
                        fn (Media $media): string => $media->getPrettyName(),
                    );
                }
            });
    }

    public function directory(string|Closure|null $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->evaluate($this->directory);
    }
}
