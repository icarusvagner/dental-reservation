<?php

use App\Models\DentalClinic;
use App\Models\Reservations;
use App\Models\User;
use App\Models\WebNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $notifications;
    public int $dentists;
    public int $clinics;
    public int $sales;
    public int $patients;

    public function mount(): void
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications(): void
    {
        $result = WebNotification::query()
            ->where("notif_status", "=", 0)
            ->where("user_id", "=", 0)
            ->get();

        $this->dentists = User::where("user_role", "=", 2)->count();
        $this->clinics = DentalClinic::count();
        $this->patients = User::where("user_role", "=", 3)->count();
        $this->sales = Reservations::leftjoin(
            "services",
            "reservations.service_id",
            "=",
            "services.id"
        )
            ->where("reservations.reservation_status", "=", 3)
            ->sum("services.service_price");
        $this->notifications = $result->count();
    }
};
?>

<div class="w-full p-3">
    <x-mary-header size="text-xl md:text-4xl" title="{{ __('Dashboard') }}" separator progress-indicator >
        <x-slot:actions>
            <x-button-notif :count="$this->notifications" />
        </x-slot:actions>
    </x-mary-header>
    <div class="w-full pt-12 place-items-center grid grid-cols-2 gap-8">
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Total Patients</h1>
            <span class="text-5xl">{{ $this->patients }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Total Dentist</h1>
            <span class="text-5xl">{{ $this->dentists }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Total Sales</h1>
            <span class="text-5xl">{{ $this->sales }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Total Clinics</h1>
            <span class="text-5xl">{{ $this->clinics }}</span>
        </div>
    </div>
</div>
