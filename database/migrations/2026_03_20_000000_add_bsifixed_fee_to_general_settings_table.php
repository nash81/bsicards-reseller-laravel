<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->decimal('bsifixed_fee', 8, 2)->nullable()->after('bsiload_fee');
        });
    }

    public function down() {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('bsifixed_fee');
        });
    }
};

