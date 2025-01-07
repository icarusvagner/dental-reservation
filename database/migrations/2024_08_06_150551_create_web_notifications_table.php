<?php

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("web_notifications", function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->foreignIdFor(Appointment::class)->cascadeOnDelete();
            $table->string("web_message");
            $table->dateTime("web_date_time", precision: 0);
            // 0 - unread
            // 1 - read
            $table->tinyInteger("notif_status")->default(0);
            $table->tinyInteger("dentist_status")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("web_notifications");
    }
};
