<?php

namespace App\Livewire\Supir;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\K3Checklist;
use App\Models\Delivery;

class K3ChecklistSystem extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';

    // Checklist form
    public $showChecklistModal = false;
    public $checklistId;
    public $delivery_id;
    public $kondisi_ban = false;
    public $kondisi_rem = false;
    public $level_oli_mesin = false;
    public $level_bbm = false;
    public $kondisi_lampu = false;
    public $kondisi_spion = false;
    public $kondisi_klakson = false;
    public $kondisi_sabuk_pengaman = false;
    public $kondisi_kaca = false;
    public $kondisi_wiper = false;
    public $kelengkapan_p3k = false;
    public $kelengkapan_apar = false;
    public $kelengkapan_segitiga = false;
    public $kondisi_muatan = false;
    public $catatan_tambahan;
    public $foto_kendaraan;

    // View checklist modal
    public $showViewModal = false;
    public $viewChecklist;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'delivery_id' => 'nullable|exists:deliveries,id',
            'kondisi_ban' => 'boolean',
            'kondisi_rem' => 'boolean',
            'level_oli_mesin' => 'boolean',
            'level_bbm' => 'boolean',
            'kondisi_lampu' => 'boolean',
            'kondisi_spion' => 'boolean',
            'kondisi_klakson' => 'boolean',
            'kondisi_sabuk_pengaman' => 'boolean',
            'kondisi_kaca' => 'boolean',
            'kondisi_wiper' => 'boolean',
            'kelengkapan_p3k' => 'boolean',
            'kelengkapan_apar' => 'boolean',
            'kelengkapan_segitiga' => 'boolean',
            'kondisi_muatan' => 'boolean',
            'catatan_tambahan' => 'nullable|string|max:500',
            'foto_kendaraan' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openChecklistModal($deliveryId = null)
    {
        $this->resetChecklistForm();
        $this->delivery_id = $deliveryId;
        $this->showChecklistModal = true;
    }

    public function closeChecklistModal()
    {
        $this->showChecklistModal = false;
        $this->resetChecklistForm();
    }

    public function resetChecklistForm()
    {
        $this->reset([
            'checklistId', 'delivery_id', 'kondisi_ban', 'kondisi_rem', 'level_oli_mesin',
            'level_bbm', 'kondisi_lampu', 'kondisi_spion', 'kondisi_klakson',
            'kondisi_sabuk_pengaman', 'kondisi_kaca', 'kondisi_wiper',
            'kelengkapan_p3k', 'kelengkapan_apar', 'kelengkapan_segitiga',
            'kondisi_muatan', 'catatan_tambahan', 'foto_kendaraan'
        ]);
    }

    public function createChecklist()
    {
        $this->validate();

        try {
            // Check if already have checklist today
            $existingChecklist = K3Checklist::where('supir_id', auth()->id())
                                          ->whereDate('tanggal_checklist', today())
                                          ->when($this->delivery_id, function($q) {
                                              $q->where('delivery_id', $this->delivery_id);
                                          })
                                          ->first();

            if ($existingChecklist) {
                session()->flash('error', 'Anda sudah membuat checklist hari ini!');
                return;
            }

            // Create checklist
            $checklist = K3Checklist::create([
                'supir_id' => auth()->id(),
                'delivery_id' => $this->delivery_id,
                'tanggal_checklist' => now(),
                'kondisi_ban' => $this->kondisi_ban,
                'kondisi_rem' => $this->kondisi_rem,
                'level_oli_mesin' => $this->level_oli_mesin,
                'level_bbm' => $this->level_bbm,
                'kondisi_lampu' => $this->kondisi_lampu,
                'kondisi_spion' => $this->kondisi_spion,
                'kondisi_klakson' => $this->kondisi_klakson,
                'kondisi_sabuk_pengaman' => $this->kondisi_sabuk_pengaman,
                'kondisi_kaca' => $this->kondisi_kaca,
                'kondisi_wiper' => $this->kondisi_wiper,
                'kelengkapan_p3k' => $this->kelengkapan_p3k,
                'kelengkapan_apar' => $this->kelengkapan_apar,
                'kelengkapan_segitiga' => $this->kelengkapan_segitiga,
                'kondisi_muatan' => $this->kondisi_muatan,
                'catatan_tambahan' => $this->catatan_tambahan,
                'status' => 'pending',
            ]);

            // Upload vehicle photo if provided
            if ($this->foto_kendaraan) {
                $checklist->addMediaFromDisk($this->foto_kendaraan->getRealPath())
                    ->usingName('K3 Checklist Vehicle Photo')
                    ->toMediaCollection('vehicle_photos');
            }

            $this->closeChecklistModal();
            session()->flash('success', 'K3 Checklist berhasil dibuat!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewChecklist($checklistId)
    {
        $this->viewChecklist = K3Checklist::with(['supir', 'delivery', 'approvedBy'])
                                         ->where('supir_id', auth()->id())
                                         ->findOrFail($checklistId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewChecklist = null;
    }

    public function deleteChecklist($checklistId)
    {
        try {
            $checklist = K3Checklist::where('supir_id', auth()->id())
                                   ->findOrFail($checklistId);

            // Only allow deletion if pending and from today
            if ($checklist->status !== 'pending' || !$checklist->tanggal_checklist->isToday()) {
                session()->flash('error', 'Hanya checklist pending hari ini yang dapat dihapus!');
                return;
            }

            $checklist->delete();
            session()->flash('success', 'K3 Checklist berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $checklists = K3Checklist::where('supir_id', auth()->id())
            ->with(['delivery'])
            ->when($this->search, function ($query) {
                $query->where('catatan_tambahan', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('tanggal_checklist', $this->dateFilter);
            })
            ->orderBy('tanggal_checklist', 'desc')
            ->paginate(10);

        $availableDeliveries = Delivery::where('supir_id', auth()->id())
                                     ->where('status', 'assigned')
                                     ->with('order.customer')
                                     ->get();

        $todayChecklists = K3Checklist::where('supir_id', auth()->id())
                                    ->whereDate('tanggal_checklist', today())
                                    ->count();

        $pendingChecklists = K3Checklist::where('supir_id', auth()->id())
                                       ->where('status', 'pending')
                                       ->count();

        return view('livewire.supir.k3-checklist-system', [
            'checklists' => $checklists,
            'availableDeliveries' => $availableDeliveries,
            'todayChecklists' => $todayChecklists,
            'pendingChecklists' => $pendingChecklists,
        ]);
    }
}
