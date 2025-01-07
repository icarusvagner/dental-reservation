<?php

use App\Models\DentalClinic;
use App\Models\SmsNotification;
use App\Support\TimeRange;
use App\Support\SendSMS;
use App\Models\Reservations;
use App\Models\WebNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

use Livewire\Volt\Component;

new class extends Component {
    use Toast;

    public object|null $reservations = null;
    public bool $resrv_modal = false;
    public int $resrv_id;
    public int $resrv_status = 0;
    public string|null $temp_app_q = "";
    public int|null $temp_d_id = 0;
    public string $cn = "";

    public array $soryBy = [
        "column" => "reservations.id",
        "direction" => "asc",
    ];

    public function mount($appointment_q = "", $d_id = 0): void
    {
        $this->temp_app_q = $appointment_q;
        $this->temp_d_id = $d_id;
        $this->fetch_reservation($this->temp_app_q, $this->temp_d_id);
    }

    public function fetch_reservation($aq, $d_id): void
    {
        $result = Reservations::query()
            ->leftjoin("users", "reservations.user_id", "=", "users.id")
            ->leftjoin("details", "users.details_id", "=", "details.id")
            ->leftjoin(
                "services",
                "reservations.service_id",
                "=",
                "services.id"
            )
            ->select(
                DB::raw("
            services.service_name,
            services.service_price,
            reservations.id as resrv_id,
            reservations.reservation_datetime,
            reservations.reserve_type,
            reservations.count as patient_count,
            reservations.reservation_status,
            concat(details.first_name, ' ', SUBSTRING(details.middle_name, 1, 1), '. ', details.last_name) as full_name,
            details.contact_no
        ")
            );

        if ($aq != "") {
            $res = DentalClinic::where("user_id", "=", $d_id)->get();
            $result
                ->where("services.dental_clinic_id", "=", $res[0]["id"])
                ->get();
        }
        $result->orderBy(...array_values($this->soryBy))->get();

        // dd($result);
        $this->reservations = $result->get();
    }

    public function headers(): array
    {
        return [
            ["key" => "resrv_id", "label" => "#", "class" => "w-10"],
            ["key" => "full_name", "label" => "Full Name", "class" => "w-40"],
            [
                "key" => "service_name",
                "label" => "Service",
                "class" => "w-24",
                "sortable" => false,
            ],
            [
                "key" => "service_price",
                "label" => "Service amount",
                "class" => "w-24",
                "sortable" => false,
            ],
            [
                "key" => "reservation_datetime",
                "label" => "Reservation time",
                "class" => "w-32",
                "sortable" => false,
            ],
            [
                "key" => "reserve_type",
                "label" => "Reservation Type",
                "class" => "w-24",
                "sortable" => false,
            ],
            [
                "key" => "patient_count",
                "label" => "Patient count",
                "class" => "w-24",
                "sortable" => false,
            ],
            [
                "key" => "reservation_status",
                "label" => "Status",
                "class" => "w-24",
                "sortable" => false,
            ],
        ];
    }

    public function sortBy(): array
    {
        return [
            "column" => "reservations.id",
            "direction" => "asc",
        ];
    }

    public function show_detail(array $details): void
    {
        $this->resrv_id = $details["resrv_id"];
        $this->resrv_modal = true;
        $this->cn = $details["contact_no"];
        $this->fetch_reservation($this->temp_app_q, $this->temp_d_id);
        return;
    }

    public function resrv_status(): array
    {
        // 0    --- Pending
        // 1    --- Accept
        // 2    --- Denied/Reject
        // 3    --- Complete
        // 4    --- Error
        return [
            ["id" => 0, "name" => "Pending"],
            ["id" => 1, "name" => "Accept"],
            ["id" => 2, "name" => "Reject"],
            ["id" => 3, "name" => "Complete"],
            // [ "id" => 4, "name" => 'Error'],
        ];
    }

    public function update_reservation(): void
    {
        $result = Reservations::find($this->resrv_id);
        $result->reservation_status = $this->resrv_status;
        $result->save();

        $this->fetch_reservation($this->temp_app_q, $this->temp_d_id);
        $this->resrv_modal = false;
        $this->success("Status updated", "toast-top toast-right");
        $stat_msg = "";
        if ($this->resrv_status == 0) {
            $stat_msg = "set to pending";
        } elseif ($this->resrv_status == 1) {
            $stat_msg = "accepted";
        } elseif ($this->resrv_status == 2) {
            $stat_msg = "rejected";
        } else {
            $stat_msg = "completed";
        }
        WebNotification::create([
            "user_id" => Auth::user()->id,
            "appointment_id" => $this->resrv_id,
            "web_message" =>
                "Admin has " .
                $stat_msg .
                " your appointment on " .
                TimeRange::consiseDatetime(now()),
            "web_date_time" => now(),
        ]);
        SmsNotification::create([
            "user_id" => Auth::user()->id,
            "appointment_id" => $this->resrv_id,
            "sms_message" =>
                "Admin has " .
                $stat_msg .
                " your appointment on " .
                TimeRange::consiseDatetime(now()),
            "sms_date_time" => now(),
        ]);

        SendSMS::sendMessage(
            $this->cn,
            "Dental Reservation SOY - Admin has " .
                $stat_msg .
                " your appointment on " .
                TimeRange::consiseDatetime(now())
        );
    }
};
?>

<div class="w-full p-3">
    <x-mary-header size="text-xl md:text-4xl" title="{{ __('Appointments') }}" separator progress-indicator />
    <x-mary-table :headers="$this->headers()" :rows="$this->reservations" :sort-by="$this->sortBy()" @row-click="$wire.show_detail($event.detail)" >
        @scope('cell_reservation_status', $user)
            @if($user->reservation_status == 0)
                <x-mary-badge value="Pending" class="badge-warning" />
            @elseif ($user->reservation_status == 1)
                <x-mary-badge value="Accepted" class="badge-gray-500 text-white" />
            @elseif($user->reservation_status == 2)
                <x-mary-badge value="Rejected" class="badge-error" />
            @elseif($user->reservation_status == 3)
                <x-mary-badge value="Completed" class="badge-success" />
            @else
                <x-mary-badge value="Error" class="badge-error" />
            @endif
        @endscope
        @scope('cell_service_price', $user)
            {{ $user->service_price * $user->patient_count }}
        @endscope
    </x-mary-table>


    <x-mary-modal wire:model="resrv_modal" title="Update appointment status" no-separator persistent >
        <x-mary-select label="Appointment Status" :options="$this->resrv_status()" wire:model="resrv_status" />

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.resrv_modal = false" />
            <x-mary-button label="Confirm" class="btn-primary" @click="$wire.update_reservation()" spinner="update_reservation" />
        </x-slot:actions>
    </x-mary-modal>
</div>
