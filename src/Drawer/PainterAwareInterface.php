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

namespace Runalyze\StaticMaps\Drawer;

interface PainterAwareInterface
{
    /**
     * Set painter for drawing
     *
     * Set color specification in rgb-code as well as opacity and line width for later drawing.
     *
     * @param int $r         [0 .. 255]
     * @param int $g         [0 .. 255]
     * @param int $b         [0 .. 255]
     * @param float $alpha   (0.0 .. 100.0]
     * @param int $lineWidth [px]
     */
    public function setPainter(int $r, int $g, int $b, float $alpha = 100.0, int $lineWidth = 1);
}
