<?php

namespace Evitenic\ResizedColumn\Setup;

use Evitenic\ResizedColumn\Setup\Concerns\CanResizedColumn;

class Setup
{
    use CanResizedColumn;

    public static function resizedColumnPlugged(): bool
    {
        return filament()->hasPlugin('evitenic-resized-column') && filament()->getCurrentPanel();
    }
}
