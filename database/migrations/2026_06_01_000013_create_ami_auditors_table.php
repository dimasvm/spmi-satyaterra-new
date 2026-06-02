<?php

use App\Enums\AmiAuditorRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ami_audit_id')->constrained('ami_audits')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default(AmiAuditorRole::Member->value);
            $table->timestamps();

            $table->unique(['ami_audit_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ami_auditors');
    }
};
