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

namespace Runalyze\StaticMaps\TileService;

class OpenStreetMapDe extends OpenStreetMap
{
    protected string $TileTemplate = 'https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png';
}
