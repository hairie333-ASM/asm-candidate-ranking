<?php

namespace Tests\Feature;

use App\Models\Discipline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_import_command_runs_successfully(): void
    {
        // 1. Seed the 8 disciplines
        $disciplines = [
            1 => 'BAES - Biological Agriculture and Environmental Sciences',
            2 => 'CS - Chemical Sciences',
            3 => 'ES - Engineering Sciences',
            4 => 'ITCS - Information Technology and Computer Sciences',
            5 => 'MHS - Medical and Health Sciences',
            6 => 'MPES - Mathematical and Physical Sciences',
            7 => 'STDI - Science and Technology Development and Industry',
            8 => 'SSH - Social Sciences and Humanities',
        ];
        foreach ($disciplines as $id => $name) {
            Discipline::create([
                'id' => $id,
                'discipline_name' => $name,
                'description' => $name,
                'display_order' => $id,
                'active' => true,
            ]);
        }

        // 2. Insert dummy users
        User::create([
            'name' => 'Dummy Voter',
            'email' => 'dummy_voter@example.test',
            'password' => bcrypt('password'),
            'role' => 'voting_user',
            'discipline_id' => 1,
            'active' => true,
        ]);

        $this->assertEquals(1, User::where('email', 'like', '%@example.test')->count());

        // 3. Run import command
        $exitCode = Artisan::call('users:import-excel');
        $this->assertEquals(0, $exitCode);

        // 4. Verify dummy users were removed
        $this->assertEquals(0, User::where('email', 'like', '%@example.test')->count());

        // 5. Verify real users were imported
        $this->assertEquals(829, User::count());
        $this->assertEquals(819, User::where('role', 'voting_user')->count());
        $this->assertEquals(10, User::where('role', 'administrator')->count());

        // 6. Verify sample fellow authentication
        $fellow = User::where('username', 'bae.yong.h.s56')->first();
        $this->assertNotNull($fellow);
        $this->assertEquals('hoiseny@gmail.com', $fellow->email);
        $this->assertEquals(1, $fellow->discipline_id);

        // 7. Verify sample admin authentication
        $admin = User::where('username', 'asm.seetha.test')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('seetha@akademisains.gov.my', $admin->email);
        $this->assertEquals('administrator', $admin->role);
    }
}
