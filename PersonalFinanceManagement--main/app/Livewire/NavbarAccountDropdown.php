<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;

class NavbarAccountDropdown extends Component
{
    use WithPagination;

    public $confirmingDelete = null;
    public $editingAccount = null;
    public $showEditModal = false;

    public $name, $type, $balance;
    public $showForm = false; // Control form visibility
    protected $listeners = ['refreshPage' => '$refresh'];

    public function showCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function showEditForm($accountId)
    {
        $this->editingAccount = $accountId;
        $this->showEditModal = true;
        $this->dispatch('editAccount', $accountId); // Pass account ID to EditAccountComponent
    }
    public function closeEditForm()
    {
        $this->showEditModal = false;
        $this->editingAccount = null;
    }

    public function resetForm()
    {
        $this->editingAccount = null;
        $this->name = '';
        $this->type = '';
        $this->balance = '';
    }

    public function confirmDelete($accountId)
    {
        $this->confirmingDelete = $accountId;
    }

    public function deleteAccount($accountId)
    {
        $account = Account::where('id', $accountId)->where('user_id', Auth::id())->firstOrFail();
        $account->delete();
        $this->confirmingDelete = null;

        session()->flash('message', 'Account deleted successfully.');
        $this->dispatch('refreshPage'); // Ensure UI updates
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function render()
    {
        return view('livewire.navbar-account-dropdown', [
            'accounts' => Account::where('user_id', Auth::id())->get(),
        ])->layout('layouts.app');
    }
}