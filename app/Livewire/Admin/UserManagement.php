<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';

    // Form properties
    public $showModal = false;
    public $editMode = false;
    public $userId;
    public $name;
    public $email;
    public $phone;
    public $role = 'sales';
    public $password;
    public $password_confirmation;
    public $is_active = true;
    public $photo;

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->userId)
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,sales,gudang,supir',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ];

        if (!$this->editMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function openEditModal($userId)
    {
        $user = User::findOrFail($userId);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->is_active = $user->is_active;

        $this->showModal = true;
        $this->editMode = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'userId', 'name', 'email', 'phone', 'role',
            'password', 'password_confirmation', 'is_active', 'photo'
        ]);
        $this->is_active = true;
        $this->role = 'sales';
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $user = User::findOrFail($this->userId);

                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => $this->role,
                    'is_active' => $this->is_active,
                ];

                if ($this->password) {
                    $userData['password'] = Hash::make($this->password);
                }

                $user->update($userData);

                // Update role
                $user->syncRoles([$this->role]);

                session()->flash('success', 'User berhasil diperbarui!');
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => $this->role,
                    'password' => Hash::make($this->password),
                    'is_active' => $this->is_active,
                    'email_verified_at' => now(),
                ]);

                // Assign role
                $user->assignRole($this->role);

                session()->flash('success', 'User berhasil dibuat!');
            }

            // Handle photo upload
            if ($this->photo) {
                // Remove old photo if editing
                if ($this->editMode && $user->photo) {
                    \Storage::disk('public')->delete('photos/' . $user->photo);
                }

                // Store new photo
                $filename = time() . '_' . $this->photo->getClientOriginalName();
                $path = $this->photo->storeAs('photos', $filename, 'public');
                $user->update(['photo' => $filename]);
            }

            $this->closeModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "User berhasil {$status}!");
    }

    public function deleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Don't allow deleting current user
            if ($user->id === auth()->id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun sendiri!');
                return;
            }

            $user->delete();
            session()->flash('success', 'User berhasil dihapus!');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => ['admin', 'sales', 'gudang', 'supir']
        ]);
    }
}
