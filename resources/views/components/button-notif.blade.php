@props([
    'count'
])

@if (auth()->user()->user_role === 2)
<a type="button" class="relative inline-flex items-center p-3 text-sm font-medium text-center text-white rounded-full" href="{{ route('dentist_notif') }}" >
@else
<a type="button" class="relative inline-flex items-center p-3 text-sm font-medium text-center text-white rounded-full" href="{{ route('subadmin_notif') }}" >
@endif
    <span class="sr-only">notifications</span>
    @if($count == 0)
        <x-mary-icon name="o-bell" class="w-9 h-9" />
        <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-dark dark:text-white bg-transparent border-2 border-base-200/50 rounded-full -top-[1px] -end-[1px] dark:border-base-200">{{ $count }}</div>
    @else
        <x-mary-icon name="bi.bell-fill" class="w-9 h-9 text-warning" />
        <div class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-dark bg-warning border-2 border-base-200/50 rounded-full -top-[1px] -end-[1px] dark:border-base-200">{{ $count }}</div>
    @endif
</a>
