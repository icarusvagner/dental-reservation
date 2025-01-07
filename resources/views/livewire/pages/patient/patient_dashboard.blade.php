<?php

use App\Models\DentalClinic;
use App\Models\Reservations;
use App\Models\Schedule;
use App\Models\User;
use App\Models\WebNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $notifications;
    public int $doctors;
    public int $clinics;
    public int $bookings;

    public function mount(): void
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications(): void
    {
        $result = WebNotification::query()
            ->where("notif_status", "=", 0)
            ->where("user_id", "=", 3)
            ->get();

        $this->doctors = Schedule::where("doctor_status", "=", 0)->count();
        $this->clinics = Schedule::where("clinic_status", "=", 0)->count();
        $this->bookings = Reservations::where(
            "user_id",
            "=",
            Auth::user()->id
        )->count();
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
    <div class="w-full pt-12 place-items-center grid grid-cols-3 gap-8">
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Available Dentist</h1>
            <span class="text-5xl">{{ $this->doctors }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Available Clinics</h1>
            <span class="text-5xl">{{ $this->clinics }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Total Bookings</h1>
            <span class="text-5xl">{{ $this->bookings }}</span>
        </div>
    </div>
</div>
