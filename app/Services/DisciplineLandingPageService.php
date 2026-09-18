<?php

namespace App\Services;

use App\Models\Discipline;

class DisciplineLandingPageService
{
    /**
     * Complete institutional landing page dossiers for all 8 Academy disciplines.
     * Sourced verbatim from official Academy communiqués in Download/Landing pages/*.docx.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $dossiers = [
        'ITCS' => [
            'code' => 'ITCS',
            'full_name' => 'Information Technology and Computer Sciences',
            'salutation' => 'Dear Fellows of the Information Technology and Computer Sciences (ITCS) Discipline Group,',
            'vetting_date' => '6 February 2026',
            'evaluated_count' => 2,
            'shortlisted_count' => 2,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The ITCS Discipline Vetting Committee convened on 6 February 2026 to evaluate and shortlist the nominations received. Proposers and/or seconders presented their nominees to the Committee. Two (2) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward both nominees for the ITCS Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                'Professor Ir Dr Hafizal Mohamad',
                'YM Raja Azrina Raja Othman',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the ITCS Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
        'BAES' => [
            'code' => 'BAES',
            'full_name' => 'Biological, Agricultural and Environmental Sciences',
            'salutation' => 'Dear Fellows of the Biological, Agricultural and Environmental Sciences (BAES) Discipline Group,',
            'vetting_date' => '5 February 2026',
            'evaluated_count' => 11,
            'shortlisted_count' => 6,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The BAES Discipline Vetting Committee convened on 5 February 2026 to evaluate and shortlist the nominations received. Proposers and/ or seconders presented their nominees to the Committee. Eleven (11) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the results of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward six (6) nominees for the BAES Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                'Professor Dr Ahmad Ainuddin Nuruddin',
                'Professor Dr Anjas Asmara @ Ab. Hadi Bin Samsudin',
                'Professor Ts Dr Chong Khim Phin',
                'Professor Ts Dr Hidayah Ariffin',
                'Professor Dr Jamal Hisham Hashim',
                'Professor Dr Sreeramanan Subramaniam',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the BAES Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
        'MPES' => [
            'code' => 'MPES',
            'full_name' => 'Mathematics, Physics and Earth Sciences',
            'salutation' => 'Dear Fellows of the Mathematics, Physics and Earth Sciences (MPES) Discipline Group,',
            'vetting_date' => '9 February 2026',
            'evaluated_count' => 2,
            'shortlisted_count' => 1,
            'is_single_candidate' => true,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The MPES Discipline Vetting Committee convened on 9 February 2026 to evaluate and shortlist the nominations received. Proposers and/ or seconders presented their nominees to the Committee. Two (2) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward one (1) nominee for the MPES Discipline Group.',
            'next_steps_paragraph' => 'Considering the unique circumstance of having only one recommended nominee this year, the typical next step in the selection process, which is online ranking by discipline group, is deemed unnecessary. Instead, the recommended nominee will proceed to the next level, which is consideration by ASM Council. If approved, the nominee will be presented for election at the upcoming 31st Annual General Meeting on 25 April 2026. For any inquiries or feedback regarding the nominee, Fellows are encouraged to use the online voting system.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'It is important to note that the shortlisted nominee, Professor Dr Yong Ken Tye, meets the merit and eligibility criteria for election as a Fellow within the MPES Discipline Group for the year 2026.',
            'shortlisted_nominees' => [
                'Professor Dr Yong Ken Tye',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the MPES Discipline Group to participate in this exercise.',
            'sign_off' => 'Thank you.',
        ],
        'CS' => [
            'code' => 'CS',
            'full_name' => 'Chemical Sciences',
            'salutation' => 'Dear Fellows of the Chemical Sciences (CS) Discipline Group,',
            'vetting_date' => '29 January 2026',
            'evaluated_count' => 2,
            'shortlisted_count' => 1,
            'is_single_candidate' => true,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The CS Discipline Vetting Committee convened on 29 January 2026 to evaluate and shortlist the nominations received. Proposers and/ or seconders presented their nominees to the Committee. Two (2) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the results of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward one (1) nominee for the CS Discipline Group.',
            'next_steps_paragraph' => 'Considering the unique circumstance of having only one recommended nominee this year, the typical next step in the selection process, which is online ranking by discipline group, is deemed unnecessary. Instead, the recommended nominee will proceed to the next level, which is consideration by ASM Council. If approved, the nominee will be presented for election at the upcoming 31st Annual General Meeting on 25 April 2026. For any inquiries or feedback regarding the nominee, Fellows are encouraged to use the online voting system.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'It is important to note that the shortlisted nominee, Professor ChM Juan Joon Ching, meets the merit and eligibility criteria for election as a Fellow within the CS Discipline Group for the year 2026.',
            'shortlisted_nominees' => [
                'Professor ChM Dr Juan Joon Ching',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the CS Discipline Group to participate in this exercise.',
            'sign_off' => 'Thank you.',
        ],
        'ES' => [
            'code' => 'ES',
            'full_name' => 'Engineering Sciences',
            'salutation' => 'Dear Fellows of the Engineering Sciences (ES) Discipline Group,',
            'vetting_date' => '10 and 11 February 2026',
            'evaluated_count' => 22,
            'shortlisted_count' => 10,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The ES Discipline Vetting Committee convened on 10 and 11 February 2026 to evaluate and shortlist the nominations received. Proposers and/or seconders presented their nominees to the Committee. Twenty-two (22) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the results of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward ten (10) nominees for the ES Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments / enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                "Dato' Professor Ir Dr Ahmad Farhan Mohd Sadullah",
                'Professor Ir Dr Ching Yern Chee',
                'Professor Ir Dr Faridah Othman',
                'Professor Ts Dr Mohamad Kamal A Rahim',
                'Professor Ir Ts Dr Mohamed Thariq Hameed Sultan',
                'Professor Dr Omar Yaakob',
                'Professor Ir Ts Dr Sharul Kamal Abdul Rahim',
                'Professor Dr Tang Tong Boon',
                'Professor Ir Ts Dr Tiong Sieh Kiong',
                'Professor Ir Dr Vigna Ramachandramurthy',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the ES Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
        'MHS' => [
            'code' => 'MHS',
            'full_name' => 'Medical and Health Sciences',
            'salutation' => 'Dear Fellows of the Medical and Health Sciences (MHS) Discipline Group,',
            'vetting_date' => '30 January 2026',
            'evaluated_count' => 2,
            'shortlisted_count' => 2,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The MHS Discipline Vetting Committee convened on 30 January 2026 to evaluate and shortlist the nominations received. Proposers and/ or seconders presented their nominees to the Committee. Two (2) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward both of the nominees for the MHS Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                'Professor Dr Gan Shiaw Sze @ Gan Gin Gin',
                "Dato' Dr Mohd Zaki Salleh",
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the MHS Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
        'SSH' => [
            'code' => 'SSH',
            'full_name' => 'Social Sciences and Humanities',
            'salutation' => 'Dear Fellows of the Social Sciences and Humanities (SSH) Discipline Group,',
            'vetting_date' => '4 February 2026',
            'evaluated_count' => 9,
            'shortlisted_count' => 6,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => 'The SSH Discipline Vetting Committee convened on 4 February 2026 to evaluate and shortlist the nominations received. Proposers and/ or seconders presented their nominees to the Committee. Nine (9) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward six (6) nominees for the SSH Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                'Ms Chee Yoke Ling',
                'Professor TPr Dr Goh Hong Ching',
                'Madam Hazami Habib',
                "Professor Dato' Dr Norzaini Azman",
                'Professor Dr Santha Vaithilingam',
                'Professor Dr Surinderpal Kaur Chanan Singh',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the SSH Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
        'STDI' => [
            'code' => 'STDI',
            'full_name' => 'Science & Technology Development and Industry',
            'salutation' => 'Dear Fellows of the Science & Technology Development and Industry (STDI) Discipline Group,',
            'vetting_date' => '9 February 2026',
            'evaluated_count' => 4,
            'shortlisted_count' => 2,
            'is_single_candidate' => false,
            'opening_time' => '0900 hours',
            'vetting_paragraph' => 'The STDI Discipline Vetting Committee convened on 9 February 2026 to evaluate and shortlist the nominations received. Proposers and/or seconders presented their nominees to the Committee. Four (4) nominees were evaluated based on the evaluation rubric and selection criteria.',
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026. Upon reviewing the results of the vetting exercise and deliberation, the Membership Committee agreed to put forward two (2) nominees for the STDI Discipline Group.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. The online ranking exercise will produce a list of shortlisted nominees in ranking order for Membership Committee to finalise the recommended nominees. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups. Suppose any of the shortlisted nominees are deemed inappropriate and do not fit the criteria of election as a Fellow, you may send your comments/ enquiries with supporting documents using the online ranking system. The Membership Committee will conduct due diligence on the respective nominee prior to recommending the nominees to ASM Council.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows. The nominees in alphabetical order are:',
            'shortlisted_nominees' => [
                'Tan Sri Dato’ Seri Professor Ir Ts Dr Abdahir Haji Abdul Majid JP',
                'Dr Ismail Hashim',
            ],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 0900 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time. Therefore, we invite Fellows of the STDI Discipline Group to participate in this exercise and to rank.',
            'sign_off' => 'Thank you.',
        ],
    ];

    /**
     * Retrieve landing page dossier for a given discipline model, code, or ID.
     *
     * @return array<string, mixed>
     */
    public function getForDiscipline(Discipline|string|int|null $discipline): array
    {
        $code = $this->resolveCode($discipline);

        if ($code && isset($this->dossiers[$code])) {
            return $this->dossiers[$code];
        }

        return $this->generateFallbackDossier($discipline);
    }

    /**
     * Resolve 2-5 letter code from discipline instance, string, or ID.
     */
    public function resolveCode(Discipline|string|int|null $discipline): ?string
    {
        if ($discipline instanceof Discipline) {
            $code = strtoupper(trim($discipline->code));
            if (isset($this->dossiers[$code])) {
                return $code;
            }

            return $this->resolveCode($discipline->discipline_name);
        }

        if (is_numeric($discipline)) {
            $model = Discipline::find((int) $discipline);

            return $model ? $this->resolveCode($model) : null;
        }

        if (is_string($discipline)) {
            $clean = strtoupper(trim($discipline));
            if (isset($this->dossiers[$clean])) {
                return $clean;
            }

            // Check parenthesized code e.g. "Social Sciences and Humanities (SSH)"
            if (preg_match('/\(([A-Z]{2,6})\)/', $clean, $m)) {
                if (isset($this->dossiers[$m[1]])) {
                    return $m[1];
                }
            }

            // Check prefix code e.g. "ITCS - Information Technology"
            if (preg_match('/^([A-Z]{2,6})\b/', $clean, $m)) {
                if (isset($this->dossiers[$m[1]])) {
                    return $m[1];
                }
            }

            // Check substring matching against known dossier keys
            foreach (array_keys($this->dossiers) as $key) {
                if (str_contains($clean, $key)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * Get all dossiers.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllDossiers(): array
    {
        return $this->dossiers;
    }

    /**
     * Generate dynamic fallback if custom discipline is passed.
     *
     * @return array<string, mixed>
     */
    protected function generateFallbackDossier(Discipline|string|int|null $discipline): array
    {
        $name = $discipline instanceof Discipline ? $discipline->discipline_name : (string) $discipline;
        $code = $discipline instanceof Discipline ? $discipline->code : 'GENERAL';

        return [
            'code' => $code,
            'full_name' => $name,
            'salutation' => "Dear Fellows of the {$name} Discipline Group,",
            'vetting_date' => 'February 2026',
            'evaluated_count' => 0,
            'shortlisted_count' => 0,
            'is_single_candidate' => false,
            'opening_time' => '1200 hours',
            'vetting_paragraph' => "The {$code} Discipline Vetting Committee convened in February 2026 to evaluate and shortlist nominations received based on the evaluation rubric and selection criteria.",
            'membership_paragraph' => 'Following this, the Chairs of the Discipline Vetting Committee presented the result of the vetting exercise at the Membership Committee Meeting which was held on 27 February and 3 March 2026.',
            'next_steps_paragraph' => 'The next step in the selection process is the online ranking by discipline. Your discretion to find the best among the best to become ASM Fellow is important. Upon approval by ASM Council, these nominees will be put forward for election at the 31st Annual General Meeting on 25 April 2026.',
            'due_diligence_paragraph' => 'While voting is restricted within the discipline, Fellows can view recommended nominees of other discipline groups and submit due diligence assessments with supporting documents using the online ranking system.',
            'nominees_intro' => 'Please be reminded, the shortlisted nominees have the merit and eligible for election as Fellows.',
            'shortlisted_nominees' => [],
            'voting_timeline_paragraph' => 'Online voting will be open from 18 March 2026 (Wednesday), 1200 hours until 26 March 2026 (Thursday), 2359 hours, Malaysian Time.',
            'sign_off' => 'Thank you.',
        ];
    }
}
