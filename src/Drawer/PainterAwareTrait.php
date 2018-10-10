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

trait PainterAwareTrait
{
    /** @var int[] [r, g, b] */
    protected $PainterColor = [0, 0, 0];

    /** @var float (0.0 .. 100.0) */
    protected $PainterAlpha = 100.0;

    /** @var int [px] */
    protected $LineWidth = 1;

    public function setPainter(int $r, int $g, int $b, float $alpha = 100.0, int $lineWidth = 1)
    {
        $this->PainterColor = [$r, $g, $b];
        $this->PainterAlpha = $alpha;
        $this->LineWidth = $lineWidth;
    }

    protected function allocateColor($resource, float $alpha = null): int
    {
        $alpha = null !== $alpha ? $alpha : $this->PainterAlpha;

        return imagecolorallocatealpha($resource, $this->PainterColor[0], $this->PainterColor[1], $this->PainterColor[2], (int)(127.0 - $alpha * 127.0 / 100.0));
    }
}
