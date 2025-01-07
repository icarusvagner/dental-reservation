<?php

use App\Models\WebNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $notifications;

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
    you are logged in
</div>
