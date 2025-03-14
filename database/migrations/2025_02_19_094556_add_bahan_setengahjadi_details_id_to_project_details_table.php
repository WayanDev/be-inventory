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
        Schema::table('projek_details', function (Blueprint $table) {
            $table->unsignedBigInteger('bahan_setengahjadi_details_id')->nullable()->after('bahan_id');

            $table->foreign('bahan_setengahjadi_details_id')->references('id')->on('bahan_setengahjadi_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projek_details', function (Blueprint $table) {
            $table->dropForeign(['bahan_setengahjadi_details_id']);
            $table->dropColumn('bahan_setengahjadi_details_id');
        });
    }
};
