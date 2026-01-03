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
        Schema::table('reservations', function (Blueprint $table) {
            // user_id カラムを追加（NULL許可 = ゲスト予約にも対応）
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // 外部キー制約を追加
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null'); // ユーザー削除時はNULLに設定
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['user_id']);

            // カラムを削除
            $table->dropColumn('user_id');
        });
    }
};
