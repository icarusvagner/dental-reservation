<x-mary-menu-item title="Appointments" icon="o-calendar-days" link="{!! route('appointment', ['q' => 'dentist', 'id' => auth()->user()->id]) !!}" />
<x-mary-menu-item title="Patients" icon="iconpark.peoplestwo" link="{!! route('patients') !!}" />
<x-mary-menu-item title="Clinic" icon="bx.clinic" link="{!! route('clinic') !!}" />
<x-mary-menu-item title="Notifications" icon="o-bell" link="{!! route('dentist_notif') !!}" />
