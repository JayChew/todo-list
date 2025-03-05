<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(table: 'tasks', callback: function (Blueprint $table): void {
            $table->id(); // 主键 ID
            $table->string(column: 'title'); // 任务标题
            $table->text(column: 'description')->nullable(); // 任务描述
            $table->boolean(column: 'completed')->default(value: false); // 任务描述
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'tasks');
    }
};
