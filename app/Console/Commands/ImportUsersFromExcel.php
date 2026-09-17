<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

#[Signature('users:import-excel {--file= : Path to the User data for system.xlsx file}')]
#[Description('Import users from Excel, remove dummy users, and configure voting/admin roles')]
class ImportUsersFromExcel extends Command
{
    /**
     * Map Excel discipline names to system discipline IDs.
     */
    protected array $disciplineMap = [
        'BAES - Biological Agriculture and Environmental Sciences' => 1,
        'Biological Agriculture and Environmental Sciences' => 1,
        'Biological, Agricultural and Environmental Sciences' => 1,
        'Biological, Agricultural & Environmental Sciences' => 1,
        'CS - Chemical Sciences' => 2,
        'Chemical Sciences' => 2,
        'ES - Engineering Sciences' => 3,
        'Engineering Sciences' => 3,
        'ITCS - Information Technology and Computer Sciences' => 4,
        'Information Technology and Computer Sciences' => 4,
        'Information Technology & Computer Sciences' => 4,
        'MHS - Medical and Health Sciences' => 5,
        'Medical and Health Sciences' => 5,
        'Medical & Health Sciences' => 5,
        'MPES - Mathematical and Physical Sciences' => 6,
        'Mathematical and Physical Sciences' => 6,
        'Mathematics, Physics & Earth Sciences' => 6,
        'Mathematics, Physics and Earth Sciences' => 6,
        'STDI - Science and Technology Development and Industry' => 7,
        'Science and Technology Development and Industry' => 7,
        'Science & Technology Development and Industry' => 7,
        'SSH - Social Sciences and Humanities' => 8,
        'Social Sciences and Humanities' => 8,
        'Social Sciences & Humanities' => 8,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->option('file');

        if (! $filePath) {
            $candidates = [
                storage_path('app/User data for system.xlsx'),
                storage_path('app/User_data_for_system.xlsx'),
                base_path('User data for system.xlsx'),
                base_path('User_data_for_system.xlsx'),
                '/Users/asm/Downloads/User data for system.xlsx',
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    $filePath = $candidate;
                    break;
                }
            }
        }

        if (! $filePath || ! file_exists($filePath)) {
            $this->error('Excel file not found at: '.($filePath ?: storage_path('app/User data for system.xlsx')));

            return Command::FAILURE;
        }

        $this->info("Opening Excel workbook: {$filePath}");

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            $this->error('Failed to extract Excel workbook as zip archive.');

            return Command::FAILURE;
        }

        // 1. Read shared strings table
        $sharedStrings = [];
        if (($ssXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($ssXml);
            foreach ($xml->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        // 2. Read workbook sheets
        $sheetMap = [];
        if (($wbXml = $zip->getFromName('xl/workbook.xml')) !== false) {
            $xml = simplexml_load_string($wbXml);
            foreach ($xml->sheets->sheet as $sheet) {
                $name = (string) $sheet['name'];
                $rId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                $sheetMap[$name] = $rId;
            }
        }

        // 3. Read relationships
        $targetMap = [];
        if (($relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels')) !== false) {
            $xml = simplexml_load_string($relsXml);
            foreach ($xml->Relationship as $rel) {
                $id = (string) $rel['Id'];
                $target = (string) $rel['Target'];
                $targetMap[$id] = $target;
            }
        }

        // 4. Remove all previous dummy users
        $this->info('Removing previous dummy and sample users...');
        $deletedCount = User::where('email', 'like', '%@example.test')
            ->orWhereIn('email', [
                'admin@example.test',
                'voter1@example.test',
                'voter2@example.test',
                'reviewer@example.test',
            ])
            ->delete();

        $this->info("Deleted {$deletedCount} dummy users from database.");

        // 5. Parse Sheet 'Fellow 11032026'
        if (! isset($sheetMap['Fellow 11032026']) || ! isset($targetMap[$sheetMap['Fellow 11032026']])) {
            $this->error("Sheet 'Fellow 11032026' not found in workbook.");
            $zip->close();

            return Command::FAILURE;
        }

        $fellowRows = $this->parseSheet($zip, $targetMap[$sheetMap['Fellow 11032026']], $sharedStrings);
        $rejectedRecords = [];
        $importedFellows = 0;
        $disciplineCounts = array_fill(1, 8, 0);

        $this->info('Processing '.count($fellowRows)." rows from sheet 'Fellow 11032026'...");

        foreach ($fellowRows as $rowNum => $cells) {
            if ($rowNum === 1) {
                continue; // Skip header
            }

            $name = trim($cells['B'] ?? '');
            $login = trim($cells['C'] ?? '');
            $password = trim($cells['D'] ?? '');
            $email = trim($cells['F'] ?? '');
            $disciplineStr = trim($cells['G'] ?? '');

            // Skip empty rows
            if (empty($name) && empty($login) && empty($email)) {
                continue;
            }

            // Validation checks
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $login === 'no data' || empty($password)) {
                $reason = empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)
                    ? 'Invalid or error message in email field'
                    : ($login === 'no data' ? 'Missing login username (no data)' : 'Missing password');

                $rejectedRecords[] = [
                    'Sheet' => 'Fellow 11032026',
                    'Row' => $rowNum,
                    'Name' => $name,
                    'Login' => $login,
                    'Email' => $email,
                    'Discipline' => $disciplineStr,
                    'Reason' => $reason,
                ];

                continue;
            }

            $disciplineId = $this->disciplineMap[$disciplineStr] ?? null;
            if (! $disciplineId) {
                $rejectedRecords[] = [
                    'Sheet' => 'Fellow 11032026',
                    'Row' => $rowNum,
                    'Name' => $name,
                    'Login' => $login,
                    'Email' => $email,
                    'Discipline' => $disciplineStr,
                    'Reason' => "Unknown discipline: {$disciplineStr}",
                ];

                continue;
            }

            User::updateOrCreate(
                ['email' => strtolower($email)],
                [
                    'name' => $name,
                    'username' => $login,
                    'password' => Hash::make($password),
                    'role' => 'voting_user',
                    'discipline_id' => $disciplineId,
                    'active' => true,
                ]
            );

            $disciplineCounts[$disciplineId]++;
            $importedFellows++;
        }

        // 6. Parse Sheet 'TESTING' (Internal ASM Admins)
        $importedAdmins = 0;
        if (isset($sheetMap['TESTING']) && isset($targetMap[$sheetMap['TESTING']])) {
            $testingRows = $this->parseSheet($zip, $targetMap[$sheetMap['TESTING']], $sharedStrings);
            $this->info('Processing '.count($testingRows)." rows from sheet 'TESTING' (ASM Administrators)...");

            foreach ($testingRows as $rowNum => $cells) {
                $name = trim($cells['C'] ?? '');
                $login = trim($cells['D'] ?? '');
                $password = trim($cells['E'] ?? '');
                $email = trim($cells['G'] ?? '');

                if (($cells['A'] ?? '') === 'No' || $name === 'Name' || empty($name)) {
                    continue;
                }

                if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! empty($password)) {
                    User::updateOrCreate(
                        ['email' => strtolower($email)],
                        [
                            'name' => $name,
                            'username' => $login,
                            'password' => Hash::make($password),
                            'role' => 'administrator',
                            'discipline_id' => null,
                            'active' => true,
                        ]
                    );

                    $importedAdmins++;
                } else {
                    $rejectedRecords[] = [
                        'Sheet' => 'TESTING',
                        'Row' => $rowNum,
                        'Name' => $name,
                        'Login' => $login,
                        'Email' => $email,
                        'Discipline' => 'ASM Admin',
                        'Reason' => 'Invalid email or password',
                    ];
                }
            }
        }

        $zip->close();

        // 7. Output Results & Verification
        $this->newLine();
        $this->info('====================================================');
        $this->info(' USER DATA IMPORT SUMMARY');
        $this->info('====================================================');

        $this->table(
            ['Category', 'Count'],
            [
                ['Dummy Users Removed', $deletedCount],
                ['Valid Fellows Imported (voting_user)', $importedFellows],
                ['ASM Internal Staff Imported (administrator)', $importedAdmins],
                ['Total Users Now in System', User::count()],
                ['Rejected Records', count($rejectedRecords)],
            ]
        );

        $this->info('Discipline Breakdown (Fellows):');
        $disciplineTableData = [];
        $disciplineNames = [
            1 => 'BAES - Biological Agriculture and Environmental Sciences',
            2 => 'CS - Chemical Sciences',
            3 => 'ES - Engineering Sciences',
            4 => 'ITCS - Information Technology and Computer Sciences',
            5 => 'MHS - Medical and Health Sciences',
            6 => 'MPES - Mathematical and Physical Sciences',
            7 => 'STDI - Science and Technology Development and Industry',
            8 => 'SSH - Social Sciences and Humanities',
        ];

        foreach ($disciplineCounts as $id => $cnt) {
            $disciplineTableData[] = [
                'Discipline ID' => $id,
                'Discipline Name' => $disciplineNames[$id] ?? "Discipline {$id}",
                'Voters Count' => $cnt,
            ];
        }
        $this->table(['Discipline ID', 'Discipline Name', 'Voters Count'], $disciplineTableData);

        if (! empty($rejectedRecords)) {
            $this->warn('Rejected Records (Logged & Skipped):');
            $this->table(
                ['Sheet', 'Row', 'Name', 'Login', 'Email', 'Discipline', 'Reason'],
                $rejectedRecords
            );
        }

        $this->info('Import completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Parse XML sheet data into structured row/column array.
     */
    protected function parseSheet(ZipArchive $zip, string $target, array $sharedStrings): array
    {
        $path = (strpos($target, 'xl/') === 0 || strpos($target, '/') === 0) ? ltrim($target, '/') : 'xl/'.$target;
        $sheetXml = $zip->getFromName($path);
        if (! $sheetXml) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            $cells = [];
            foreach ($row->c as $c) {
                $cellRef = (string) $c['r'];
                preg_match('/[A-Z]+/', $cellRef, $matches);
                $col = $matches[0] ?? '';
                $type = (string) $c['t'];
                $val = '';
                if (isset($c->v)) {
                    $rawVal = (string) $c->v;
                    if ($type === 's' && isset($sharedStrings[(int) $rawVal])) {
                        $val = $sharedStrings[(int) $rawVal];
                    } else {
                        $val = $rawVal;
                    }
                } elseif (isset($c->is->t)) {
                    $val = (string) $c->is->t;
                }
                $cells[$col] = trim($val);
            }
            $rows[$rowNum] = $cells;
        }

        return $rows;
    }
}
