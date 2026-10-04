<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type', 30)->default('group'); // direct | group
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('unit', 100)->nullable();
            $table->string('owner_id', 100)->default('u-virga');
            $table->json('member_ids')->nullable();
            $table->json('related_request_ids')->nullable();
            $table->integer('unread_count')->default(0);
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('conversation_id', 100)->index();
            $table->string('sender_id', 100);
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->json('reply_to')->nullable();
            $table->string('status', 30)->default('sent'); // sending | sent | delivered | read
            $table->boolean('deleted')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['conversation_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversations');
    }
};
