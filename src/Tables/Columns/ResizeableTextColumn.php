<?php

namespace Evitenic\ResizedColumn\Tables\Columns;

use Closure;
use Filament\Tables\Columns\TextColumn;

class ResizeableTextColumn extends TextColumn
{
    protected string $view = 'resized-column::tables.columns.text-column';

    protected bool | Closure $isResizable = true;

    protected bool|Closure $canTruncate = true;

    public function resizable(bool | Closure $condition = true): static
    {
        $this->isResizable = $condition;

        return $this;
    }

    public function isResizable(): bool
    {
        return $this->evaluate($this->isResizable);
    }

    public function truncate(bool|Closure $condition = true): static
    {
        $this->canTruncate = $condition;

        return $this;
    }

    public function canTruncate(): bool|Closure
    {
        return $this->evaluate($this->canTruncate);
    }
}
