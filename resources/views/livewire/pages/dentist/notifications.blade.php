
<?php
use Illuminate\Support\Facades\DB;

use Mary\Traits\Toast;

use App\Models\WebNotification;
use Livewire\Volt\Component;

new class extends Component {
    use Toast;

    public object $notifications;

    public function mount(): void
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications(): void
    {
        // $this->notifications = WebNotification::where("user_id", "=", 0)->get();
        $this->notifications = WebNotification::query()
            ->leftjoin(
                "reservations",
                "web_notifications.appointment_id",
                "=",
                "reservations.id"
            )
            ->leftjoin(
                "services",
                "reservations.service_id",
                "=",
                "services.id"
            )
            ->leftjoin(
                "dental_clinic",
                "services.dental_clinic_id",
                "=",
                "dental_clinic.id"
            )
            ->select(
                DB::raw("
                   	web_notifications.id ,
                   	web_notifications.web_message,
                   	web_notifications.web_date_time,
                   	web_notifications.created_at,
                   	reservations.reserve_type,
                   	reservations.count,
                   	services.service_name,
                   	services.service_price,
                   	dental_clinic.clinic_name,
                    web_notifications.notif_status,
                    web_notifications.dentist_notif
                ")
            )
            ->where("dental_clinic.user_id", "=", Auth::user()->id)
            ->get();
    }

    public function headers(): array
    {
        return [
            ["key" => "id", "label" => "#"],
            ["key" => "web_message", "label" => "Message"],
            [
                "key" => "web_date_time",
                "label" => "Date time",
                "sortable" => false,
            ],
            [
                "key" => "created_at",
                "label" => "Date set",
                "sortable" => false,
            ],
            [
                "key" => "reserve_type",
                "label" => "Reservation type",
                "sortable" => false,
            ],
            [
                "key" => "count",
                "label" => "Patient count",
                "class=" => "w-14",
                "sortable" => false,
            ],
            [
                "key" => "service_name",
                "label" => "Service name",
                "sortable" => false,
            ],
            [
                "key" => "service_price",
                "label" => "Price",
                "class=" => "w-16",
                "sortable" => false,
            ],
            [
                "key" => "clinic_name",
                "label" => "Clinic name",
                "sortable" => false,
            ],
            ["key" => "dentist_notif", "label" => "Status"],
        ];
    }

    public function readNotif($notif_id): void
    {
        if ($notif_id != 0) {
            $result = WebNotification::find($notif_id);
            $result->notif_status = 1;
            $result->save();

            $this->fetchNotifications();
            return;
        }

        WebNotification::where("id", ">", 0)->update(["dentist_notif" => 1]);

        $this->fetchNotifications();
        return;
    }
};
?>

<div class="w-full p-3">
    <x-mary-header size="text-xl md:text-4xl" title="{{ __('Notifications') }}" separator progress-indicator >
        <x-slot:actions>
            <x-mary-button label="Read all" class="btn-ghost text-success" @click="$wire.readNotif(0)" icon="bi.check-all" spinner="readNotif" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-table :headers="$this->headers()" :rows="$this->notifications" striped @row-click="$wire.readNotif($event.detail['id'])" show-empty-text empty-text="No Available data." >
        @scope('cell_dentist_notif', $notif)
            @if($notif->dentist_notif == 0)
                <x-mary-badge value="Unread" class="badge-warning" />
            @else
                <x-mary-badge value="Read" class="" />
            @endif
        @endscope
        @scope('cell_service_price', $user)
            {{ $user->service_price * $user->count }}
        @endscope
    </x-mary-table>
</div>
