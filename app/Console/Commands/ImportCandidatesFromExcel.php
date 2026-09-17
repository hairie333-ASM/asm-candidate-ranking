<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\Discipline;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ImportCandidatesFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candidates:import-excel {--folder= : Custom folder path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import real shortlisted candidate data from Excel files into the application';

    /**
     * Discipline master list mapping.
     *
     * @var array<string, int>
     */
    protected array $disciplineCodes = [
        1 => 'BAES',
        2 => 'CS',
        3 => 'ES',
        4 => 'ITCS',
        5 => 'MHS',
        6 => 'MPES',
        7 => 'STDI',
        8 => 'SSH',
    ];

    public function __construct(protected AuditService $auditService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $folder = $this->option('folder') ?: '/Users/asm/Downloads/Upload candidate to system';

        if (! is_dir($folder)) {
            $this->error("Directory not found: {$folder}");

            return self::FAILURE;
        }

        $files = glob($folder.'/*.xlsx');
        sort($files);

        if (empty($files)) {
            $this->error("No Excel files found in {$folder}");

            return self::FAILURE;
        }

        $this->info('Starting candidate import from '.count($files).' Excel file(s)...');

        // Target photo directories
        $storagePhotosDir = storage_path('app/public/photos');
        $publicPhotosDir = public_path('photos');
        File::ensureDirectoryExists($storagePhotosDir);
        File::ensureDirectoryExists($publicPhotosDir);

        $parsedCandidates = [];
        $filesProcessed = 0;
        $cleaningNeededCount = 0;

        foreach ($files as $fpath) {
            $fname = basename($fpath);
            $this->line("Processing file: <comment>{$fname}</comment>");

            $zip = new ZipArchive;
            if ($zip->open($fpath) !== true) {
                $this->warn("Could not open zip archive: {$fname}");

                continue;
            }

            $filesProcessed++;

            // 1. Read drawing anchors to map row index to embedded media image
            $rowToMedia = $this->extractDrawingAnchors($zip);

            // 2. Read sheet1.xml (Candidates sheet)
            $candidatesFromSheet = $this->extractCandidatesFromSheet($zip, $fname, $rowToMedia, $cleaningNeededCount);

            foreach ($candidatesFromSheet as $cand) {
                // Extract photo image data from zip if available
                if (! empty($cand['picture_media'])) {
                    $imageData = $zip->getFromName($cand['picture_media']);
                    if ($imageData) {
                        $ext = pathinfo($cand['picture_media'], PATHINFO_EXTENSION) ?: 'jpeg';
                        $slug = Str::slug($cand['full_name']);
                        $filename = $slug.'.'.$ext;

                        file_put_contents($storagePhotosDir.'/'.$filename, $imageData);
                        file_put_contents($publicPhotosDir.'/'.$filename, $imageData);

                        $cand['photo_url'] = '/storage/photos/'.$filename;
                    }
                }

                $parsedCandidates[] = $cand;
            }

            $zip->close();
        }

        $candidatesFound = count($parsedCandidates);
        $this->info("Total candidates parsed from Excel: {$candidatesFound}");

        // 3. Identify and remove dummy/test candidates
        $dummyCount = 0;
        $existingCandidates = Candidate::all();

        foreach ($existingCandidates as $existing) {
            $name = $existing->candidate_name;
            // Check if name indicates dummy/test candidate
            if (
                preg_match('/^Candidate\s+(Alpha|Bravo|Charlie|Delta|Echo|Foxtrot|Golf|Hotel|India|Juliet|Kilo|Lima|Mike|November|Oscar|Papa|Quebec|Romeo|Sierra|Tango|Uniform|Victor|Whiskey|X-ray|Yankee|Zulu|Amber|Cobalt|Copper|Diamond|Emerald|Garnet|Jade|Opal|Ruby|Sapphire)/i', $name) ||
                preg_match('/^(Dummy|Test|Sample)\s+Candidate/i', $name) ||
                in_array(strtolower(trim($name)), ['dummy candidate', 'test candidate', 'sample candidate', 'john doe', 'jane doe'])
            ) {
                $existing->delete();
                $dummyCount++;
            }
        }

        $this->info("Removed {$dummyCount} dummy/test candidate records.");

        // 4. Import real candidates
        $importedCount = 0;
        $skippedCount = 0;
        $byDiscipline = array_fill_keys(array_values($this->disciplineCodes), 0);
        $disciplineCounters = array_fill_keys(array_keys($this->disciplineCodes), 0);

        DB::beginTransaction();

        try {
            foreach ($parsedCandidates as $c) {
                $discId = $c['discipline_id'];

                if (! $discId || ! isset($this->disciplineCodes[$discId])) {
                    $this->warn("Skipping candidate '{$c['full_name']}' - unknown discipline '{$c['raw_discipline']}'");

                    continue;
                }

                // Check for duplicates
                $alreadyExists = Candidate::where('candidate_name', $c['full_name'])
                    ->where('discipline_id', $discId)
                    ->exists();

                if ($alreadyExists) {
                    $this->line("Skipping duplicate candidate: <comment>{$c['full_name']}</comment>");
                    $skippedCount++;

                    continue;
                }

                $disciplineCounters[$discId]++;
                $displayOrder = $disciplineCounters[$discId];

                // Extract organisation
                $title = $c['title_designation'];
                $organisation = null;
                if ($title) {
                    $parts = explode(',', $title);
                    $organisation = trim(end($parts));
                }

                $shortDesc = null;
                if (! empty($c['basis_of_recommendation'])) {
                    $shortDesc = Str::limit($c['basis_of_recommendation'], 350);
                }

                $candidate = Candidate::create([
                    'discipline_id' => $discId,
                    'candidate_name' => $c['full_name'],
                    'candidate_title' => $c['title_designation'] ?: null,
                    'organisation' => $organisation ?: null,
                    'photo_url' => $c['photo_url'] ?? null,
                    'affiliation_to_asm' => $c['affiliation_to_asm'] ?: null,
                    'nomination_form_url' => $c['onedrive_dossier_link'] ?: null,
                    'area_of_expertise' => $c['areas_of_expertise'] ?: null,
                    'qualifications_professional_memberships' => $c['qualifications_professional_memberships'] ?: null,
                    'qualifications' => $c['qualifications_professional_memberships'] ?: null,
                    'professional_memberships' => null,
                    'basis_of_recommendation' => $c['basis_of_recommendation'] ?: null,
                    'short_description' => $shortDesc,
                    'display_order' => $displayOrder,
                    'active' => true,
                ]);

                $code = $this->disciplineCodes[$discId];
                $byDiscipline[$code]++;
                $importedCount++;

                $this->auditService->log(
                    action: 'Candidate Imported',
                    recordType: 'Candidate',
                    recordId: $candidate->id,
                    description: "Imported real shortlisted candidate '{$candidate->candidate_name}' under Discipline {$code} from {$c['source_file']}"
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed during candidate import transaction: '.$e->getMessage());

            return self::FAILURE;
        }

        // 5. Output Data Import Summary
        $this->newLine();
        $this->info('==================================================');
        $this->info('DATA IMPORT SUMMARY');
        $this->info('==================================================');
        $this->line("Number of Excel files processed      : <info>{$filesProcessed}</info>");
        $this->line("Number of candidates found            : <info>{$candidatesFound}</info>");
        $this->line("Number of candidates imported         : <info>{$importedCount}</info>");
        $this->line("Number of dummy/test candidates removed: <info>{$dummyCount}</info>");
        $this->line("Number of duplicate candidates skipped: <info>{$skippedCount}</info>");
        $this->line("Number of records requiring data cleaning: <info>{$cleaningNeededCount}</info>");
        $this->newLine();
        $this->info('Number of candidates by discipline:');

        foreach ($this->disciplineCodes as $id => $code) {
            $count = $byDiscipline[$code] ?? 0;
            $disc = Discipline::find($id);
            $fullName = $disc?->discipline_name ?? $code;
            $this->line(sprintf('  %-6s : %2d  (%s)', $code, $count, $fullName));
        }

        $this->info('==================================================');

        return self::SUCCESS;
    }

    /**
     * Extract drawing anchors from xl/drawings/drawing1.xml.
     * Maps 0-indexed row number to media path (e.g. xl/media/image1.png).
     */
    protected function extractDrawingAnchors(ZipArchive $zip): array
    {
        $rowToMedia = [];

        $relsXmlStr = $zip->getFromName('xl/drawings/_rels/drawing1.xml.rels');
        $dXmlStr = $zip->getFromName('xl/drawings/drawing1.xml');

        if (! $relsXmlStr || ! $dXmlStr) {
            return $rowToMedia;
        }

        $relMap = [];
        $relsXml = @simplexml_load_string($relsXmlStr);
        if ($relsXml) {
            foreach ($relsXml->Relationship as $rel) {
                $target = ltrim((string) $rel['Target'], '/');
                if (! str_starts_with($target, 'xl/')) {
                    $target = 'xl/'.str_replace('../', '', $target);
                }
                $relMap[(string) $rel['Id']] = $target;
            }
        }

        $dXml = @simplexml_load_string($dXmlStr);
        if ($dXml) {
            $ns = $dXml->getNamespaces(true);
            $xdrNs = $ns['xdr'] ?? 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing';
            $aNs = $ns['a'] ?? 'http://schemas.openxmlformats.org/drawingml/2006/main';
            $rNs = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

            $dXml->registerXPathNamespace('xdr', $xdrNs);
            $anchors = $dXml->xpath('//xdr:oneCellAnchor | //xdr:twoCellAnchor');

            if ($anchors) {
                foreach ($anchors as $anchor) {
                    $anchor->registerXPathNamespace('xdr', $xdrNs);
                    $anchor->registerXPathNamespace('a', $aNs);

                    $rowElements = $anchor->xpath('xdr:from/xdr:row');
                    $blipElements = $anchor->xpath('.//a:blip');

                    if (! empty($rowElements) && ! empty($blipElements)) {
                        $rowIdx = (int) $rowElements[0];
                        $blip = $blipElements[0];
                        $embedId = (string) $blip->attributes($rNs)['embed'];

                        if (isset($relMap[$embedId])) {
                            $rowToMedia[$rowIdx] = $relMap[$embedId];
                        }
                    }
                }
            }
        }

        return $rowToMedia;
    }

    /**
     * Extract candidate records from sheet1.xml.
     */
    protected function extractCandidatesFromSheet(ZipArchive $zip, string $sourceFile, array $rowToMedia, int &$cleaningNeededCount): array
    {
        $candidates = [];

        $sheetXmlStr = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (! $sheetXmlStr) {
            return $candidates;
        }

        $xml = @simplexml_load_string($sheetXmlStr);
        if (! $xml || ! isset($xml->sheetData)) {
            return $candidates;
        }

        $rows = [];
        foreach ($xml->sheetData->row as $rowElem) {
            $rNum = (int) $rowElem['r'];
            $cells = [];

            foreach ($rowElem->c as $c) {
                $coord = (string) $c['r'];
                preg_match('/[A-Z]+/', $coord, $matches);
                $col = $matches[0] ?? '';

                $val = '';
                if (isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string) $c->v;
                }

                $cells[$col] = $val;
            }

            $rows[$rNum] = $cells;
        }

        // Row 1 is header. Process data rows starting from row 2
        foreach ($rows as $rNum => $rCells) {
            if ($rNum < 2) {
                continue;
            }

            $fullName = $rCells['C'] ?? '';
            if (empty(trim($fullName))) {
                continue;
            }

            // Detect if data cleaning / normalization was required
            $rawFullName = $rCells['C'] ?? '';
            $rawTitle = $rCells['D'] ?? '';
            $rawDiscipline = $rCells['E'] ?? '';
            $rawAffiliation = $rCells['F'] ?? '';
            $rawOneDrive = $rCells['G'] ?? '';
            $rawExpertise = $rCells['H'] ?? '';
            $rawQuals = $rCells['I'] ?? '';
            $rawBor = $rCells['J'] ?? '';

            $hadFormattingIssues = false;
            foreach ([$rawFullName, $rawTitle, $rawDiscipline, $rawAffiliation, $rawOneDrive, $rawExpertise, $rawQuals, $rawBor] as $fieldVal) {
                if ($fieldVal !== trim($fieldVal) || str_contains($fieldVal, "\r\n") || preg_match('/[ \t]{2,}/', $fieldVal)) {
                    $hadFormattingIssues = true;
                    break;
                }
            }

            $disciplineId = $this->mapDiscipline($rawDiscipline);
            if ($hadFormattingIssues || ! empty($rawDiscipline)) {
                // Discipline mapping and whitespace normalization counts towards cleaned records
                $cleaningNeededCount++;
            }

            // Candidate picture media from drawing anchors (0-indexed row number = rNum - 1)
            $pictureMedia = $rowToMedia[$rNum - 1] ?? null;

            $cleanName = trim(preg_replace('/[ \t]+/', ' ', $rawFullName));
            $cleanTitle = trim(preg_replace('/[ \t]+/', ' ', str_replace("\r\n", "\n", $rawTitle)));
            $cleanAffiliation = trim(preg_replace('/[ \t]+/', ' ', str_replace("\r\n", "\n", $rawAffiliation)));
            $cleanOneDrive = trim($rawOneDrive);
            $cleanExpertise = trim(str_replace("\r\n", "\n", $rawExpertise));
            $cleanQuals = trim(str_replace("\r\n", "\n", $rawQuals));
            $cleanBor = trim(str_replace("\r\n", "\n", $rawBor));

            $candidates[] = [
                'source_file' => $sourceFile,
                'excel_row' => $rNum,
                'no' => trim($rCells['A'] ?? ''),
                'full_name' => $cleanName,
                'title_designation' => $cleanTitle,
                'raw_discipline' => $rawDiscipline,
                'discipline_id' => $disciplineId,
                'affiliation_to_asm' => $cleanAffiliation,
                'onedrive_dossier_link' => $cleanOneDrive,
                'areas_of_expertise' => $cleanExpertise,
                'qualifications_professional_memberships' => $cleanQuals,
                'basis_of_recommendation' => $cleanBor,
                'picture_media' => $pictureMedia,
            ];
        }

        return $candidates;
    }

    /**
     * Map raw discipline string to standardized discipline ID (1 to 8).
     */
    protected function mapDiscipline(string $rawDiscipline): ?int
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $rawDiscipline)));

        if (str_contains($normalized, 'BIOLOGICAL') || str_contains($normalized, 'BAES')) {
            return 1; // BAES
        }
        if (str_contains($normalized, 'CHEMICAL') || $normalized === 'CS') {
            return 2; // CS
        }
        if (str_contains($normalized, 'ENGINEERING') || $normalized === 'ES') {
            return 3; // ES
        }
        if (str_contains($normalized, 'INFORMATION TECHNOLOGY') || str_contains($normalized, 'COMPUTER') || str_contains($normalized, 'ITCS')) {
            return 4; // ITCS
        }
        if (str_contains($normalized, 'MEDICAL') || str_contains($normalized, 'HEALTH') || str_contains($normalized, 'MHS')) {
            return 5; // MHS
        }
        if (str_contains($normalized, 'MATHEMATIC') || str_contains($normalized, 'PHYSICAL') || str_contains($normalized, 'PHYSICS') || str_contains($normalized, 'MPES')) {
            return 6; // MPES
        }
        if (str_contains($normalized, 'DEVELOPMENT AND INDUSTRY') || str_contains($normalized, 'STDI')) {
            return 7; // STDI
        }
        if (str_contains($normalized, 'SOCIAL') || str_contains($normalized, 'HUMANITIES') || str_contains($normalized, 'SSH')) {
            return 8; // SSH
        }

        return null;
    }
}
