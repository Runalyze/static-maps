<?php

declare(strict_types=1);

/*
 * This file is part of the StaticMaps.
 *
 * (c) RUNALYZE <mail@runalyze.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Runalyze\StaticMaps\Feature;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class CopyrightNotice implements FeatureInterface
{
    protected string $Text;

    /** @var callable */
    protected $FontCallback;
    protected string $BackgroundColor = 'rgba(255, 255, 255, 0.5)';
    protected string $Position = 'bottom-right';

    /** @var int [px] */
    protected int $OffsetX = 0;

    /** @var int [px] */
    protected int $OffsetY = 0;

    /**
     * @param string $text
     * @param null|callable(FontFactory): void $fontCallback
     */
    public function __construct(string $text, callable $fontCallback = null)
    {
        $this->Text = $text;
        $this->FontCallback = $fontCallback ?? function (FontFactory $font) {
        };
    }

    public function setBackgroundColor(string $rgba): self
    {
        $this->BackgroundColor = $rgba;

        return $this;
    }

    public function setPosition(string $position, int $offsetX = 0, int $offsetY = 0): self
    {
        $this->Position = $position;
        $this->OffsetX = $offsetX;
        $this->OffsetY = $offsetY;

        return $this;
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport): void
    {
        $boxSize = $imageManager->driver()->fontProcessor()->boxSize($this->Text, (new FontFactory($this->applyFontCallbacks(...)))());

        $watermark = $imageManager->create($boxSize->width(), $boxSize->height());
        $watermark->text($this->Text, 0, 2, fn (FontFactory $font) => $this->applyFontCallbacks($font));

        $watermarkBackground = $imageManager->create($boxSize->width() + 10, $boxSize->height() + 5)->fill($this->BackgroundColor);
        $watermarkBackground->place($watermark, 'center');

        $image->place($watermarkBackground, $this->Position, $this->OffsetX, $this->OffsetY);
    }

    protected function applyFontCallbacks(FontFactory $font): void
    {
        $font->valign('top');
        $font->color('#000');
        $font->size(9);

        if (null !== $this->FontCallback) {
            ($this->FontCallback)($font);
        }
    }
}
