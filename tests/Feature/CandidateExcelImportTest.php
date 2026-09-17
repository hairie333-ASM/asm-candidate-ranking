<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Discipline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CandidateExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed 8 official disciplines
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
    }

    public function test_import_candidates_command_imports_all_real_candidates(): void
    {
        // Create dummy candidate before import
        Candidate::create([
            'discipline_id' => 1,
            'candidate_name' => 'Candidate Alpha',
            'candidate_title' => 'Dummy Title',
            'active' => true,
        ]);

        $this->assertEquals(1, Candidate::where('candidate_name', 'Candidate Alpha')->count());

        $folder = '/Users/asm/Downloads/Upload candidate to system';
        if (! is_dir($folder) || empty(glob("$folder/*.xlsx"))) {
            $this->markTestSkipped("Excel import folder {$folder} is not accessible in this execution environment.");
        }

        // Run the import command via Artisan::call
        $exitCode = Artisan::call('candidates:import-excel', [
            '--folder' => $folder,
        ]);

        $this->assertEquals(0, $exitCode);

        // Dummy candidate should be removed
        $this->assertEquals(0, Candidate::where('candidate_name', 'Candidate Alpha')->count());

        // Exactly 30 real candidates should be imported
        $this->assertEquals(30, Candidate::count());

        // Check discipline distribution
        $this->assertEquals(6, Candidate::where('discipline_id', 1)->count()); // BAES
        $this->assertEquals(1, Candidate::where('discipline_id', 2)->count()); // CS
        $this->assertEquals(10, Candidate::where('discipline_id', 3)->count()); // ES
        $this->assertEquals(2, Candidate::where('discipline_id', 4)->count()); // ITCS
        $this->assertEquals(2, Candidate::where('discipline_id', 5)->count()); // MHS
        $this->assertEquals(1, Candidate::where('discipline_id', 6)->count()); // MPES
        $this->assertEquals(2, Candidate::where('discipline_id', 7)->count()); // STDI
        $this->assertEquals(6, Candidate::where('discipline_id', 8)->count()); // SSH

        // Verify specific candidate details
        $hafizal = Candidate::where('candidate_name', 'Professor Ir Dr Hafizal Mohamad')->first();
        $this->assertNotNull($hafizal);
        $this->assertEquals(4, $hafizal->discipline_id);
        $this->assertStringContainsString('Universiti Sains Islam Malaysia', $hafizal->candidate_title);
        $this->assertStringContainsString('YSN-ASM', $hafizal->affiliation_to_asm);
        $this->assertNotEmpty($hafizal->area_of_expertise);
        $this->assertNotEmpty($hafizal->qualifications_professional_memberships);
        $this->assertNotEmpty($hafizal->basis_of_recommendation);
        $this->assertStringStartsWith('/storage/photos/', $hafizal->photo_url);
    }
}
