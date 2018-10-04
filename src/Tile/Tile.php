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

namespace Runalyze\StaticMaps\Tile;

class Tile implements TileInterface
{
    /** @var int */
    protected $X;

    /** @var int */
    protected $Y;

    /** @var int */
    protected $Zoom;

    public function __construct(int $x, int $y, int $zoom)
    {
        $this->X = $x;
        $this->Y = $y;
        $this->Zoom = $zoom;
    }

    public function getX(): int
    {
        return $this->X;
    }

    public function getY(): int
    {
        return $this->Y;
    }

    public function getZoom(): int
    {
        return $this->Zoom;
    }
}
