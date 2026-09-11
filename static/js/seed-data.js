/**
 * Initial Seed Data for Browser-Hosted Engine (GitHub Pages Demo Mode)
 * Academy of Sciences Malaysia (ASM)
 */

const SEED_DATA = {
  exercise: {
    id: 1,
    exercise_name: "ASM Annual Fellow Assessment & Ranking Exercise 2026",
    description: "Official assessment and confidential ranking exercise for shortlisted scientific candidates across 8 disciplines for induction as Fellows of the Academy of Sciences Malaysia.",
    instructions: "Please review the candidate nomination dossier and assign a unique preference rank (1 = Highest Preference) to every candidate within each discipline. All candidates must be ranked before submitting.",
    status: "open",
    allow_resubmission: 0,
    tie_breaker_method: "rank1_count",
    start_datetime: "2026-09-01 09:00:00",
    end_datetime: "2026-09-30 18:00:00"
  },

  users: [
    { id: 1, email: "admin@asm.org.my", username: "admin", role: "admin", full_name: "Dr. Aminah binti Razak (ASM Admin)" },
    { id: 2, email: "voter1@asm.org.my", username: "voter1", role: "voting_user", full_name: "Academician Tan Sri Dr. Ahmad Ibrahim" },
    { id: 3, email: "voter2@asm.org.my", username: "voter2", role: "voting_user", full_name: "Professor Emerita Datuk Dr. Mazlan Othman" },
    { id: 4, email: "voter3@asm.org.my", username: "voter3", role: "voting_user", full_name: "Professor Dr. Wong Chee Kong" },
    { id: 5, email: "reviewer@asm.org.my", username: "reviewer", role: "reviewer", full_name: "Dato' Dr. Sharifah Maimunah (Reviewer)" }
  ],

  disciplines: [
    { id: 1, discipline_name: "Discipline 1: Biological, Agricultural & Environmental Sciences", description: "Encompasses plant and animal biology, biotechnology, environmental management, forestry, and sustainable agriculture.", display_order: 1, candidate_count: 4 },
    { id: 2, discipline_name: "Discipline 2: Chemical Sciences", description: "Covers organic, inorganic, analytical, physical chemistry, industrial chemistry, and advanced materials.", display_order: 2, candidate_count: 5 },
    { id: 3, discipline_name: "Discipline 3: Engineering Sciences", description: "Includes mechanical, electrical, civil, chemical, aeronautical, and systems engineering innovations.", display_order: 3, candidate_count: 3 },
    { id: 4, discipline_name: "Discipline 4: Information Technology & Computer Sciences", description: "Focuses on artificial intelligence, cybersecurity, software systems, data science, and telecommunications.", display_order: 4, candidate_count: 6 },
    { id: 5, discipline_name: "Discipline 5: Mathematics, Physics & Earth Sciences", description: "Covers theoretical and applied mathematics, astrophysics, geosciences, meteorology, and quantum technologies.", display_order: 5, candidate_count: 4 },
    { id: 6, discipline_name: "Discipline 6: Medical & Health Sciences", description: "Includes epidemiology, clinical medicine, pharmacology, biomedical sciences, public health, and immunology.", display_order: 6, candidate_count: 5 },
    { id: 7, discipline_name: "Discipline 7: Social Sciences & Humanities", description: "Encompasses science diplomacy, ethics, economics of innovation, science education, and technology policy.", display_order: 7, candidate_count: 3 },
    { id: 8, discipline_name: "Discipline 8: Science & Technology Development and Industry", description: "Focuses on commercialization, translational research, industrial R&D leadership, and technological innovation.", display_order: 8, candidate_count: 6 }
  ],

  categories: [
    { id: 1, category_name: "General Comment" },
    { id: 2, category_name: "Professional Background" },
    { id: 3, category_name: "Academic / Research Record" },
    { id: 4, category_name: "Leadership" },
    { id: 5, category_name: "Achievement" },
    { id: 6, category_name: "Conflict of Interest" },
    { id: 7, category_name: "Integrity / Reputation" },
    { id: 8, category_name: "Other Relevant Information" }
  ],

  info_pages: {
    guidelines: {
      title: "Candidate Ranking Exercise Guidelines",
      content: `## Academy of Sciences Malaysia — Candidate Assessment Guidelines

### Objective
This exercise facilitates the formal, rigorous, and confidential evaluation of shortlisted candidates for induction into the Fellowship of the Academy of Sciences Malaysia (ASM).

### Assessment Principles
1. **Excellence & Impact**: Assessment must be founded upon verifiable scientific merit, technological innovation, socioeconomic impact, and institutional leadership.
2. **Confidentiality**: All proceedings, candidate portfolios, individual reviewer rankings, and due diligence commentaries are strictly confidential under the Academy of Sciences Malaysia Act 1994.
3. **Impartiality & Objectivity**: Evaluators are required to declare any conflict of interest regarding candidates within their review portfolio.`
    },
    criteria: {
      title: "Fellowship Assessment Criteria",
      content: `## Detailed Evaluation Criteria

1. **Originality and Scholarly Impact (30%)**
   * Landmark publications, high-impact citations, patents, and conceptual breakthroughs.
   * International peer recognition, keynote lectures, and research leadership.

2. **Translation, Socioeconomic and Industrial Significance (30%)**
   * Practical application of scientific discoveries toward national development.
   * Commercialization, technology transfer, or creation of enterprise.

3. **Human Capital Development & Leadership (20%)**
   * Mentorship of postgraduate researchers, early-career scientists, and research teams.
   * Leadership in academic departments, research institutes, or scientific societies.

4. **Professional Integrity & Public Service (20%)**
   * Contributions to national science advisory, policy formulation, and societal well-being.
   * Adherence to highest standards of research ethics and professional conduct.`
    },
    faq: {
      title: "Frequently Asked Questions (FAQ)",
      content: `## Frequently Asked Questions

**Q: Can I assign the same ranking to two candidates in the same discipline?**
A: No. The system strictly enforces ordinal uniqueness. If a discipline has 4 candidates, you must assign 1, 2, 3, and 4 exactly once.

**Q: Can I submit rankings for only some disciplines?**
A: You can save your progress as a draft at any time. However, final submission is only permitted once every candidate across all 8 disciplines has received a valid ranking.

**Q: Can I modify my submission after clicking 'Submit Ranking'?**
A: Once finalized, your ranking submission is cryptographically sealed and locked. In exceptional circumstances, an authorized Administrator may reopen your submission upon formal written request.`
    },
    confidentiality: {
      title: "Statutory Confidentiality Notice",
      content: `## Statutory Confidentiality Notice

All information contained within this system, including nominee dossiers, curriculum vitae, expert evaluations, due diligence statements, and individual ranking ballots, constitutes strictly confidential institutional data.

Unauthorized disclosure, duplication, distribution, or copying of any record is strictly prohibited and subject to institutional sanctions and legal penalties under Malaysian legislation.`
    }
  }
};
