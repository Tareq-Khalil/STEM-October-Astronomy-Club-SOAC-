<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->date('date');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_interval')->nullable();
            
            // Add the next_date column for recurring transactions
            $table->date('next_date')->nullable(); // Store the next transaction date
    
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['account_id']);
            $table->dropForeign(['user_id']);
            // Drop next_date column if rolling back the migration
            $table->dropColumn('next_date');
        });
    
        Schema::dropIfExists('transactions');
    }
    
};
