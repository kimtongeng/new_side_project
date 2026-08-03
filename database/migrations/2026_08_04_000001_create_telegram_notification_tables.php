<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned()->index();
            $table->string('name');
            $table->string('bot_token');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('telegram_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned()->index();
            $table->foreignId('telegram_bot_id')->constrained('telegram_bots')->onDelete('cascade');
            $table->string('location_id')->index(); // e.g. "PT1001" or location ID
            $table->string('group_name');
            $table->string('chat_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('telegram_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_group_id')->constrained('telegram_groups')->onDelete('cascade');
            $table->string('topic_key'); // e.g. "sell", "purchase", "repair", "payment_account"
            $table->string('topic_name'); // e.g. "Sales", "Purchases", "Repairs"
            $table->string('topic_id'); // message_thread_id
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_topics');
        Schema::dropIfExists('telegram_groups');
        Schema::dropIfExists('telegram_bots');
    }
};
