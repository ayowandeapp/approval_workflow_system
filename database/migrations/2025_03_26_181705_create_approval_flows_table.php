<?php

use App\Enums\ActivationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */


    public function up(): void
    {
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approver_id');
            // $table->unsignedBigInteger('department_id');
            $table->foreign('approver_id')->references('id')->on('users');
            // $table->foreign('department_id')->references('id')->on('departments');
            $table->string('level');
            $table->enum('is_active', array_column(ActivationStatus::cases(), 'value'))
                ->default(ActivationStatus::Active->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};
