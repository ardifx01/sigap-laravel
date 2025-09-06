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
    public $dateFilter = '';

    // Checklist form
    public $showChecklistModal = false;
    public $checklistId;
    public $delivery_id;
    public $cek_ban = false;
    public $cek_oli = false;
    public $cek_air_radiator = false;
    public $cek_rem = false;
    public $cek_bbm = false;
    public $cek_terpal = false;
    public $catatan;
    public $foto_kendaraan;

    // View checklist modal
    public $showViewModal = false;
    public $selectedChecklist;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        return [
            'delivery_id' => 'nullable|exists:deliveries,id',
            'cek_ban' => 'boolean',
            'cek_oli' => 'boolean',
            'cek_air_radiator' => 'boolean',
            'cek_rem' => 'boolean',
            'cek_bbm' => 'boolean',
            'cek_terpal' => 'boolean',
            'catatan' => 'nullable|string|max:500',
            'foto_kendaraan' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch()
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
            'checklistId', 'delivery_id', 'cek_ban', 'cek_oli', 'cek_air_radiator',
            'cek_rem', 'cek_bbm', 'cek_terpal', 'catatan', 'foto_kendaraan'
        ]);
    }

    public function createChecklist()
    {
        $this->validate();

        try {
            // Check if already have checklist for this delivery
            if ($this->delivery_id) {
                $existingChecklist = K3Checklist::where('driver_id', auth()->id())
                                              ->where('delivery_id', $this->delivery_id)
                                              ->first();

                if ($existingChecklist) {
                    session()->flash('error', 'Checklist untuk delivery ini sudah ada!');
                    return;
                }

                // Validate delivery exists and belongs to current driver
                $delivery = Delivery::where('id', $this->delivery_id)
                                  ->where('driver_id', auth()->id())
                                  ->where('status', 'assigned')
                                  ->first();

                if (!$delivery) {
                    session()->flash('error', 'Delivery tidak valid atau tidak dapat diupdate!');
                    return;
                }
            } else {
                // General checklist - check if already have one today
                $existingChecklist = K3Checklist::where('driver_id', auth()->id())
                                              ->whereDate('checked_at', today())
                                              ->whereNull('delivery_id')
                                              ->first();

                if ($existingChecklist) {
                    session()->flash('error', 'Anda sudah membuat checklist umum hari ini!');
                    return;
                }
            }

            // Create checklist
            $checklist = K3Checklist::create([
                'driver_id' => auth()->id(),
                'delivery_id' => $this->delivery_id,
                'cek_ban' => $this->cek_ban,
                'cek_oli' => $this->cek_oli,
                'cek_air_radiator' => $this->cek_air_radiator,
                'cek_rem' => $this->cek_rem,
                'cek_bbm' => $this->cek_bbm,
                'cek_terpal' => $this->cek_terpal,
                'catatan' => $this->catatan,
                'checked_at' => now(),
            ]);

            // Upload vehicle photo if provided
            if ($this->foto_kendaraan) {
                $filename = time() . '_' . $this->foto_kendaraan->getClientOriginalName();
                $path = $this->foto_kendaraan->storeAs('vehicle_photos', $filename, 'public');
                $checklist->update(['vehicle_photo' => $filename]);
            }

            // Update delivery status if checklist is complete and linked to delivery
            if ($this->delivery_id && $checklist->isAllItemsPassed()) {
                $delivery = Delivery::find($this->delivery_id);
                if ($delivery && $delivery->status === 'assigned') {
                    $delivery->update([
                        'status' => 'k3_checked',
                        'k3_checked_at' => now(),
                    ]);
                }
            }

            $this->closeChecklistModal();

            $message = 'K3 Checklist berhasil dibuat!';
            if ($this->delivery_id && $checklist->isAllItemsPassed()) {
                $message .= ' Delivery sudah siap untuk dimulai.';
            } elseif ($this->delivery_id && !$checklist->isAllItemsPassed()) {
                $message .= ' Harap lengkapi semua item checklist untuk dapat memulai delivery.';
            }

            session()->flash('success', $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewChecklist($checklistId)
    {
        $this->selectedChecklist = K3Checklist::with(['driver', 'delivery'])
                                             ->where('driver_id', auth()->id())
                                             ->findOrFail($checklistId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedChecklist = null;
    }

    public function editChecklist($checklistId)
    {
        $checklist = K3Checklist::where('driver_id', auth()->id())
                               ->findOrFail($checklistId);

        // Only allow editing if from today and delivery not started yet
        if (!$checklist->checked_at->isToday()) {
            session()->flash('error', 'Hanya checklist hari ini yang dapat diedit!');
            return;
        }

        if ($checklist->delivery && in_array($checklist->delivery->status, ['in_progress', 'delivered'])) {
            session()->flash('error', 'Delivery sudah dimulai, checklist tidak dapat diedit!');
            return;
        }

        // Load checklist data to form
        $this->checklistId = $checklist->id;
        $this->delivery_id = $checklist->delivery_id;
        $this->cek_ban = $checklist->cek_ban;
        $this->cek_oli = $checklist->cek_oli;
        $this->cek_air_radiator = $checklist->cek_air_radiator;
        $this->cek_rem = $checklist->cek_rem;
        $this->cek_bbm = $checklist->cek_bbm;
        $this->cek_terpal = $checklist->cek_terpal;
        $this->catatan = $checklist->catatan;

        $this->showChecklistModal = true;
    }

    public function updateChecklist()
    {
        $this->validate();

        try {
            $checklist = K3Checklist::where('driver_id', auth()->id())
                                   ->findOrFail($this->checklistId);

            if (!$checklist->checked_at->isToday()) {
                session()->flash('error', 'Hanya checklist hari ini yang dapat diedit!');
                return;
            }

            // Update checklist
            $checklist->update([
                'cek_ban' => $this->cek_ban,
                'cek_oli' => $this->cek_oli,
                'cek_air_radiator' => $this->cek_air_radiator,
                'cek_rem' => $this->cek_rem,
                'cek_bbm' => $this->cek_bbm,
                'cek_terpal' => $this->cek_terpal,
                'catatan' => $this->catatan,
            ]);

            // Update delivery status based on checklist completion
            if ($checklist->delivery_id) {
                $delivery = Delivery::find($checklist->delivery_id);
                if ($delivery) {
                    if ($checklist->isAllItemsPassed() && $delivery->status === 'assigned') {
                        $delivery->update([
                            'status' => 'k3_checked',
                            'k3_checked_at' => now(),
                        ]);
                    } elseif (!$checklist->isAllItemsPassed() && $delivery->status === 'k3_checked') {
                        $delivery->update([
                            'status' => 'assigned',
                            'k3_checked_at' => null,
                        ]);
                    }
                }
            }

            $this->closeChecklistModal();
            session()->flash('success', 'K3 Checklist berhasil diupdate!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deleteChecklist($checklistId)
    {
        try {
            $checklist = K3Checklist::where('driver_id', auth()->id())
                                   ->findOrFail($checklistId);

            // Only allow deletion if from today
            if (!$checklist->checked_at->isToday()) {
                session()->flash('error', 'Hanya checklist hari ini yang dapat dihapus!');
                return;
            }

            // Check if delivery is linked and update status back to assigned
            if ($checklist->delivery_id) {
                $delivery = Delivery::find($checklist->delivery_id);
                if ($delivery && $delivery->status === 'k3_checked') {
                    $delivery->update([
                        'status' => 'assigned',
                        'k3_checked_at' => null,
                    ]);
                }
            }

            $checklist->delete();
            session()->flash('success', 'K3 Checklist berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $checklists = K3Checklist::where('driver_id', auth()->id())
            ->with(['driver', 'delivery'])
            ->when($this->search, function ($query) {
                $query->where('catatan', 'like', '%' . $this->search . '%');
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('checked_at', $this->dateFilter);
            })
            ->orderBy('checked_at', 'desc')
            ->paginate(10);

        $availableDeliveries = Delivery::where('driver_id', auth()->id())
                                     ->where('status', 'assigned')
                                     ->with('order.customer')
                                     ->get();

        $todayChecklists = K3Checklist::where('driver_id', auth()->id())
                                    ->whereDate('checked_at', today())
                                    ->count();

        return view('livewire.supir.k3-checklist-system', [
            'checklists' => $checklists,
            'availableDeliveries' => $availableDeliveries,
            'todayChecklists' => $todayChecklists,
        ]);
    }
}
