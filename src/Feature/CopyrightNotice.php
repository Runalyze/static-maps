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


    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        $font = new \Intervention\Image\Gd\Font($this->Text);
        $this->applyFontCallbacks($font);
        $size = $font->getBoxSize();

        $watermarkBackground = $imageManager->canvas($size['width'] + 10, $size['height'] + 5, 'rgba(255, 255, 255, 0.5)');
        $watermark = $imageManager->canvas($size['width'] + 5, $size['height']);

        $font->applyToImage($watermark);

        $image->insert($watermarkBackground, 'bottom-right');
        $image->insert($watermark, 'bottom-right');
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
