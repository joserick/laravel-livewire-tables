<?php

namespace Rappasoft\LaravelLivewireTables\Traits;

use Livewire\Attributes\Url;
use Rappasoft\LaravelLivewireTables\Events\ColumnsSelected;
use Rappasoft\LaravelLivewireTables\Traits\Configuration\ColumnSelectConfiguration;
use Rappasoft\LaravelLivewireTables\Traits\Helpers\ColumnSelectHelpers;

trait WithColumnSelect
{
    use ColumnSelectConfiguration,
        ColumnSelectHelpers;

    #[Url(history: true, as: 'columns')]
    public array $selectedColumns = [];

    protected bool $columnSelectStatus = true;

    protected bool $rememberColumnSelectionStatus = true;

    protected bool $columnSelectHiddenOnMobile = false;

    protected bool $columnSelectHiddenOnTablet = false;

    public function setupColumnSelect(): void
    {
        // If remember selection is off, then clear the session
        if ($this->rememberColumnSelectionIsDisabled()) {
            $this->forgetColumnSelectSession();
        }

        // If the column select is off, make sure to clear the session
        if ($this->columnSelectIsDisabled() && session()->has($this->getColumnSelectSessionKey())) {
            session()->forget($this->getColumnSelectSessionKey());
        }

        // Get a list of visible default columns that are not excluded
        $columns = $this->getDefaultVisibleColumns();

        // Set to either the default set or what is stored in the session
        $this->selectedColumns = (isset($this->selectedColumns) && count($this->selectedColumns) > 0) ?
            $this->selectedColumns :
            session()->get($this->getColumnSelectSessionKey(), $columns);

        // Check to see if there are any excluded that are already stored in the enabled and remove them
        foreach ($this->getColumns() as $column) {
            if (! $column->isSelectable() && ! in_array($column->getSlug(), $this->selectedColumns, true)) {
                $this->selectedColumns[] = $column->getSlug();
                session([$this->getColumnSelectSessionKey() => $this->selectedColumns]);
            }
        }
    }

    public function getDefaultVisibleColumns(): array
    {
        return collect($this->getColumns())
            ->filter(function ($column) {
                return $column->isVisible() && $column->isSelectable() && $column->isSelected();
            })
            ->map(fn ($column) => $column->getSlug())
            ->values()
            ->toArray();
    }

    public function selectAllColumns()
    {
        $this->selectedColumns = [];
        $this->forgetColumnSelectSession();
        event(new ColumnsSelected($this->getColumnSelectSessionKey(), $this->selectedColumns));
    }

    public function deselectAllColumns()
    {
        $this->selectedColumns = [];
        session([$this->getColumnSelectSessionKey() => []]);
        event(new ColumnsSelected($this->getColumnSelectSessionKey(), $this->selectedColumns));
    }

    public function updatedSelectedColumns(): void
    {
        // The query string isn't needed if it's the same as the default
        if ($this->allDefaultVisibleColumnsAreSelected() && $this->allSelectedColumnsAreVisibleByDefault()) {
            $this->selectAllColumns();
        } else {
            session([$this->getColumnSelectSessionKey() => $this->selectedColumns]);
            event(new ColumnsSelected($this->getColumnSelectSessionKey(), $this->selectedColumns));
        }
    }

    public function allDefaultVisibleColumnsAreSelected(): bool
    {
        return count(array_intersect($this->selectedColumns, $this->getDefaultVisibleColumns())) === count($this->getDefaultVisibleColumns());
    }

    public function allSelectedColumnsAreVisibleByDefault(): bool
    {
        return count(array_intersect($this->selectedColumns, $this->getDefaultVisibleColumns())) === count($this->selectedColumns);
    }
}
