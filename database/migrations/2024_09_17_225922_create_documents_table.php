<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('type');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('tags')->nullable();
            $table->string('thumbnailPath')->nullable();
            $table->json('contact_details')->nullable();
            $table->json('dates')->nullable();
            $table->json('actionable_data')->nullable();
            $table->json('analysis_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};