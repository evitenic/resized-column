<?php

namespace Evitenic\ResizedColumn;

use Evitenic\ResizedColumn\Setup\Concerns\LoadResizedColumn;
use Evitenic\ResizedColumn\Tables\Columns\ResizeableTextColumn;

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
            if(!($column instanceof ResizeableTextColumn)) continue;
            if($column->isResizable() === false) continue;
            $this->applyExtraAttributes($columnName, $column, $this->minColumnWidth, $this->maxColumnWidth);
        }
    }
}
