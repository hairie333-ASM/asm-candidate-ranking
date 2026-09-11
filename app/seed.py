import os
from app.database import get_db, init_db
from app.auth import hash_password

def seed_database():
    init_db()
    with get_db() as conn:
        cursor = conn.cursor()
        
        # 1. Seed Users
        users = [
            ("admin@asm.org.my", "admin", "Admin123!", "Dr. Aminah binti Razak (ASM Admin)", "admin"),
            ("voter1@asm.org.my", "voter1", "Voter123!", "Academician Tan Sri Dr. Ahmad Ibrahim", "voting_user"),
            ("voter2@asm.org.my", "voter2", "Voter123!", "Professor Emerita Datuk Dr. Mazlan Othman", "voting_user"),
            ("voter3@asm.org.my", "voter3", "Voter123!", "Professor Dr. Wong Chee Kong", "voting_user"),
            ("reviewer@asm.org.my", "reviewer", "Reviewer123!", "Dato' Dr. Sharifah Maimunah (Reviewer)", "reviewer"),
        ]
        
        for email, uname, pwd, name, role in users:
            cursor.execute(
                """
                INSERT OR IGNORE INTO users (email, username, password_hash, full_name, role, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
                """,
                (email, uname, hash_password(pwd), name, role)
            )
            
        # 2. Seed Exercise
        cursor.execute("SELECT COUNT(*) as cnt FROM ranking_exercises")
        if cursor.fetchone()["cnt"] == 0:
            cursor.execute(
                """
                INSERT INTO ranking_exercises (
                    exercise_name, description, instructions,
                    start_datetime, end_datetime, status, allow_resubmission, tie_breaker_method
                ) VALUES (
                    'ASM Annual Fellow Assessment & Ranking Exercise 2026',
                    'Official assessment and confidential ranking exercise for shortlisted scientific candidates across 8 disciplines for induction as Fellows of the Academy of Sciences Malaysia.',
                    'Please review the candidate nomination dossier and assign a unique preference rank (1 = Highest Preference) to every candidate within each discipline. All candidates must be ranked before submitting.',
                    '2026-09-01 09:00:00',
                    '2026-09-30 18:00:00',
                    'open',
                    0,
                    'rank1_count'
                )
                """
            )
            exercise_id = cursor.lastrowid
        else:
            cursor.execute("SELECT id FROM ranking_exercises LIMIT 1")
            exercise_id = cursor.fetchone()["id"]
            
        # 3. Seed 8 Disciplines
        disciplines_data = [
            (1, "Discipline 1: Biological, Agricultural & Environmental Sciences", "Encompasses plant and animal biology, biotechnology, environmental management, forestry, and sustainable agriculture.", 1),
            (2, "Discipline 2: Chemical Sciences", "Covers organic, inorganic, analytical, physical chemistry, industrial chemistry, and advanced materials.", 2),
            (3, "Discipline 3: Engineering Sciences", "Includes mechanical, electrical, civil, chemical, aeronautical, and systems engineering innovations.", 3),
            (4, "Discipline 4: Information Technology & Computer Sciences", "Focuses on artificial intelligence, cybersecurity, software systems, data science, and telecommunications.", 4),
            (5, "Discipline 5: Mathematics, Physics & Earth Sciences", "Covers theoretical and applied mathematics, astrophysics, geosciences, meteorology, and quantum technologies.", 5),
            (6, "Discipline 6: Medical & Health Sciences", "Includes epidemiology, clinical medicine, pharmacology, biomedical sciences, public health, and immunology.", 6),
            (7, "Discipline 7: Social Sciences & Humanities", "Encompasses science diplomacy, ethics, economics of innovation, science education, and technology policy.", 7),
            (8, "Discipline 8: Science & Technology Development and Industry", "Focuses on commercialization, translational research, industrial R&D leadership, and technological innovation.", 8),
        ]
        
        for d_id, d_name, d_desc, d_order in disciplines_data:
            cursor.execute(
                """
                INSERT OR IGNORE INTO disciplines (id, discipline_name, description, display_order, is_active)
                VALUES (?, ?, ?, ?, 1)
                """,
                (d_id, d_name, d_desc, d_order)
            )

        # 4. Seed Candidates across 8 disciplines:
        # Discipline 1: 4 candidates
        # Discipline 2: 5 candidates
        # Discipline 3: 3 candidates
        # Discipline 4: 6 candidates
        # Discipline 5: 4 candidates
        # Discipline 6: 5 candidates
        # Discipline 7: 3 candidates
        # Discipline 8: 6 candidates
        # Total = 36 candidates

        candidates_data = [
            # Discipline 1: 4 candidates
            {
                "discipline_id": 1,
                "candidate_name": "Professor Dr. Candidate Alpha",
                "candidate_title": "Senior Professor & Principal Investigator",
                "organisation": "Universiti Malaya (UM)",
                "position": "Director, Centre for Biotechnology Research",
                "photo_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Professor Dr. Alpha is an internationally recognized pioneer in agricultural genomics. Over the past 25 years, her pioneering work in drought-resistant crop breeding has directly improved yields for more than 40,000 smallholder farmers across Southeast Asia. She has published over 140 indexed Q1 journal articles, secured 7 national patents, and led high-profile international consortium projects funded by UNESCO and the Global Crop Diversity Trust.",
                "area_of_expertise": "Agricultural Biotechnology, Plant Functional Genomics, Climate-Resilient Agriculture",
                "qualifications": "• PhD in Plant Molecular Biology, University of Cambridge, UK (1998)\n• MSc in Applied Plant Science, Universiti Malaya (1994)\n• BSc (Hons) Biochemistry, Universiti Malaya (1991)",
                "professional_memberships": "• Senior Member, International Society for Horticultural Science (ISHS)\n• Council Member, Malaysian Society for Molecular Biology and Biotechnology\n• Chartered Biologist (CBiol), Royal Society of Biology, UK",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC1-CAND-ALPHA",
                "short_description": "Pioneer in agricultural functional genomics and climate-resilient crop genetics.",
                "display_order": 1
            },
            {
                "discipline_id": 1,
                "candidate_name": "Professor Dr. Candidate Bravo",
                "candidate_title": "Distinguished Professor",
                "organisation": "Universiti Putra Malaysia (UPM)",
                "position": "Dean, Faculty of Forestry and Environment",
                "photo_url": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Professor Dr. Bravo has made transformative contributions to tropical rainforest conservation ecology and peatland carbon sequestration modeling. His policy frameworks have been adopted by the Ministry of Natural Resources and Environmental Sustainability (NRES) as Malaysia's benchmark standard for carbon accounting in tropical wetlands.",
                "area_of_expertise": "Tropical Forest Ecology, Peat Swamp Restoration, Carbon Sequestration Modeling",
                "qualifications": "• D.Phil in Environmental Science, University of Oxford (2001)\n• Master of Forestry, Yale University (1996)\n• BSc Forestry, Universiti Putra Malaysia (1993)",
                "professional_memberships": "• Member, IUCN Species Survival Commission\n• Fellow, Institute of Foresters Malaysia (FIM)\n• International Peatland Society Scientific Advisory Board",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC1-CAND-BRAVO",
                "short_description": "Leading authority on tropical peatland carbon dynamics and wetland preservation.",
                "display_order": 2
            },
            {
                "discipline_id": 1,
                "candidate_name": "Dato' Dr. Candidate Charlie",
                "candidate_title": "Chief Scientific Officer",
                "organisation": "Malaysian Agricultural Research and Development Institute (MARDI)",
                "position": "Director of Agro-Technology & Genebank",
                "photo_url": "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Dato' Dr. Charlie has spearheaded national food security initiatives through the establishment of the National Agri-Food Cryo-Genebank preserving indigenous germplasm. His applied cultivars have transformed national rice productivity by 18% under salinity-stress conditions.",
                "area_of_expertise": "Agrobiodiversity Preservation, Cryoconservation, Crop Stress Physiology",
                "qualifications": "• PhD in Agronomy, Wageningen University, Netherlands (2003)\n• MSc Crop Science, Universiti Putra Malaysia (1997)\n• BAgricSc, Universiti Putra Malaysia (1994)",
                "professional_memberships": "• Chairman, National Rice Research Technical Committee\n• Member, Crop Science Society of America (CSSA)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC1-CAND-CHARLIE",
                "short_description": "Leader in national agro-biodiversity preservation and staple crop salinity tolerance.",
                "display_order": 3
            },
            {
                "discipline_id": 1,
                "candidate_name": "Associate Professor Dr. Candidate Delta",
                "candidate_title": "Associate Professor & Research Fellow",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Head of Marine Ecosystems Research Group",
                "photo_url": "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Dr. Delta has advanced coral reef resilience mapping and mangrove biome conservation across the Coral Triangle. Her team developed open-source acoustic telemetry devices now deployed across ASEAN maritime borders to track endangered elasmobranch populations.",
                "area_of_expertise": "Marine Biodiversity, Coastal Oceanography, Bioacoustics Conservation",
                "qualifications": "• PhD in Marine Biology, James Cook University, Australia (2008)\n• MSc Marine Sciences, Universiti Kebangsaan Malaysia (2004)\n• BSc (Hons) Marine Biology, Universiti Malaysia Terengganu (2001)",
                "professional_memberships": "• Executive Committee, Malaysian Marine Science Society\n• Member, International Coral Reef Society (ICRS)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC1-CAND-DELTA",
                "short_description": "Innovator in coastal ecosystem resilience and acoustic marine conservation.",
                "display_order": 4
            },

            # Discipline 2: 5 candidates
            {
                "discipline_id": 2,
                "candidate_name": "Professor Dr. Candidate Echo",
                "candidate_title": "Professor of Materials Chemistry",
                "organisation": "Universiti Sains Malaysia (USM)",
                "position": "Head of Advanced Nanomaterials Cluster",
                "photo_url": "https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Professor Dr. Echo is a prominent leader in porous metal-organic frameworks (MOFs) and heterogeneous nanocatalysis for carbon dioxide capture and conversion into green methanol. He holds 12 patents and was named a Clarivate Highly Cited Researcher in Chemistry for three consecutive years.",
                "area_of_expertise": "Metal-Organic Frameworks, Nanocatalysis, CO2 Hydrogenation, Green Chemistry",
                "qualifications": "• PhD in Inorganic Materials Chemistry, Imperial College London (2000)\n• MSc Chemistry, Universiti Sains Malaysia (1996)\n• BSc (Hons) Pure Chemistry, USM (1993)",
                "professional_memberships": "• Fellow, Royal Society of Chemistry (FRSC), UK\n• Member, Malaysian Institute of Chemistry (IKM)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC2-CAND-ECHO",
                "short_description": "Global leader in porous metal-organic frameworks for carbon capture and green fuels.",
                "display_order": 1
            },
            {
                "discipline_id": 2,
                "candidate_name": "Professor Dr. Candidate Foxtrot",
                "candidate_title": "Professor of Polymer Chemistry",
                "organisation": "Universiti Teknologi Malaysia (UTM)",
                "position": "Director, Institute of Bioproduct Development",
                "photo_url": "https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Pioneered sustainable biocomposites from oil palm empty fruit bunches and renewable polyhydroxyalkanoates (PHA) replacing single-use petrochemical plastics in high-performance packaging.",
                "area_of_expertise": "Renewable Polymers, Biodegradable Composites, Green Biomaterials",
                "qualifications": "• PhD in Polymer Science, University of Manchester, UK (2004)\n• MSc Polymer Engineering, UTM (2000)\n• BEng Chemical Engineering, UTM (1997)",
                "professional_memberships": "• Fellow, Malaysian Institute of Chemistry (FMIC)\n• Member, American Chemical Society (ACS)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC2-CAND-FOXTROT",
                "short_description": "Pioneering researcher in bio-derived polymers and eco-friendly packaging.",
                "display_order": 2
            },
            {
                "discipline_id": 2,
                "candidate_name": "Dr. Candidate Golf",
                "candidate_title": "Chief Specialist in Oleochemicals",
                "organisation": "Malaysian Palm Oil Board (MPOB)",
                "position": "Head of Advanced Chemical Synthesis Unit",
                "photo_url": "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Breakthrough synthesis of bio-lubricants and pharmaceutical-grade excipients derived from non-food palm fatty acid distillates, creating high-value export markets.",
                "area_of_expertise": "Oleochemical Synthesis, Biolubricants, Lipid Chemistry",
                "qualifications": "• PhD in Organic Synthesis, Kyoto University, Japan (2006)\n• BSc Industrial Chemistry, Universiti Putra Malaysia (2001)",
                "professional_memberships": "• Council Member, American Oil Chemists' Society (AOCS)\n• Registered Chemist (ChM), IKM",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC2-CAND-GOLF",
                "short_description": "Expert in oleochemical transformations and high-grade specialty biolubricants.",
                "display_order": 3
            },
            {
                "discipline_id": 2,
                "candidate_name": "Professor Dr. Candidate Hotel",
                "candidate_title": "Professor of Analytical Chemistry",
                "organisation": "Universiti Malaya (UM)",
                "position": "Director, Central Research Laboratories",
                "photo_url": "https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Developed microfluidic lab-on-a-chip electrochemical sensors for trace pesticide detection in river basins, achieving parts-per-trillion sensitivity.",
                "area_of_expertise": "Electrochemical Sensors, Microfluidics, Environmental Analytical Chemistry",
                "qualifications": "• PhD in Analytical Chemistry, University of Sheffield (2002)\n• BSc (Hons) Chemistry, Universiti Malaya (1998)",
                "professional_memberships": "• Fellow, Royal Society of Chemistry (FRSC)\n• President, Malaysian Analytical Sciences Society",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC2-CAND-HOTEL",
                "short_description": "Leader in electrochemical micro-sensors for environmental ultra-trace diagnostics.",
                "display_order": 4
            },
            {
                "discipline_id": 2,
                "candidate_name": "Associate Professor Dr. Candidate India",
                "candidate_title": "Associate Professor",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Deputy Director, Solar Energy Research Institute",
                "photo_url": "https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Groundbreaking synthesis of perovskite-tandem photovoltaic absorbers with verified stability exceeding 2,500 hours under equatorial humidity.",
                "area_of_expertise": "Perovskite Photovoltaics, Optoelectronic Chemistry, Solid-State Energy Storage",
                "qualifications": "• PhD in Physical Chemistry, National University of Singapore (2010)\n• BSc Chemistry, Universiti Kebangsaan Malaysia (2005)",
                "professional_memberships": "• Materials Research Society (MRS)\n• Institute of Chemistry Malaysia (IKM)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC2-CAND-INDIA",
                "short_description": "Specialist in stable perovskite photovoltaics and next-gen solar cell chemistry.",
                "display_order": 5
            },

            # Discipline 3: 3 candidates
            {
                "discipline_id": 3,
                "candidate_name": "Ir. Dr. Candidate Juliet",
                "candidate_title": "Chair Professor in Civil Engineering",
                "organisation": "Universiti Teknologi Malaysia (UTM)",
                "position": "Head of Coastal Protection and Offshore Infrastructure",
                "photo_url": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Pioneered adaptive geocomposite revetments that withstand super-storm surge pressures along high-erosion coastal belts in Peninsular Malaysia.",
                "area_of_expertise": "Offshore Geotechnics, Coastal Defense Systems, Disaster-Resilient Structures",
                "qualifications": "• PhD in Geotechnical Engineering, Delft University of Technology (1999)\n• MEng Civil Engineering, UTM (1995)\n• BEng (Hons) Civil Engineering, UTM (1992)",
                "professional_memberships": "• Professional Engineer with Practising Certificate (PEPC), BEM\n• Fellow, Institution of Engineers Malaysia (FIEM)\n• Fellow, Institution of Civil Engineers (FICE), UK",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC3-CAND-JULIET",
                "short_description": "Distinguished expert in resilient offshore infrastructure and coastal defense geotechnics.",
                "display_order": 1
            },
            {
                "discipline_id": 3,
                "candidate_name": "Ir. Dr. Candidate Kilo",
                "candidate_title": "Professor of Robotics & Automation",
                "organisation": "Universiti Malaya (UM)",
                "position": "Director, Centre for Autonomous Robotics",
                "photo_url": "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Created precision robotic microsurgery end-effectors and robotic haptic rehabilitation exoskeletons deployed in 12 major public hospitals.",
                "area_of_expertise": "Robotics, Haptic Systems, Biomedical Instrumentation, Automation",
                "qualifications": "• PhD in Robotics & Mechanical Engineering, MIT, USA (2005)\n• BEng (Hons) Mechanical Engineering, Universiti Malaya (2000)",
                "professional_memberships": "• Senior Member, IEEE Robotics & Automation Society\n• Professional Engineer (PEng), Board of Engineers Malaysia",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC3-CAND-KILO",
                "short_description": "Innovator in robotic surgery manipulation and rehabilitation exoskeletons.",
                "display_order": 2
            },
            {
                "discipline_id": 3,
                "candidate_name": "Ir. Dr. Candidate Lima",
                "candidate_title": "Professor of Power Systems Engineering",
                "organisation": "Universiti Tenaga Nasional (UNITEN)",
                "position": "Chair, Smart Grid Research Centre",
                "photo_url": "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Developed grid stability and dynamic load dispatch algorithms for multi-gigawatt solar interconnectors across the national transmission grid.",
                "area_of_expertise": "Smart Grid Interconnection, Renewable Grid Stability, High-Voltage Power Transmission",
                "qualifications": "• PhD in Electrical Power Engineering, University of New South Wales (2002)\n• BEng (Hons) Electrical Engineering, Universiti Malaya (1997)",
                "professional_memberships": "• Fellow, Institution of Engineering and Technology (FIET)\n• Fellow, Institution of Engineers Malaysia (FIEM)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC3-CAND-LIMA",
                "short_description": "Architect of resilient power dispatch and smart grid national transmission architectures.",
                "display_order": 3
            },

            # Discipline 4: 6 candidates
            {
                "discipline_id": 4,
                "candidate_name": "Professor Dr. Candidate Mike",
                "candidate_title": "Professor of Artificial Intelligence",
                "organisation": "Universiti Malaya (UM)",
                "position": "Director, National Centre for AI & Data Science",
                "photo_url": "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Nation's foremost researcher in deep multimodal reasoning and Southeast Asian low-resource language models. Authored over 160 papers in top-tier conferences (NeurIPS, ICML, CVPR).",
                "area_of_expertise": "Artificial Intelligence, Multimodal Machine Learning, Natural Language Processing",
                "qualifications": "• PhD in Computer Science, Stanford University (2004)\n• MSc Artificial Intelligence, University of Edinburgh (2000)\n• BSc (Hons) Computer Science, Universiti Malaya (1998)",
                "professional_memberships": "• Senior Member, Association for Computing Machinery (ACM)\n• Fellow, British Computer Society (FBCS)\n• Senior Member, IEEE Computer Society",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-MIKE",
                "short_description": "Pioneering AI researcher in multimodal architectures and regional linguistic LLMs.",
                "display_order": 1
            },
            {
                "discipline_id": 4,
                "candidate_name": "Professor Dr. Candidate November",
                "candidate_title": "Professor of Cybersecurity",
                "organisation": "Universiti Sains Malaysia (USM)",
                "position": "Head of Cryptography and Cyber Defense Laboratory",
                "photo_url": "https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Developed post-quantum cryptographic primitives that resist Shor algorithm factorization on noisy intermediate-scale quantum computers.",
                "area_of_expertise": "Post-Quantum Cryptography, Zero-Knowledge Proofs, Network Security Architecture",
                "qualifications": "• PhD in Cryptography, ETH Zurich, Switzerland (2007)\n• MSc Information Security, Royal Holloway, University of London (2003)\n• BSc Computer Science, USM (2001)",
                "professional_memberships": "• Member, International Association for Cryptologic Research (IACR)\n• Certified Information Systems Security Professional (CISSP)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-NOVEMBER",
                "short_description": "Internationally renowned scholar in post-quantum cryptography and cryptographic lattices.",
                "display_order": 2
            },
            {
                "discipline_id": 4,
                "candidate_name": "Dr. Candidate Oscar",
                "candidate_title": "Chief Scientific Officer",
                "organisation": "MIMOS Berhad",
                "position": "Head of Semiconductor System-on-Chip (SoC) Design",
                "photo_url": "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Spearheaded national RISC-V edge-AI accelerator chip designs achieving sub-milliwatt power envelopes for remote IoT environmental sensor clusters.",
                "area_of_expertise": "VLSI Design, Edge AI Hardware Accelerators, RISC-V Architecture",
                "qualifications": "• PhD in Electrical & Computer Engineering, Purdue University, USA (2009)\n• BEng Computer Engineering, Universiti Teknologi Malaysia (2004)",
                "professional_memberships": "• Senior Member, IEEE Solid-State Circuits Society\n• Board Member, Malaysia Semiconductor Industry Association",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-OSCAR",
                "short_description": "Architect of indigenous low-power RISC-V edge-AI silicon and VLSI systems.",
                "display_order": 3
            },
            {
                "discipline_id": 4,
                "candidate_name": "Associate Professor Dr. Candidate Papa",
                "candidate_title": "Associate Professor in Data Science",
                "organisation": "Universiti Putra Malaysia (UPM)",
                "position": "Leader, Big Data and Health Informatics Group",
                "photo_url": "https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Engineered real-time epidemiological spatio-temporal tracking platforms utilized by state health departments to preempt vector-borne dengue outbreaks.",
                "area_of_expertise": "Health Informatics, Spatio-Temporal Data Mining, Predictive Epidemiology",
                "qualifications": "• PhD in Computing & Informatics, University of Melbourne (2011)\n• MSc Computer Science, UPM (2006)\n• BSc (Hons) Computer Science, UPM (2003)",
                "professional_memberships": "• Member, International Medical Informatics Association (IMIA)\n• Member, IEEE Computational Intelligence Society",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-PAPA",
                "short_description": "Leader in spatio-temporal machine learning models for disease outbreak surveillance.",
                "display_order": 4
            },
            {
                "discipline_id": 4,
                "candidate_name": "Professor Dr. Candidate Quebec",
                "candidate_title": "Professor of Software Engineering",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Director, Centre for Software Quality & Assurance",
                "photo_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Authored national standards for automated formal verification of safety-critical embedded avionics and rail transit signaling software.",
                "area_of_expertise": "Formal Methods, Safety-Critical Systems, Automated Program Verification",
                "qualifications": "• PhD in Software Engineering, Carnegie Mellon University (2003)\n• MSc Computer Science, UKM (1998)\n• BSc Computer Science, UKM (1995)",
                "professional_memberships": "• Fellow, British Computer Society (FBCS)\n• Senior Member, IEEE Reliability Society",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-QUEBEC",
                "short_description": "Authority on formal methods and safety-critical software assurance frameworks.",
                "display_order": 5
            },
            {
                "discipline_id": 4,
                "candidate_name": "Associate Professor Dr. Candidate Romeo",
                "candidate_title": "Associate Professor in Quantum Computing",
                "organisation": "Universiti Malaya (UM)",
                "position": "Principal Investigator, Quantum Algorithms Laboratory",
                "photo_url": "https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Designed variational quantum eigensolvers that reduce circuit depth by 45% for simulating heavy-metal catalyst coordination complexes.",
                "area_of_expertise": "Quantum Algorithms, Variational Quantum Optimization, Quantum Information Science",
                "qualifications": "• PhD in Quantum Information Theory, University of Waterloo (2013)\n• MSc Theoretical Physics, Universiti Malaya (2009)\n• BSc (Hons) Physics, UM (2007)",
                "professional_memberships": "• Member, American Physical Society (APS)\n• Member, IEEE Quantum Computing Community",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC4-CAND-ROMEO",
                "short_description": "Pioneer in low-depth variational quantum algorithms and quantum simulation.",
                "display_order": 6
            },

            # Discipline 5: 4 candidates
            {
                "discipline_id": 5,
                "candidate_name": "Professor Dr. Candidate Sierra",
                "candidate_title": "Professor of Applied Mathematics",
                "organisation": "Universiti Sains Malaysia (USM)",
                "position": "Director, School of Mathematical Sciences",
                "photo_url": "https://images.unsplash.com/photo-1566492031773-4f4e44671857?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "World-class contributions to nonlinear partial differential equations and turbulent fluid boundary layer mathematical physics.",
                "area_of_expertise": "Nonlinear PDEs, Computational Fluid Dynamics, Soliton Theory",
                "qualifications": "• PhD in Applied Mathematics, Princeton University (2001)\n• MSc Mathematics, USM (1996)\n• BSc (Hons) Mathematics, USM (1993)",
                "professional_memberships": "• Fellow, Institute of Mathematics and its Applications (FIMA), UK\n• Member, Society for Industrial and Applied Mathematics (SIAM)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC5-CAND-SIERRA",
                "short_description": "Prominent mathematician specializing in nonlinear hydrodynamic PDEs and soliton dynamics.",
                "display_order": 1
            },
            {
                "discipline_id": 5,
                "candidate_name": "Professor Dr. Candidate Tango",
                "candidate_title": "Professor of Theoretical Astrophysics",
                "organisation": "Universiti Malaya (UM)",
                "position": "Head of Radio Astronomy Group",
                "photo_url": "https://images.unsplash.com/photo-1573496799652-408c2ac9fe98?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Core contributor to international very-long-baseline interferometry (VLBI) observations modeling black hole relativistic accretion jets.",
                "area_of_expertise": "Radio Astronomy, Relativistic Astrophysics, High-Energy Computational Physics",
                "qualifications": "• PhD in Astrophysics, University of Cambridge (2005)\n• BSc (Hons) Physics, Universiti Malaya (2000)",
                "professional_memberships": "• Member, International Astronomical Union (IAU)\n• Fellow, Royal Astronomical Society (FRAS), UK",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC5-CAND-TANGO",
                "short_description": "Leading astrophysicist specializing in VLBI radio astronomy and relativistic jets.",
                "display_order": 2
            },
            {
                "discipline_id": 5,
                "candidate_name": "Associate Professor Dr. Candidate Uniform",
                "candidate_title": "Associate Professor in Geophysics",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Leader, Seismology and Earth Systems Laboratory",
                "photo_url": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Developed subsurface 3D seismic crustal tomography maps identifying unmapped fault reactivations along the Sunda Plate margin.",
                "area_of_expertise": "Seismic Tomography, Geohazards, Structural Geophysics",
                "qualifications": "• PhD in Geophysics, Australian National University (2008)\n• MSc Geology, UKM (2003)\n• BSc Geology, UKM (2000)",
                "professional_memberships": "• Member, American Geophysical Union (AGU)\n• Council Member, Geological Society of Malaysia",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC5-CAND-UNIFORM",
                "short_description": "Geophysicist charting active tectonic faults and intraplate seismic vulnerabilities.",
                "display_order": 3
            },
            {
                "discipline_id": 5,
                "candidate_name": "Dr. Candidate Victor",
                "candidate_title": "Senior Research Scientist",
                "organisation": "Malaysian Space Agency (MYSA)",
                "position": "Lead Scientist, Satellite Remote Sensing Earth Observation",
                "photo_url": "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Architected synthetic aperture radar (SAR) interferometry algorithms tracking sub-centimeter land subsidence in urban delta basins.",
                "area_of_expertise": "Interferometric SAR, Satellite Geodesy, Climate Dynamics",
                "qualifications": "• PhD in Satellite Geodesy, University College London (2009)\n• BEng Geomatic Engineering, UTM (2004)",
                "professional_memberships": "• Member, IEEE Geoscience and Remote Sensing Society (GRSS)\n• Royal Institution of Surveyors Malaysia (RISM)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC5-CAND-VICTOR",
                "short_description": "Leader in satellite radar interferometry for precision urban deformation monitoring.",
                "display_order": 4
            },

            # Discipline 6: 5 candidates
            {
                "discipline_id": 6,
                "candidate_name": "Professor Dr. Candidate Whiskey",
                "candidate_title": "Senior Consultant Clinical Immunologist",
                "organisation": "Universiti Malaya Medical Centre (UMMC)",
                "position": "Director, Tropical Infectious Diseases Research & Education Centre",
                "photo_url": "https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "World authority on vector-borne arboviral immunopathology. Led pivotal Phase III trials on recombinant dengue vaccine efficacy and humoral neutralization.",
                "area_of_expertise": "Infectious Disease Immunology, Arboviruses, Clinical Trial Protocols",
                "qualifications": "• MBBS, Universiti Malaya (1995)\n• PhD in Viral Immunology, Johns Hopkins University (2002)\n• Fellow of the Royal College of Physicians (FRCP), London",
                "professional_memberships": "• Fellow, Academy of Medicine of Malaysia (FAMM)\n• Advisory Committee, World Health Organization (WHO) Strategic Advisory Group of Experts",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC6-CAND-WHISKEY",
                "short_description": "World-renowned infectious disease immunologist and clinical trial investigator.",
                "display_order": 1
            },
            {
                "discipline_id": 6,
                "candidate_name": "Professor Dr. Candidate Xray",
                "candidate_title": "Professor of Pharmacology",
                "organisation": "Universiti Sains Malaysia (USM)",
                "position": "Head, Centre for Drug Research",
                "photo_url": "https://images.unsplash.com/photo-1582750433449-648ed127bb54?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Discovered novel neuroprotective alkaloid derivatives from Mitragyna speciosa exhibiting potent non-addictive analgesia in preclinical stroke models.",
                "area_of_expertise": "Neuropharmacology, Ethnopharmacology, Drug Discovery",
                "qualifications": "• PhD in Pharmacology, University of Strathclyde, UK (2000)\n• BPharm (Hons), Universiti Sains Malaysia (1995)",
                "professional_memberships": "• Member, British Pharmacological Society (BPS)\n• Council Member, Malaysian Society of Pharmacology and Physiology",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC6-CAND-XRAY",
                "short_description": "Pioneer in natural product neuropharmacology and non-opioid pain analgesics.",
                "display_order": 2
            },
            {
                "discipline_id": 6,
                "candidate_name": "Datuk Dr. Candidate Yankee",
                "candidate_title": "Senior Consultant Epidemiologist",
                "organisation": "Ministry of Health Malaysia (MOH)",
                "position": "Director, Institute for Medical Research (IMR)",
                "photo_url": "https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Steered the national antimicrobial resistance (AMR) containment strategy and national genomic pathogen sequencing consortium during major public health crises.",
                "area_of_expertise": "Public Health Surveillance, Pathogen Genomics, Antimicrobial Stewardship",
                "qualifications": "• MD, Universiti Kebangsaan Malaysia (1994)\n• Master of Public Health, Harvard University (1999)\n• DrPH, University of North Carolina (2004)",
                "professional_memberships": "• Fellow, Academy of Medicine of Malaysia (FAMM)\n• Member, International Society for Infectious Diseases (ISID)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC6-CAND-YANKEE",
                "short_description": "Public health epidemiologist and national genomic surveillance director.",
                "display_order": 3
            },
            {
                "discipline_id": 6,
                "candidate_name": "Professor Dr. Candidate Zulu",
                "candidate_title": "Professor of Oncology & Molecular Pathology",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Director, UKM Medical Molecular Biology Institute (UMBI)",
                "photo_url": "https://images.unsplash.com/photo-1594824813571-638f02614d3f?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Established the Malaysian Multi-Ethnic Cohort Cancer Genome Atlas, discovering unique germline BRCA1/2 and TP53 mutation spectra in Asian populations.",
                "area_of_expertise": "Cancer Genomics, Molecular Pathology, Precision Oncology",
                "qualifications": "• MBChB, University of Glasgow (1998)\n• PhD in Molecular Oncology, University of Cambridge (2005)\n• Fellow of Royal College of Pathologists (FRCPath), UK",
                "professional_memberships": "• Member, American Association for Cancer Research (AACR)\n• President, Malaysian Society of Human Genetics",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC6-CAND-ZULU",
                "short_description": "Champion of multi-ethnic cancer genomics and personalized targeted oncology.",
                "display_order": 4
            },
            {
                "discipline_id": 6,
                "candidate_name": "Associate Professor Dr. Candidate AlphaPrime",
                "candidate_title": "Consultant Cardiologist & Biomedical Engineer",
                "organisation": "Institut Jantung Negara (IJN)",
                "position": "Head of Translational Cardiovascular Engineering",
                "photo_url": "https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Co-invented bio-resorbable magnesium coronary stents engineered specifically to accommodate high-tortuosity small-vessel atherosclerotic lesions.",
                "area_of_expertise": "Interventional Cardiology, Cardiovascular Stent Design, Hemodynamics",
                "qualifications": "• MD, Universiti Malaya (2003)\n• PhD in Bioengineering, Imperial College London (2010)\n• Fellow, European Society of Cardiology (FESC)",
                "professional_memberships": "• National Heart Association of Malaysia (NHAM)\n• Fellow, American College of Cardiology (FACC)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC6-CAND-ALPHAPRIME",
                "short_description": "Cardiovascular clinician-engineer behind bio-resorbable endovascular implants.",
                "display_order": 5
            },

            # Discipline 7: 3 candidates
            {
                "discipline_id": 7,
                "candidate_name": "Professor Dr. Candidate BravoPrime",
                "candidate_title": "Distinguished Professor of Science Policy & Philosophy",
                "organisation": "Universiti Malaya (UM)",
                "position": "Director, Centre for Science & Technology Policy Studies",
                "photo_url": "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Architected the National Science, Technology & Innovation Policy (DSTIN) and pioneered bioethics guidelines for human CRISPR gene editing in the ASEAN region.",
                "area_of_expertise": "Science Policy, Ethics of Emerging Technologies, STI Governance",
                "qualifications": "• DPhil in Science & Technology Studies, University of Sussex (1998)\n• MA Public Policy, National University of Singapore (1994)\n• BA (Hons) Philosophy, Universiti Malaya (1991)",
                "professional_memberships": "• Vice President, UNESCO International Bioethics Committee\n• Member, Society for Social Studies of Science (4S)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC7-CAND-BRAVOPRIME",
                "short_description": "Foremost authority on national STI policy formulation and bioethical governance.",
                "display_order": 1
            },
            {
                "discipline_id": 7,
                "candidate_name": "Professor Dr. Candidate CharliePrime",
                "candidate_title": "Professor of Economics & Innovation",
                "organisation": "Universiti Kebangsaan Malaysia (UKM)",
                "position": "Chair, Institute of Malaysian & International Studies",
                "photo_url": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Empirical researcher whose macro-econometric modeling proved the multiplier effect of national gross expenditure on R&D (GERD) on middle-income trap avoidance.",
                "area_of_expertise": "Economics of Innovation, Industrial Upgrading, Knowledge Economies",
                "qualifications": "• PhD in Economics, London School of Economics (LSE) (2002)\n• MEcon, Universiti Malaya (1997)\n• BEcon (Hons), UKM (1994)",
                "professional_memberships": "• Fellow, Malaysian Economic Association\n• Research Associate, Asian Development Bank Institute (ADBI)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC7-CAND-CHARLIEPRIME",
                "short_description": "Prominent innovation economist analyzing R&D productivity and economic transitions.",
                "display_order": 2
            },
            {
                "discipline_id": 7,
                "candidate_name": "Associate Professor Dr. Candidate DeltaPrime",
                "candidate_title": "Associate Professor in Science Education & Psychology",
                "organisation": "Universiti Sains Malaysia (USM)",
                "position": "Head, Regional Centre of Expertise in STEM Education",
                "photo_url": "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Designed the immersive inquiry-based STEM curriculum implemented in over 800 rural schools, increasing science enrollment among indigenous youths by 34%.",
                "area_of_expertise": "STEM Pedagogy, Educational Cognitive Psychology, Digital Learning Inclusivity",
                "qualifications": "• PhD in Science Education, University of Wisconsin-Madison (2007)\n• MEd, Universiti Sains Malaysia (2003)\n• BSc (Ed), USM (1999)",
                "professional_memberships": "• Member, National Association for Research in Science Teaching (NARST)\n• Executive Council, Malaysian Educational Research Association",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC7-CAND-DELTAPRIME",
                "short_description": "Pioneering STEM educator advancing inclusive cognitive science learning models.",
                "display_order": 3
            },

            # Discipline 8: 6 candidates
            {
                "discipline_id": 8,
                "candidate_name": "Datuk Ir. Dr. Candidate EchoPrime",
                "candidate_title": "Chief Executive Officer & Chief Technologist",
                "organisation": "NanoMalaysia Berhad / Industrial Consortium",
                "position": "Group Executive Director",
                "photo_url": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Spearheaded national commercialization of graphene-enhanced battery electrodes and hydrogen fuel cell electric vehicles, securing RM350M in foreign direct tech investments.",
                "area_of_expertise": "Nanotechnology Commercialization, CleanTech Ventures, Technology Transfer",
                "qualifications": "• PhD in Chemical & Materials Engineering, University of Queensland (2000)\n• MBA, Cranfield University (2006)\n• BEng (Hons) Chemical Engineering, UTM (1995)",
                "professional_memberships": "• Professional Engineer with Practising Certificate (PEPC), BEM\n• Fellow, Academy of Technology Commercialization (FATC)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-ECHOPRIME",
                "short_description": "Visionary clean-tech entrepreneur and industrial commercialization executive.",
                "display_order": 1
            },
            {
                "discipline_id": 8,
                "candidate_name": "Dr. Candidate FoxtrotPrime",
                "candidate_title": "Vice President of Global Semiconductor Engineering",
                "organisation": "Inari Amertron / SilTerra Malaysia",
                "position": "Chief Technology Officer",
                "photo_url": "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Led the development and qualification of advanced RF-frontend millimeter-wave multi-chip modules deployed inside tier-1 global smartphone flagships.",
                "area_of_expertise": "Advanced Semiconductor Packaging, RF Front-End Modules, Heterogeneous Integration",
                "qualifications": "• PhD in Microelectronics, UC Berkeley, USA (2005)\n• BEng Electrical Engineering, Universiti Malaya (2000)",
                "professional_memberships": "• Senior Member, IEEE Electronics Packaging Society\n• Chairman, National Semiconductor Packaging Taskforce",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-FOXTROTPRIME",
                "short_description": "Global leader in advanced RF microelectronics packaging and heterogeneous integration.",
                "display_order": 2
            },
            {
                "discipline_id": 8,
                "candidate_name": "Datin Paduka Dr. Candidate GolfPrime",
                "candidate_title": "Managing Director & Founder",
                "organisation": "BioNexus Genomics Sdn Bhd",
                "position": "Chief Executive Officer",
                "photo_url": "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Bootstrapped Malaysia's premier high-throughput diagnostic sequencing spin-off company to unicorn valuation, providing accessible clinical genetics across 14 countries.",
                "area_of_expertise": "Biotech Entrepreneurship, Clinical Diagnostic Commercialization, Venture Scalability",
                "qualifications": "• PhD in Molecular Medicine, Oxford University (2003)\n• BSc (Hons) Genetics, Universiti Malaya (1998)",
                "professional_memberships": "• Member, Global Biotech Innovation Council\n• Fellow, Malaysian Institute of Management",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-GOLFPRIME",
                "short_description": "Pioneering biotech entrepreneur who built regional diagnostics unicorn.",
                "display_order": 3
            },
            {
                "discipline_id": 8,
                "candidate_name": "Ir. Dr. Candidate HotelPrime",
                "candidate_title": "Chief Digital & Innovation Officer",
                "organisation": "PETRONAS Research Sdn Bhd",
                "position": "Vice President of Decarbonization Technologies",
                "photo_url": "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Championed industrial-scale Carbon Capture, Utilization, and Storage (CCUS) offshore deep-saline aquifer injection projects, sequestering 3.3 MTPA of CO2.",
                "area_of_expertise": "Carbon Capture Utilization & Storage (CCUS), Industrial Decarbonization, Subsurface Engineering",
                "qualifications": "• PhD in Petroleum Engineering, University of Tulsa (2004)\n• BEng Chemical Engineering, Universiti Teknologi PETRONAS (1999)",
                "professional_memberships": "• Registered Professional Engineer (BEM)\n• Member, Society of Petroleum Engineers (SPE)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-HOTELPRIME",
                "short_description": "Industrial leader driving mega-scale CCUS offshore sequestration implementations.",
                "display_order": 4
            },
            {
                "discipline_id": 8,
                "candidate_name": "Dr. Candidate IndiaPrime",
                "candidate_title": "Executive Director",
                "organisation": "Aerospace Malaysia Innovation Centre (AMIC)",
                "position": "Head of Advanced Aerospace Composites",
                "photo_url": "https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Engineered automated dry fiber placement technologies for commercial aircraft composite wing structures, qualifying Malaysian manufacturing lines for international aviation primes.",
                "area_of_expertise": "Aerospace Composites, Automated Fiber Placement, Lightweight Structural Materials",
                "qualifications": "• PhD in Aerospace Engineering, Cranfield University, UK (2006)\n• MEng Aeronautical Engineering, UTM (2001)",
                "professional_memberships": "• Fellow, Royal Aeronautical Society (FRAeS)\n• Member, American Institute of Aeronautics and Astronautics (AIAA)",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-INDIAPRIME",
                "short_description": "Pioneer in aerospace structural composites manufacturing and automated fiber placement.",
                "display_order": 5
            },
            {
                "discipline_id": 8,
                "candidate_name": "Associate Professor Ir. Dr. Candidate JulietPrime",
                "candidate_title": "Chief Technology Officer",
                "organisation": "Green Hydrogen Technologies Malaysia",
                "position": "Director of Electrolyser Development",
                "photo_url": "https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80",
                "basis_of_recommendation": "Developed high-efficiency proton exchange membrane (PEM) water electrolysers utilizing low-iridium nanostructured catalyst layers, slashing green hydrogen production costs by 40%.",
                "area_of_expertise": "Hydrogen Electrolysers, Fuel Cells, Electrochemical Energy Systems",
                "qualifications": "• PhD in Electrochemical Engineering, University of Newcastle, UK (2008)\n• BEng (Hons) Chemical Engineering, Universiti Malaya (2003)",
                "professional_memberships": "• Member, International Association for Hydrogen Energy (IAHE)\n• Professional Engineer (PEng), Board of Engineers Malaysia",
                "nomination_form_url": "https://onedrive.live.com/view.aspx?cid=ASM-NOMINATION-FORM-DISC8-CAND-JULIETPRIME",
                "short_description": "Innovator in industrial PEM hydrogen electrolysers and low-iridium catalyst layers.",
                "display_order": 6
            }
        ]

        for cand in candidates_data:
            cursor.execute(
                """
                INSERT OR IGNORE INTO candidates (
                    discipline_id, candidate_name, candidate_title, organisation, position,
                    photo_url, basis_of_recommendation, area_of_expertise, qualifications,
                    professional_memberships, nomination_form_url, short_description, display_order, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                """,
                (
                    cand["discipline_id"], cand["candidate_name"], cand["candidate_title"],
                    cand["organisation"], cand["position"], cand["photo_url"],
                    cand["basis_of_recommendation"], cand["area_of_expertise"],
                    cand["qualifications"], cand["professional_memberships"],
                    cand["nomination_form_url"], cand["short_description"],
                    cand["display_order"]
                )
            )

        # 5. Seed Due Diligence Categories
        categories = [
            "General Comment",
            "Professional Background",
            "Academic / Research Record",
            "Leadership",
            "Achievement",
            "Conflict of Interest",
            "Integrity / Reputation",
            "Other Relevant Information"
        ]
        for idx, cat in enumerate(categories):
            cursor.execute(
                "INSERT OR IGNORE INTO due_diligence_categories (category_name, display_order) VALUES (?, ?)",
                (cat, idx + 1)
            )

        # 6. Seed Information Pages
        info_pages = [
            ("guidelines", "Candidate Ranking Exercise Guidelines", 
             """## Academy of Sciences Malaysia — Candidate Assessment Guidelines

### Objective
This exercise facilitates the formal, rigorous, and confidential evaluation of shortlisted candidates for induction into the Fellowship of the Academy of Sciences Malaysia (ASM).

### Assessment Principles
1. **Excellence & Impact**: Assessment must be founded upon verifiable scientific merit, technological innovation, socioeconomic impact, and institutional leadership.
2. **Confidentiality**: All proceedings, candidate portfolios, individual reviewer rankings, and due diligence commentaries are strictly confidential under the Academy of Sciences Malaysia Act 1994.
3. **Impartiality & Objectivity**: Evaluators are required to declare any conflict of interest regarding candidates within their review portfolio.""", 1),

            ("criteria", "Fellowship Assessment Criteria",
             """## Detailed Evaluation Criteria

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
   * Adherence to highest standards of research ethics and professional conduct.""", 2),

            ("faq", "Frequently Asked Questions (FAQ)",
             """## Frequently Asked Questions

**Q: Can I assign the same ranking to two candidates in the same discipline?**
A: No. The system strictly enforces ordinal uniqueness. If a discipline has 4 candidates, you must assign 1, 2, 3, and 4 exactly once.

**Q: Can I submit rankings for only some disciplines?**
A: You can save your progress as a draft at any time. However, final submission is only permitted once every candidate across all 8 disciplines has received a valid ranking.

**Q: Can I modify my submission after clicking 'Submit Ranking'?**
A: Once finalized, your ranking submission is cryptographically sealed and locked. In exceptional circumstances, an authorized Administrator may reopen your submission upon formal written request.""", 3),

            ("confidentiality", "Statutory Confidentiality Notice",
             """## Statutory Confidentiality Notice

All information contained within this system, including nominee dossiers, curriculum vitae, expert evaluations, due diligence statements, and individual ranking ballots, constitutes strictly confidential institutional data.

Unauthorized disclosure, duplication, distribution, or copying of any record is strictly prohibited and subject to institutional sanctions and legal penalties under Malaysian legislation.""", 4)
        ]

        for slug, title, content, order in info_pages:
            cursor.execute(
                """
                INSERT OR IGNORE INTO system_information_pages (slug, title, content, display_order)
                VALUES (?, ?, ?, ?)
                """,
                (slug, title, content, order)
            )

        print("Database initialized and successfully seeded with 8 disciplines, 36 candidates, test accounts, and info pages!")

if __name__ == "__main__":
    seed_database()
