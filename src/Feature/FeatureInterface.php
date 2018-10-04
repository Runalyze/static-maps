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

interface FeatureInterface
{
    public function render(ImageManager $imangeManager, Image $image, ViewportInterface $viewport);
}
