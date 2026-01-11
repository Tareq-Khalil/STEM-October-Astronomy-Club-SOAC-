<?php 

namespace App\Livewire;

use Livewire\Component;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 

class AccountComponent extends Component
{
    public $accountId;
    public $name;
    public $type = 'CURRENT';
    public $balance = 1.00;
    public $isDefault = false;
    public $isSelected = false;

    public $isEditing = false;
    public $editingAccountId = null;
    public $editingAccount = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:CURRENT,SAVINGS',
        'balance' => 'required|numeric|min:0|max:999999999999.99', 
        'isDefault' => 'boolean',
        'isSelected' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'The account name is required.',
        'name.max' => 'The account name must not exceed 255 characters.',
        'type.required' => 'The account type is required.',
        'type.in' => 'The account type must be either "Current" or "Savings".',
        'balance.required' => 'The initial balance is required.',
        'balance.numeric' => 'The initial balance must be a number.',
        'balance.min' => 'The initial balance must be at least 0.',
        'balance.max' => 'The initial balance must not exceed 999,999,999,999.99.',
        'isDefault.boolean' => 'The "Set as Default" field must be true or false.',
        'isSelected.boolean' => 'The "Set as Selected" field must be true or false.',
    ];

    public function mount($accountId = null)
    {
        if ($accountId) {
            $this->editingAccount = Account::findOrFail($accountId);
            $this->name = $this->editingAccount->name;
            $this->type = $this->editingAccount->type;
            $this->balance = $this->editingAccount->balance;
            $this->isDefault = $this->editingAccount->is_default;
            $this->isSelected = $this->editingAccount->is_selected;
            $this->isEditing = true;
        }
    }
    public function showEditForm($accountId)
{
    $account = Account::find($accountId);

    if (!$account) {
        session()->flash('error', 'Account not found.');
        return;
    }

    // Set properties for editing
    $this->accountId = $account->id;
    $this->name = $account->name;
    $this->type = $account->type;
    $this->balance = $account->balance;
    $this->isEditing = true;

    // Dispatch JavaScript event to open modal
    $this->dispatch('openEditAccountModal');
}
public function saveAccount()
{
    $this->validate();

    if ($this->editingAccount) {
        // Update existing account (but not balance)
        $this->editingAccount->update([
            'name' => $this->name,
            'type' => $this->type,
            'is_default' => $this->isDefault,
            'is_selected' => $this->isSelected,
        ]);

        session()->flash('message', 'Account updated successfully!');
    } else {
        // Case-insensitive duplicate check
        $existingAccount = Account::where('user_id', Auth::id())
            ->whereRaw('LOWER(name) = ?', [Str::lower($this->name)])
            ->exists();

        if ($existingAccount) {
            $this->addError('name', 'An account with this name already exists.');
            return;
        }

        // Ensure only one default account exists
        if ($this->isDefault) {
            Account::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        // Ensure only one selected account exists
        if ($this->isSelected) {
            Account::where('user_id', Auth::id())->update(['is_selected' => false]);
        }

        // Create the new account
        Account::create([
            'name' => $this->name,
            'type' => $this->type,
            'balance' => $this->balance, // Ensure balance is properly set
            'is_default' => $this->isDefault,
            'is_selected' => $this->isSelected,
            'user_id' => Auth::id(),
        ]);

        session()->flash('message', 'Account created successfully!');
    }

    // Reset form fields
    $this->reset(['name', 'type', 'balance', 'isDefault', 'isSelected', 'editingAccount']);

    // Dispatch events
    $this->dispatch('accountCreated');
    $this->dispatch('closeAccountForm');
    $this->dispatch('refreshPage');
}
public function closeModal()
{
    $this->reset(['name', 'type', 'balance', 'isDefault', 'isSelected', 'editingAccount']);
    $this->dispatch('closeAccountForm');
}

    public function render()
    {
        return view('livewire.account-component')->layout('layouts.app');
    }
}



    
