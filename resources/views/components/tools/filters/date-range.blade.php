@php
    $filterKey = $filter->getKey();
    $filterConfigs = $filter->getConfigs();

    $dateInput = isset($this->filters[$filterKey]) ? $this->filters[$filterKey] : '';
    if ($dateInput != '') {
        if (is_array($dateInput)) {
            $startDate = isset($dateInput['minDate']) ? $dateInput['minDate'] : (isset($dateInput[1]) ? $dateInput[1] : date('Y-m-d'));
            $endDate = isset($dateInput['maxDate']) ? $dateInput['maxDate'] : (isset($dateInput[0]) ? $dateInput[0] : date('Y-m-d'));
        } else {
            $dateArray = explode(',', $dateInput);
            $startDate = isset($dateArray[0]) ? $dateArray[0] : date('Y-m-d');
            $endDate = isset($dateArray[2]) ? $dateArray[2] : date('Y-m-d');
        }
    } else {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
    }

    $startDate = strlen($startDate) > 2 ? $startDate : date('Y-m-d', strtotime('-1 days'));

    $endDate = strlen($endDate) > 2 ? $endDate : date('Y-m-d');

    $dateString = $startDate . ' to ' . $endDate;

@endphp

<div x-cloak id="{{ $tableName }}-dateRangeFilter-{{ $filterKey }}" x-data="flatpickrFilter($wire, '{{ $filterKey }}', @js($filter->getConfigs()), $refs.dateRangeInput{{ $filterKey }}, '{{ App::currentLocale() }}')" >

    <div>
        @if($filter->hasCustomFilterLabel() && !$filter->hasCustomPosition())
            @include($filter->getCustomFilterLabel(), ['filter' => $filter, 'filterLayout' => $filterLayout, 'tableName' => $tableName])
        @elseif(!$filter->hasCustomPosition())
            <x-livewire-tables::tools.filter-label :filter="$filter" :filterLayout="$filterLayout" :tableName="$tableName" />
        @endif

            <div 
                @class([
                    'w-full rounded-md shadow-sm text-right' => $isTailwind,
                    'd-inline-block w-100 mb-3 mb-md-0 input-group' => $isBootstrap,
                ])
            >
                <input 
                    type="text" 
                    x-ref="dateRangeInput{{ $filterKey }}" 
                    x-on:click="init"
                    value="{{ $dateString }}" 
                    wire:key="{{ $tableName }}-filter-dateRange-{{ $filterKey }}" 
                    id="{{ $tableName }}-filter-dateRange-{{ $filterKey }}"
                    @class([
                        'w-full inline transition duration-150 ease-in-out border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white dark:border-gray-600' => $isTailwind,
                        'd-inline-block form-control w-100 pr-2 transition duration-150 ease-in-out border border-gray rounded-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:text-white dark:border-gray-600' => $isBootstrap,
                    ])
                />

                <x-heroicon-o-calendar-days class="w-3 h-3 group-hover:opacity-0" />

            </div>

    </div>
</div>
