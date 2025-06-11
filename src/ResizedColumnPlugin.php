<?php

namespace Evitenic\ResizedColumn;

use Evitenic\ResizedColumn\Plugin\Concerns\CanResizedColumn;
use Filament\Contracts\Plugin;
use Filament\Panel;

class ResizedColumnPlugin implements Plugin
{
    use CanResizedColumn;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): Plugin|\Filament\FilamentManager
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'evitenic-resized-column';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
