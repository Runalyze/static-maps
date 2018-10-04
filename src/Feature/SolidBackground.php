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
use Runalyze\StaticMaps\Projection\ViewportInterface;

class SolidBackground implements FeatureInterface
{
    /** @var string|array */
    protected $Color;

    public function __construct($color)
    {
        $this->Color = $color;
    }

    public function render(ImageManager $imageManager, Image $image, ViewportInterface $viewport)
    {
        $image->insert(
            $imageManager->canvas($viewport->getWidth(), $viewport->getHeight(), $this->Color)
        );
    }
}
