<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Models\DueDiligenceCategory;
use App\Models\RankingExercise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed 8 Disciplines
        $disciplinesData = [
            ['id' => 1, 'discipline_name' => 'BAES - Biological Agriculture and Environmental Sciences', 'display_order' => 1],
            ['id' => 2, 'discipline_name' => 'CS - Chemical Sciences', 'display_order' => 2],
            ['id' => 3, 'discipline_name' => 'ES - Engineering Sciences', 'display_order' => 3],
            ['id' => 4, 'discipline_name' => 'ITCS - Information Technology and Computer Sciences', 'display_order' => 4],
            ['id' => 5, 'discipline_name' => 'MHS - Medical and Health Sciences', 'display_order' => 5],
            ['id' => 6, 'discipline_name' => 'MPES - Mathematical and Physical Sciences', 'display_order' => 6],
            ['id' => 7, 'discipline_name' => 'STDI - Science and Technology Development and Industry', 'display_order' => 7],
            ['id' => 8, 'discipline_name' => 'SSH - Social Sciences and Humanities', 'display_order' => 8],
        ];

        foreach ($disciplinesData as $d) {
            Discipline::updateOrCreate(
                ['id' => $d['id']],
                [
                    'discipline_name' => $d['discipline_name'],
                    'description' => 'Shortlisted candidates under '.$d['discipline_name'],
                    'display_order' => $d['display_order'],
                    'active' => true,
                ]
            );
        }

        // 2. Seed Due Diligence Categories
        $categories = [
            'General Comment',
            'Professional Background',
            'Academic / Research Record',
            'Leadership',
            'Achievement',
            'Conflict of Interest',
            'Integrity / Reputation',
            'Other Relevant Information',
        ];

        foreach ($categories as $index => $categoryName) {
            DueDiligenceCategory::updateOrCreate(
                ['name' => $categoryName],
                [
                    'description' => 'Due diligence assessment regarding '.$categoryName,
                    'display_order' => $index + 1,
                    'active' => true,
                ]
            );
        }

        // 3. Seed Default Open Ranking Exercise
        $exercise = RankingExercise::updateOrCreate(
            ['exercise_name' => 'ASM Candidate Ranking Exercise 2026'],
            [
                'description' => 'Confidential annual ranking and due diligence exercise for shortlisted candidates across all 8 disciplines.',
                'start_datetime' => now()->subDays(7),
                'end_datetime' => now()->addDays(30),
                'status' => 'Open',
                'allow_resubmission' => false,
            ]
        );

        // 4. Import Users from Excel
        if (file_exists(storage_path('app/User data for system.xlsx')) || file_exists(storage_path('app/User_data_for_system.xlsx'))) {
            Artisan::call('users:import-excel');
        }

        // 5. Seed Candidates per Discipline
        // BAES: 4, CS: 5, ES: 3, ITCS: 6, MHS: 5, MPES: 4, STDI: 3, SSH: 6
        $candidatesPerDiscipline = [
            1 => [
                ['name' => 'Candidate Alpha', 'title' => 'Professor of Molecular Biology', 'org' => 'National University of Life Sciences', 'exp' => 'Plant Genomics, Sustainable Crop Genetics'],
                ['name' => 'Candidate Bravo', 'title' => 'Distinguished Senior Fellow', 'org' => 'Institute for Tropical Forestry Research', 'exp' => 'Rainforest Conservation, Biodiversity Indices'],
                ['name' => 'Candidate Charlie', 'title' => 'Chief Research Scientist', 'org' => 'Maritime Marine Ecology Centre', 'exp' => 'Coral Ecosystems, Marine Microbiome'],
                ['name' => 'Candidate Delta', 'title' => 'Principal Investigator', 'org' => 'Academy Agricultural Innovation Lab', 'exp' => 'Bio-fertilizers, Soil Microbial Dynamics'],
            ],
            2 => [
                ['name' => 'Candidate Echo', 'title' => 'Professor of Organic Chemistry', 'org' => 'Federal Chemistry Institute', 'exp' => 'Catalytic Synthesis, Green Solvents'],
                ['name' => 'Candidate Foxtrot', 'title' => 'Head of Polymer Sciences', 'org' => 'Advanced Materials Research Centre', 'exp' => 'Biodegradable Polymers, Nanocomposites'],
                ['name' => 'Candidate Golf', 'title' => 'Senior Research Chemist', 'org' => 'Renewable Energy Chemistry Lab', 'exp' => 'Electrochemical Storage, Flow Batteries'],
                ['name' => 'Candidate Hotel', 'title' => 'Professor of Analytical Chemistry', 'org' => 'State University of Technology', 'exp' => 'Spectroscopy, Trace Contaminant Detection'],
                ['name' => 'Candidate India', 'title' => 'Distinguished Research Chair', 'org' => 'Institute of Natural Products Synthesis', 'exp' => 'Phytochemical Isolation, Bioactive Compounds'],
            ],
            3 => [
                ['name' => 'Candidate Juliet', 'title' => 'Professor of Structural Engineering', 'org' => 'National Engineering University', 'exp' => 'Earthquake Resilient Structures, Smart Concrete'],
                ['name' => 'Candidate Kilo', 'title' => 'Director of Aerospace Systems', 'org' => 'Aero-Propulsion Technologies Lab', 'exp' => 'Autonomous UAV Guidance, Aerodynamics'],
                ['name' => 'Candidate Lima', 'title' => 'Chair of Renewable Power Systems', 'org' => 'Sustainable Energy Engineering Centre', 'exp' => 'Microgrid Stability, High-Voltage Direct Current'],
            ],
            4 => [
                ['name' => 'Candidate Mike', 'title' => 'Professor of Artificial Intelligence', 'org' => 'Institute for Autonomous Systems', 'exp' => 'Reinforcement Learning, Foundation Models'],
                ['name' => 'Candidate November', 'title' => 'Chair of Cybersecurity', 'org' => 'National Cyber Defense Academy', 'exp' => 'Post-Quantum Cryptography, Protocol Verification'],
                ['name' => 'Candidate Oscar', 'title' => 'Director of Data Science', 'org' => 'Centre for High-Performance Computing', 'exp' => 'Distributed Graph Processing, Parallel Algorithms'],
                ['name' => 'Candidate Papa', 'title' => 'Associate Professor of Computer Vision', 'org' => 'Robotics & Vision Institute', 'exp' => '3D Scene Reconstruction, Neural Radiance Fields'],
                ['name' => 'Candidate Quebec', 'title' => 'Senior Fellow in Software Engineering', 'org' => 'Formal Methods Research Lab', 'exp' => 'Automated Theorem Proving, Dependable Systems'],
                ['name' => 'Candidate Romeo', 'title' => 'Chair of Quantum Computing', 'org' => 'National Quantum Technologies Hub', 'exp' => 'Quantum Error Correction, Quantum Annealing'],
            ],
            5 => [
                ['name' => 'Candidate Whiskey', 'title' => 'Professor of Immunology', 'org' => 'National Medical Research Centre', 'exp' => 'mRNA Vaccine Adjuvants, T-cell Exhaustion'],
                ['name' => 'Candidate X-ray', 'title' => 'Director of Oncology Therapeutics', 'org' => 'Cancer Precision Medicine Centre', 'exp' => 'Targeted Kinase Inhibitors, Biomarker Discovery'],
                ['name' => 'Candidate Yankee', 'title' => 'Chair of Epidemiology & Public Health', 'org' => 'Institute for Infectious Diseases', 'exp' => 'Outbreak Transmission Modelling, Pandemic Preparedness'],
                ['name' => 'Candidate Zulu', 'title' => 'Senior Consultant Neuroscientist', 'org' => 'Brain Sciences Research Hospital', 'exp' => 'Neurodegenerative Disease Mechanisms, Synaptic Plasticity'],
                ['name' => 'Candidate Amber', 'title' => 'Professor of Genomic Medicine', 'org' => 'Clinical Genomics Institute', 'exp' => 'Rare Genetic Variants, CRISPR Gene Editing Therapeutics'],
            ],
            6 => [
                ['name' => 'Candidate Sierra', 'title' => 'Professor of Theoretical Physics', 'org' => 'Institute for High Energy Physics', 'exp' => 'String Phenomenology, Dark Matter Signatures'],
                ['name' => 'Candidate Tango', 'title' => 'Distinguished Chair in Applied Mathematics', 'org' => 'Computational Mathematics Centre', 'exp' => 'Nonlinear Differential Equations, Fluid Solvers'],
                ['name' => 'Candidate Uniform', 'title' => 'Senior Fellow in Geophysics', 'org' => 'Earth & Atmospheric Research Lab', 'exp' => 'Seismic Tomography, Mantle Convection'],
                ['name' => 'Candidate Victor', 'title' => 'Professor of Astrophysics', 'org' => 'Deep Space Astronomical Observatory', 'exp' => 'Gravitational Waves, Exoplanet Atmospheres'],
            ],
            7 => [
                ['name' => 'Candidate Cobalt', 'title' => 'Chief Technology Officer & Senior Fellow', 'org' => 'National Industrial Innovation Council', 'exp' => 'Technology Transfer, Industry 4.0 Integration'],
                ['name' => 'Candidate Copper', 'title' => 'Vice President of Research Commercialisation', 'org' => 'Advanced Tech Ventures Hub', 'exp' => 'Deep-Tech Commercialisation, Intellectual Property Strategy'],
                ['name' => 'Candidate Diamond', 'title' => 'Director of Sustainable Manufacturing', 'org' => 'Green Industry Standards Institute', 'exp' => 'Circular Economy Systems, Industrial Decarbonisation'],
            ],
            8 => [
                ['name' => 'Candidate Emerald', 'title' => 'Professor of Science Policy', 'org' => 'Institute for Technology Governance', 'exp' => 'National STI Policy, Ethics of Emerging Tech'],
                ['name' => 'Candidate Garnet', 'title' => 'Distinguished Fellow in Economics', 'org' => 'Centre for Innovation Economics', 'exp' => 'R&D Productivity Metrics, Knowledge Economies'],
                ['name' => 'Candidate Jade', 'title' => 'Professor of Social Anthropology', 'org' => 'Institute of Malaysian Cultural Studies', 'exp' => 'Indigenous Knowledge Systems, Community Resilience'],
                ['name' => 'Candidate Opal', 'title' => 'Chair of Cognitive Psychology', 'org' => 'Behavioural Insights Laboratory', 'exp' => 'Human-AI Collaboration, Decision Making Under Risk'],
                ['name' => 'Candidate Ruby', 'title' => 'Director of Public Governance', 'org' => 'School of Public Policy & Administration', 'exp' => 'Institutional Trust, Regulatory Science Frameworks'],
                ['name' => 'Candidate Sapphire', 'title' => 'Professor of Digital Humanities', 'org' => 'National Heritage Informatics Centre', 'exp' => 'Digital Archives, Computational Cultural Heritage'],
            ],
        ];

        foreach ($candidatesPerDiscipline as $disciplineId => $candidates) {
            foreach ($candidates as $order => $c) {
                Candidate::updateOrCreate(
                    [
                        'discipline_id' => $disciplineId,
                        'candidate_name' => $c['name'],
                    ],
                    [
                        'candidate_title' => $c['title'],
                        'organisation' => $c['org'],
                        'photo_url' => 'https://ui-avatars.com/api/?name='.urlencode($c['name']).'&background=0D9488&color=fff&size=256',
                        'basis_of_recommendation' => 'Nominated based on exemplary national and international scientific contributions, recognized research publications, and sustained leadership in '.$c['exp'].'.',
                        'area_of_expertise' => $c['exp'],
                        'qualifications' => 'PhD in relevant field; Fellow of National and International Professional Institutes; Over 20 years of research and leadership excellence.',
                        'professional_memberships' => 'Academy Fellow Nominee; Senior Member of Professional Councils; Advisory Committee Member for National Research Grants.',
                        'short_description' => $c['name'].' currently serves as '.$c['title'].' at '.$c['org'].'. Their seminal contributions have advanced the frontiers of '.$c['exp'].' and delivered substantial socio-economic impact.',
                        'nomination_form_url' => 'https://onedrive.live.com/?id=ASM_CONFIDENTIAL_NOMINATION_'.strtoupper(str_replace(' ', '_', $c['name'])).'_FORM',
                        'display_order' => $order + 1,
                        'active' => true,
                    ]
                );
            }
        }
    }
}
