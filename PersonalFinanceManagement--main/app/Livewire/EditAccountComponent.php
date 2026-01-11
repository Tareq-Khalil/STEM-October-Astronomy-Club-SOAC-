<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Account;

class EditAccountComponent extends Component
{
    // Form fields
    public $accountId;
    public $name;
    public $type;
    public $balance;
    public $isEditing = false;

    // Validation rules
    protected $rules = [
        'name' => 'required|string|max:255|unique:accounts,name,' . '$this->accountId',
        'type' => 'required|in:CURRENT,SAVINGS',
    ];
    
    // Custom error messages
    protected $messages = [
        'name.required' => 'The account name is required.',
        'name.max' => 'The account name must not exceed 255 characters.',
        'name.unique' => 'An account with this name already exists.',
        'type.required' => 'The account type is required.',
        'type.in' => 'The account type must be either "Current" or "Savings".',
    ];

    // Listen for the 'editAccount' event
    protected $listeners = ['editAccount' => 'loadAccount'];

    // Initialize the component
    public function mount($accountId = null)
    {
        if ($accountId) {
            $this->loadAccount($accountId);
        }
    }

    // Load account data for editing
    public function loadAccount($accountId)
    {
        $account = Account::findOrFail($accountId);
        $this->accountId = $account->id;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->balance = $account->balance;
        $this->isEditing = true;
    }

    // Update account
    public function updateAccount()
    {
        $account = Account::findOrFail($this->accountId);

        // Check if any field has changed
        if ($this->name === $account->name && $this->type === $account->type) {
            session()->flash('error', 'No changes were made.');
            return;
        }

        // Add unique validation for name only if it has changed
        if ($this->name !== $account->name) {
            $this->rules['name'] = 'required|string|max:255|unique:accounts,name,' . $this->accountId;
        } else {
            // Remove unique validation if name hasn't changed
            $this->rules['name'] = 'required|string|max:255';
        }

        // Validate the form data
        $this->validate();

        // Update the account
        $account->update([
            'name' => $this->name,
            'type' => $this->type,
        ]);

        // Flash success message
        session()->flash('message', 'Account updated successfully!');

        // Close the modal and refresh the page
        $this->dispatch('closeEditAccountModal');
        $this->dispatch('refreshPage');
    }

    // Close the modal and reset form fields
    public function closeModal()
    {
        $this->reset(['accountId', 'name', 'type', 'balance', 'isEditing']);
        $this->dispatch('closeEditAccountModal');
        $this->dispatch('refreshPage');
    }

    // Render the component
    public function render()
    {
        return view('livewire.edit-account-component')
            ->layout('layouts.app');
    }
}