<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    // Properties للبيانات والإعدادات
    public $title = '';
    public $searchPlaceholder = 'ابحث...';
    public $showSearch = true;
    public $showFilters = false;
    public $showPerPageSelector = true;
    public $perPage = 25;
    public $search = '';
    public $columns = [];
    public $data = null;
    public $emptyMessage = 'لا توجد بيانات متوفرة';
    public $emptySearchMessage = 'جرب البحث بكلمات أخرى';
    
    // Filters وButtons
    public $filterButtons = [];
    public $activeFilter = '';
    public $headerIcon = 'images/group0.svg';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 25],
    ];

    public function mount(
        $title = '',
        $columns = [],
        $data = null,
        $searchPlaceholder = 'ابحث...',
        $showSearch = true,
        $showFilters = false,
        $showPerPageSelector = true,
        $perPage = 25,
        $emptyMessage = 'لا توجد بيانات متوفرة',
        $emptySearchMessage = 'جرب البحث بكلمات أخرى',
        $filterButtons = [],
        $activeFilter = '',
        $headerIcon = 'images/group0.svg'
    ) {
        $this->title = $title;
        $this->columns = $columns;
        $this->data = $data;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->showSearch = $showSearch;
        $this->showFilters = $showFilters;
        $this->showPerPageSelector = $showPerPageSelector;
        $this->perPage = $perPage;
        $this->emptyMessage = $emptyMessage;
        $this->emptySearchMessage = $emptySearchMessage;
        $this->filterButtons = $filterButtons;
        $this->activeFilter = $activeFilter;
        $this->headerIcon = $headerIcon;
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->dispatch('search-updated', $this->search);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->dispatch('per-page-updated', $this->perPage);
    }

    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        $this->resetPage();
        $this->dispatch('filter-updated', $filter);
    }

    public function render()
    {
        return view('livewire.components.data-table');
    }
}
