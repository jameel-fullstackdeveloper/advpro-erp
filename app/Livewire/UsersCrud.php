<?php
namespace App\Livewire;

use Livewire\WithPagination;
use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersCrud extends Component
{

    //use WithPagination;

    public $users, $roles, $name, $email, $user_id, $selectedRoles = [];
    public $password, $password_confirmation;
    public $isOpen = false;
    public $searchTerm = '';
    public $itemsPerPage = 5;



    public function render()
    {
        $this->users = User::where('name', 'like', '%' . $this->searchTerm . '%')
        ->get();

        $this->roles = Role::all();

            return view('livewire.users-crud', [
                'users' => $this->users,
                'roles' => $this->roles,
            ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatch('showModal');
    }

    public function closeModal()
    {
        $this->resetInputFields();
        $this->resetValidation(); // Clear validation error messages
        $this->isOpen = false;
        $this->dispatch('hideModal');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->user_id = '';
        $this->selectedRoles = []; // Reset selected roles
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function store()
    {
        try {
            $this->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $this->user_id,
                'password' => $this->user_id ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
                'selectedRoles' => 'required',
            ]);

            $user = User::updateOrCreate(['id' => $this->user_id], [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password ? bcrypt($this->password) : User::find($this->user_id)->password,
            ]);

            $user->roles()->sync($this->selectedRoles);

            session()->flash('message', $this->user_id ? 'User Updated Successfully.' : 'User Created Successfully.');

            $this->closeModal();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // No need to manually re-open the modal; just prevent the code from closing it.
            // Optionally, you can re-dispatch the showModal event, but this should not be necessary if the modal's open state is tied to the component's state.
            throw $e; // Ensure that the validation errors are properly displayed.
        }

    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray(); // Load existing roles

        // Reset password fields when editing
        $this->password = '';
        $this->password_confirmation = '';

        $this->openModal();
    }

    public function delete($id)
    {
        // Find the user by ID
        $user = User::find($id);

        if ($user) {
            // Detach all roles and permissions associated with the user
            $user->roles()->detach();
            $user->permissions()->detach();

            // Optionally, you can delete related models or records if needed
            // e.g., deleting related models, logs, or other entities associated with the user

            // Delete the user from the database
            $user->delete();

            // Flash a success message to the session
            session()->flash('message', 'User Deleted Successfully.');
        } else {
            // Flash an error message if the user is not found
            session()->flash('message', 'User not found.');
        }
    }
}
