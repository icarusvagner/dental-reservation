<?php

use App\Models\DentalClinic;
use App\Models\User;
use App\Models\WebNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $notifications;
    public int $appointments;
    public int $patients;
    public int $clinics;

    public function mount(): void
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications(): void
    {
        $result = WebNotification::query()
            ->where("dentist_notif", "=", 0)
            ->where("user_id", "=", Auth::user()->id)
            ->get();
        $this->appointments = DentalClinic::where(
            "user_id",
            "=",
            Auth::user()->id
        )->count();
        $this->patients = User::where("user_role", "=", 3)->count();
        $this->clinics = DentalClinic::where(
            "user_id",
            "=",
            Auth::user()->id
        )->count();

        $this->notifications = $result->count();
    }
};
?>

<div class="w-full p-3 h-full">
    <x-mary-header size="text-xl md:text-4xl" title="{{ __('Dashboard') }}" separator progress-indicator >
        <x-slot:actions>
            <x-button-notif :count="$this->notifications" />
        </x-slot:actions>
    </x-mary-header>
    <div class="w-full pt-12 place-items-center grid grid-cols-3 gap-8">
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Appointments</h1>
            <span class="text-5xl">{{ $this->appointments }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Patients</h1>
            <span class="text-5xl">{{ $this->patients }}</span>
        </div>
        <div class="flex flex-col gap-5 text-center bg-slate-700 text-slate-50 rounded-md p-5 w-full">
            <h1 class="text-2xl font-bold">Owned Clinics</h1>
            <span class="text-5xl">{{ $this->clinics }}</span>
        </div>
    </div>
</div>
