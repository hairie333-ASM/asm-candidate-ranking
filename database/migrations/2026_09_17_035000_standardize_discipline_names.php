<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // Swap IDs 5 (former Math/Physics) and 6 (former Medical/Health)
        // so ID 5 becomes MHS (Medical and Health Sciences) and ID 6 becomes MPES (Mathematical and Physical Sciences).
        // Temporary ID 999 is used.
        DB::table('disciplines')->insertOrIgnore([
            'id' => 999,
            'discipline_name' => 'TEMP',
            'description' => 'Temporary discipline for migration swap',
            'display_order' => 999,
            'active' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Reassign current ID 5 to 999
        DB::table('users')->where('discipline_id', 5)->update(['discipline_id' => 999]);
        DB::table('candidates')->where('discipline_id', 5)->update(['discipline_id' => 999]);
        DB::table('ranking_submissions')->where('discipline_id', 5)->update(['discipline_id' => 999]);
        DB::table('rankings')->where('discipline_id', 5)->update(['discipline_id' => 999]);
        DB::table('due_diligence_submissions')->where('user_discipline_id', 5)->update(['user_discipline_id' => 999]);
        DB::table('disciplines')->where('id', 5)->delete();

        // 2. Reassign current ID 6 to 5 (Medical & Health Sciences -> ID 5)
        DB::table('users')->where('discipline_id', 6)->update(['discipline_id' => 5]);
        DB::table('candidates')->where('discipline_id', 6)->update(['discipline_id' => 5]);
        DB::table('ranking_submissions')->where('discipline_id', 6)->update(['discipline_id' => 5]);
        DB::table('rankings')->where('discipline_id', 6)->update(['discipline_id' => 5]);
        DB::table('due_diligence_submissions')->where('user_discipline_id', 6)->update(['user_discipline_id' => 5]);
        DB::table('disciplines')->where('id', 6)->update(['id' => 5]);

        // 3. Reassign 999 to 6 (Mathematical & Physical Sciences -> ID 6)
        DB::table('users')->where('discipline_id', 999)->update(['discipline_id' => 6]);
        DB::table('candidates')->where('discipline_id', 999)->update(['discipline_id' => 6]);
        DB::table('ranking_submissions')->where('discipline_id', 999)->update(['discipline_id' => 6]);
        DB::table('rankings')->where('discipline_id', 999)->update(['discipline_id' => 6]);
        DB::table('due_diligence_submissions')->where('user_discipline_id', 999)->update(['user_discipline_id' => 6]);
        DB::table('disciplines')->where('id', 999)->update(['id' => 6]);

        // Clean up any remaining temp row
        DB::table('disciplines')->where('id', 999)->delete();

        // Standardize all 8 disciplines to the Official Master List
        $officialDisciplines = [
            1 => [
                'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences',
                'description' => 'Shortlisted candidates under BAES - Biological Agriculture and Environmental Sciences',
                'display_order' => 1,
            ],
            2 => [
                'discipline_name' => 'CS - Chemical Sciences',
                'description' => 'Shortlisted candidates under CS - Chemical Sciences',
                'display_order' => 2,
            ],
            3 => [
                'discipline_name' => 'ES - Engineering Sciences',
                'description' => 'Shortlisted candidates under ES - Engineering Sciences',
                'display_order' => 3,
            ],
            4 => [
                'discipline_name' => 'ITCS - Information Technology and Computer Sciences',
                'description' => 'Shortlisted candidates under ITCS - Information Technology and Computer Sciences',
                'display_order' => 4,
            ],
            5 => [
                'discipline_name' => 'MHS - Medical and Health Sciences',
                'description' => 'Shortlisted candidates under MHS - Medical and Health Sciences',
                'display_order' => 5,
            ],
            6 => [
                'discipline_name' => 'MPES - Mathematical and Physical Sciences',
                'description' => 'Shortlisted candidates under MPES - Mathematical and Physical Sciences',
                'display_order' => 6,
            ],
            7 => [
                'discipline_name' => 'STDI - Science and Technology Development and Industry',
                'description' => 'Shortlisted candidates under STDI - Science and Technology Development and Industry',
                'display_order' => 7,
            ],
            8 => [
                'discipline_name' => 'SSH - Social Sciences and Humanities',
                'description' => 'Shortlisted candidates under SSH - Social Sciences and Humanities',
                'display_order' => 8,
            ],
        ];

        foreach ($officialDisciplines as $id => $data) {
            DB::table('disciplines')->updateOrInsert(
                ['id' => $id],
                [
                    'discipline_name' => $data['discipline_name'],
                    'description' => $data['description'],
                    'display_order' => $data['display_order'],
                    'active' => 1,
                    'updated_at' => now(),
                ]
            );
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible due to structural standardization
    }
};
