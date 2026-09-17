<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->text('affiliation_to_asm')->nullable()->after('discipline_id');
            $table->text('qualifications_professional_memberships')->nullable()->after('area_of_expertise');
        });

        // Backfill existing candidate records
        $candidates = DB::table('candidates')->get();
        foreach ($candidates as $c) {
            $parts = array_filter([$c->qualifications ?? null, $c->professional_memberships ?? null]);
            $combinedQuals = ! empty($parts) ? implode("\n", $parts) : null;

            DB::table('candidates')->where('id', $c->id)->update([
                'affiliation_to_asm' => $c->affiliation_to_asm ?: 'ASM Fellow Nominee',
                'qualifications_professional_memberships' => $combinedQuals ?: 'Information available in dossier.',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['affiliation_to_asm', 'qualifications_professional_memberships']);
        });
    }
};
