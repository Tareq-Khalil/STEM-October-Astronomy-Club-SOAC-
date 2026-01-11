<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('budget', 10, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        
            // Ensure the account name is unique per user
            $table->unique(['name', 'user_id']);
        });
        
    }
    
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
    
};
