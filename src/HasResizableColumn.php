<?php

namespace Evitenic\ResizedColumn;

use Evitenic\ResizedColumn\Setup\Concerns\LoadResizedColumn;

trait HasResizableColumn
{
    use LoadResizedColumn;

    protected int $minColumnWidth = 150;

    protected int $maxColumnWidth = 10000;

    public function setColumnWidths(int $min, int $max): void
    {
        $this->minColumnWidth = $min;
        $this->maxColumnWidth = $max;
    }

    public function bootedHasResizableColumn(): void
    {
        $this->loadColumnWidths();

        foreach ($this->getCurrentTableColumns() as $columnName => $column) {
            $this->applyExtraAttributes($columnName, $column, $this->minColumnWidth, $this->maxColumnWidth);
        }
    }
}
