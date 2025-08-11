<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ActivityLogManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $logName = '';
    public $causerType = '';
    public $subjectType = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'logName' => ['except' => ''],
        'causerType' => ['except' => ''],
        'subjectType' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLogName()
    {
        $this->resetPage();
    }

    public function updatingCauserType()
    {
        $this->resetPage();
    }

    public function updatingSubjectType()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'logName', 'causerType', 'subjectType', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function getActivityLogsProperty()
    {
        $query = Activity::with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('causer', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->logName, function ($query) {
                $query->where('log_name', $this->logName);
            })
            ->when($this->causerType, function ($query) {
                $query->where('causer_type', $this->causerType);
            })
            ->when($this->subjectType, function ($query) {
                $query->where('subject_type', $this->subjectType);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->latest();

        return $query->paginate($this->perPage);
    }

    public function getLogNamesProperty()
    {
        return Activity::distinct()->pluck('log_name')->filter()->sort();
    }

    public function getCauserTypesProperty()
    {
        return Activity::distinct()->pluck('causer_type')->filter()->map(function ($type) {
            return class_basename($type);
        })->sort();
    }

    public function getSubjectTypesProperty()
    {
        return Activity::distinct()->pluck('subject_type')->filter()->map(function ($type) {
            return class_basename($type);
        })->sort();
    }

    public function render()
    {
        return view('livewire.admin.activity-log-management', [
            'activityLogs' => $this->activityLogs,
            'logNames' => $this->logNames,
            'causerTypes' => $this->causerTypes,
            'subjectTypes' => $this->subjectTypes,
        ]);
    }
}
