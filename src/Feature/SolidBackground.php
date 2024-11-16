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
use Runalyze\StaticMaps\Viewport\ViewportInterface;

class SolidBackground implements FeatureInterface
{
    protected string|array $Color;

    public function __construct($color)
    {
        $this->Color = $color;
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport): void
    {
        $image->place(
            $imageManager->create($viewport->getWidth(), $viewport->getHeight())->fill($this->Color)
        );
    }
}
