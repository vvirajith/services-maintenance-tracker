<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->date('handover_date');
            $table->text('handover_note')->nullable();
            $table->date('pickup_date')->nullable();
            $table->text('pickup_note')->nullable();
            $table->string('service_center')->nullable();
            $table->timestamps();

            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
