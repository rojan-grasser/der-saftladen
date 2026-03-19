<?php

use App\Enums\Bucket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->nullOnDelete();
            $table->string('file_path');
            $table->enum('bucket', array_column(Bucket::cases(), 'value'));
            $table->bigInteger('size');
            $table->timestamps();

            $table->index(['bucket', 'file_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
