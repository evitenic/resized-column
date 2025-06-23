<?php

namespace Evitenic\ResizedColumn\Setup\Concerns;

use Evitenic\ResizedColumn\Models\TableSetting;
use Evitenic\ResizedColumn\Setup\Setup;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;

trait LoadResizedColumn
{
    /**
     * @var array<string, array{width: string|null}>
     */
    protected array $columnWidths = [];

    private function applyExtraAttributes(string $columnName, Column $column, int $minColumnWidth, int $maxColumnWidth): void
    {
        /**
         * @var Column $column
         */
        $width = $this->columnWidths[$columnName]['width'] ?? $column->getWidth();
        $styles = $this->getColumnStyles($width);

        $columnId = $this->getColumnHtmlId($columnName);

        $column->extraHeaderAttributes([
            'x-data' => "resizedColumn({
                tableKey: '{$this->getSessionKey()}',
                columnName: '{$columnName}',
                columnId: '{$columnId}',
                minColumnWidth: {$minColumnWidth},
                maxColumnWidth: {$maxColumnWidth},
                sessionBrowser: {$this->isPreservedOnSessionBrowser()}
            })",
            ...$styles['header'],
        ])
            ->extraCellAttributes($styles['cell']);
    }

    /**
     * @return array{header: array<string, string>, cell: array<string, string>}
     */
    protected function getColumnStyles(?string $width): array
    {
        if (! $width) {
            return ['header' => [], 'cell' => []];
        }
        $getWidth = $this->getWidth($width);
        $style = "min-width: {$getWidth}; width: {$getWidth}; max-width: {$getWidth}";


        return [
            'header' => ['style' => $style],
            'cell' => ['style' => "{$style}; overflow: hidden"],
        ];
    }

    /**
     * Retrieves the table's columns.
     *
     * @return array<string, Column>
     */
    #[Computed(cache: true)]
    protected function getCurrentTableColumns(): array
    {
        return $this->getTable()->getColumns();
    }

    #[Renderless]
    public function updateColumnWidth(string $columnName, string $newWidth): void
    {
        if (self::isPreservedOnSessionBrowser()) return;

        $this->columnWidths[$columnName]['width'] = $newWidth;

        if (self::isPreservedOnDB()) {
            $this->persistColumnWidthsToDatabase();
        }

        $this->persistColumnWidthsToSession();
    }

    /**
     * Persist column widths to the database.
     * This method can be overridden to customize database operations.
     */
    protected function persistColumnWidthsToDatabase(): void
    {
        TableSetting::updateOrCreate(
            [
                'user_id' => $this->getUserId(),
                'resource' => $this->getResourceModelFullPath(),
            ],
            ['styles' => $this->columnWidths]
        );
    }

    /**
     * Persist column widths to the session.
     * This method can be overridden to customize session storage.
     */
    protected function persistColumnWidthsToSession(): void
    {
        session()->put($this->getSessionKey(), $this->columnWidths);
    }

    /**
     * Get the current user ID for column width storage.
     * This method can be overridden to customize user identification.
     */
    protected function getUserId()
    {
        return Auth::id();
    }

    protected function getResourceModelFullPath(): string
    {
        if (method_exists($this, 'getRelationship')) { // @phpstan-ignore-line

            $relationShipModelInstance = $this->getRelationship(); // @phpstan-ignore-line

            return $relationShipModelInstance->getModel()::class;
        } else {
            return static::getModel();
        }
    }

    private function getColumnHtmlId(string $columnName): string
    {
        return Str::of($columnName)->snake()
            ->replace('_', '-')
            ->replace(' ', '-')
            ->toString();
    }

    protected function getSessionKey(): string
    {
        $modelName = Str::snake(class_basename($this->getResourceModelFullPath()));

        return "tables.{$modelName}_columns_style";
    }

    protected function loadColumnWidths(): void
    {
        if (self::isPreservedOnSession()) {
            $this->loadColumnWidthsFromSession();
        }

        if (self::isPreservedOnDB() && blank($this->columnWidths)) {
            $this->loadColumnWidthsFromDatabase();
        }
    }

    /**
     * Load column widths from the session.
     * This method can be overridden to customize session retrieval.
     */
    protected function loadColumnWidthsFromSession(): void
    {
        $sessionKey = $this->getSessionKey();
        $this->columnWidths = session($sessionKey) ?: [];
    }

    /**
     * Load column widths from the database.
     * This method can be overridden to customize database retrieval.
     */
    protected function loadColumnWidthsFromDatabase(): void
    {
        $this->columnWidths = TableSetting::query()
            ->where('user_id', $this->getUserId())
            ->where('resource', $this->getResourceModelFullPath())
            ->value('styles') ?? [];

        $this->persistColumnWidthsToSession();
    }

    public static function isPreservedOnDB(): bool
    {
        return Setup::preserveOnDB();
    }

    public static function isPreservedOnSession(): bool
    {
        return Setup::preserveOnSession();
    }

    public static function isPreservedOnSessionBrowser(): bool
    {
        return Setup::preserveOnSessionBrowser();
    }

    public function getWidth($width): ?string
    {
        if (is_int($width)) {
            $width = "{$width}px";
        }

        return $width;
    }
}
