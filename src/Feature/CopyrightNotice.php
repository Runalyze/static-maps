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

use Intervention\Image\AbstractFont;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class CopyrightNotice implements FeatureInterface
{
    /** @var string */
    protected $Text;

    /** @var callable */
    protected $FontCallback;

    /** @var string */
    protected $BackgroundColor = 'rgba(255, 255, 255, 0.5)';

    /** @var string */
    protected $Position = 'bottom-right';

    /** @var int [px] */
    protected $OffsetX = 0;

    /** @var int [px] */
    protected $OffsetY = 0;

    /**
     * @param string $text
     * @param null|callable(AbstractFont): void $fontCallback
     */
    public function __construct(string $text, callable $fontCallback = null)
    {
        $this->Text = $text;
        $this->FontCallback = $fontCallback ?? function (AbstractFont $font) {
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

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        $font = new \Intervention\Image\Gd\Font($this->Text);
        $this->applyFontCallbacks($font);
        $size = $font->getBoxSize();

        $watermarkBackground = $imageManager->canvas($size['width'] + 10, $size['height'] + 5, $this->BackgroundColor);
        $watermark = $imageManager->canvas($size['width'], $size['height']);

        $font->applyToImage($watermark, 0, 2);

        $watermarkBackground->insert($watermark, 'center');

        $image->insert($watermarkBackground, $this->Position, $this->OffsetX, $this->OffsetY);
    }

    protected function applyFontCallbacks(AbstractFont $font)
    {
        $font->valign('top');
        $font->color('#000');
        $font->file(3);
        $font->size(9);

        if (null !== $this->FontCallback) {
            ($this->FontCallback)($font);
        }
    }
}
