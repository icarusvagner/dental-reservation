
<?php
use App\Models\Details;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $detail_modal = false;
    public array $soryBy = [
        "column" => "details.id",
        "direction" => "asc",
    ];
    public string|null $first_name = "";
    public string|null $last_name = "";
    public string|null $middle_name = "";
    public string|null $contact_no = "";
    public string|null $gender = "";
    public string|null $address = "";
    public string|null $email = "";
    public string|null $dental_clinic = "";
    public string|null $created_at = "";
    public string|null $updated_at = "";
    public int|null $id = null;

    public function remove()
    {
        $this->detail_modal = false;
        $result = Details::find($this->id);
        $result->acct_status = 1;
        $result->save();

        if ($result) {
            $this->success(
                "Deleted",
                "You've removed $result->first_name successfully",
                position: "toast-top toast-right"
            );
            $this->detail_modal = false;
        } else {
            $this->success(
                "Failed",
                "Something wen't wrong when removing $result->first_name.",
                position: "toast-top toast-right"
            );
        }
    }

    public function dentists(): Collection
    {
        return Details::query()
            ->leftJoin("users", "details.id", "=", "users.details_id")
            ->select(
                DB::raw('
                details.id as detail_id,
                details.first_name,
                details.middle_name,
                details.last_name,
                details.gender,
                details.contact_no,
                details.address,
                users.email,
                users.email_verified_at,
                users.created_at,
                users.updated_at
            ')
            )
            ->where("users.user_role", "=", 2)
            ->where("details.acct_status", "=", 0)
            ->orderBy(...array_values($this->soryBy))
            ->get();
    }

    public function sortBy(): array
    {
        return [
            "column" => "details.id",
            "direction" => "asc",
        ];
    }

    public function headers(): array
    {
        return [
            ["key" => "detail_id", "label" => "#", "class" => "w-10"],
            ["key" => "first_name", "label" => "First Name", "class" => "w-72"],
            [
                "key" => "middle_name",
                "label" => "Middle Name",
                "class" => "w-72",
            ],
            ["key" => "last_name", "label" => "Last Name", "class" => "w-72"],
            ["key" => "gender", "label" => "Gender", "class" => "w-8"],
            [
                "key" => "contact_no",
                "label" => "Contact No.",
                "class" => "w-16",
            ],
            ["key" => "email", "label" => "E-mail", "sortable" => false],
            [
                "key" => "email_verified_at",
                "label" => "Registration Status",
                "sortable" => false,
            ],
        ];
    }

    public function show_detail(array $details)
    {
        $this->first_name = $details["first_name"];
        $this->last_name = $details["last_name"];
        $this->middle_name = $details["middle_name"] ?? "N/A";
        $this->contact_no = $details["contact_no"] ?? "N/A";
        $this->address = $details["address"] ?? "N/A";
        $this->gender = $details["gender"] ?? "N/A";
        $this->email = $details["email"] ?? "N/A";
        $this->dental_clinic = $details["dental_clinic_name"] ?? "N/A";
        $this->id = $details["detail_id"];
        $this->created_at = $details["created_at"];
        $this->updated_at = $details["updated_at"];
        $this->detail_modal = true;
    }
};
?>

<div class="w-full p-3">
    < x-mary-header size="text-xl md:text-4xl" title="List all Dentist" separator progress-indicator />
    <x-mary-table :headers="$this->headers()" :rows="$this->dentists()" :sort-by="$this->sortBy()" @row-click="$wire.show_detail($event.detail)" >
        @scope('cell_email_verified_at', $user)
            @if($user->email_verified_at == "")
                <x-mary-badge value="Pending" class="badge-warning" />
            @else
                <x-mary-badge value="Registered" class="badge-success" />
            @endif
        @endscope
    </x-mary-table>

    <x-mary-modal wire:model="detail_modal" class="backdrop-blur">
        <div class="space-y-2 mb-2">
            <!-- Full name -->
            <div class="space-y-2 md:space-y-0 md:grid md:grid-cols-3 gap-2 ">
                <x-mary-input readonly  class="rounded-lg" label="First name" wire:model="first_name" />
                <x-mary-input readonly  class="rounded-lg" label="Last name" wire:model="last_name" />
                <x-mary-input readonly  class="rounded-lg" label="Middle name" wire:model="middle_name" />
            </div>

            <!-- Contact address and number -->
            <div class="space-y-2 md:space-y-0 md:grid md:grid-cols-2 gap-2 ">
                <x-mary-input readonly  class="rounded-lg" label="Contact number" wire:model="contact_no" />
                <x-mary-input readonly  class="rounded-lg" label="Gender" wire:model="gender" />
            </div>

            <x-mary-input readonly  class="rounded-lg" label="E-mail" wire:model="email" />
            <x-mary-input readonly  class="rounded-lg" label="Complete address" wire:model="address"  />

        </div>
        <div class="grid grid-cols-2 gap-2">
            <x-mary-input readonly  class="rounded-lg" label="Created Dated" wire:model="created_at" />
            <x-mary-input readonly  class="rounded-lg" label="Updated Dated" wire:model="updated_at"  />
        </div>
        <div class="flex gap-3 items-center mt-5">
            <x-mary-button label="Cancel" @click="$wire.detail_modal = false" />
            @if(auth()->user()->user_role == 0)
                <x-mary-button icon="o-trash" class="btn-circle btn-ghost text-red-500" wire:click="remove" />
            @endif
        </div>
    </x-mary-modal>
</div>
