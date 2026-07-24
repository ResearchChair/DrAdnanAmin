<?php

return [
    'orcid_id' => env('ACADEMIC_ORCID_ID', ''),
    'openalex_author_id' => env('ACADEMIC_OPENALEX_AUTHOR_ID', ''),
    'openalex_email' => env('ACADEMIC_OPENALEX_EMAIL', 'portfolio@example.com'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS / SSL for ORCID & OpenAlex API calls
    |--------------------------------------------------------------------------
    | WAMP on Windows often needs a CA bundle. A copy is stored at
    | storage/app/cacert.pem. Set ACADEMIC_HTTP_VERIFY=false only for local dev.
    */
    'http_verify' => env('ACADEMIC_HTTP_VERIFY'),
    'ca_bundle' => env('ACADEMIC_CA_BUNDLE', storage_path('app/cacert.pem')),

    'youtube_daily_rotation' => env('YOUTUBE_DAILY_ROTATION', true),
    'youtube_autoplay' => env('YOUTUBE_AUTOPLAY', true),
    'youtube_rotation_pool' => (int) env('YOUTUBE_ROTATION_POOL', 30),
    'youtube_channel_id' => env('YOUTUBE_CHANNEL_ID', ''),

    'publication_types' => [
        'journal' => 'Journal Article',
        'conference' => 'Conference Paper',
        'book_chapter' => 'Book Chapter',
        'preprint' => 'Preprint',
        'book' => 'Book',
        'other' => 'Other',
    ],

    'publication_statuses' => [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'accepted' => 'Accepted',
        'published' => 'Published',
        'rejected' => 'Rejected',
    ],

    'collaborator_link_expiry_days' => (int) env('COLLABORATOR_LINK_EXPIRY_DAYS', 14),

    'publication_publishers' => [
        'IEEE' => ['ieee'],
        'Springer' => ['springer', 'lecture notes'],
        'Elsevier' => ['elsevier', 'procedia', 'applied soft computing', 'expert systems'],
        'MDPI' => ['mdpi', 'mathematics', 'sensors', 'sustainability'],
        'PeerJ' => ['peerj'],
        'IOP Publishing' => ['journal of physics', 'iop'],
        'ACM' => ['acm', 'association for computing'],
        'Wiley' => ['wiley'],
        'Taylor & Francis' => ['taylor', 'francis'],
        'SAGE' => ['sage'],
        'Nature' => ['nature'],
    ],

    'activity_types' => [
        'session_chair' => 'Session Chair',
        'committee' => 'Committee Member',
        'reviewer' => 'Journal Reviewer',
        'editorial' => 'Editorial Board',
        'organizer' => 'Conference Organizer',
        'other' => 'Other',
    ],

    'student_statuses' => [
        'completed' => 'Completed',
        'in_progress' => 'In Progress',
        'guest_scholar' => 'Guest Scholar',
        'fyp_projects' => 'FYP Projects',
    ],

    'student_profile_platforms' => [
        'google_scholar' => 'Google Scholar',
        'linkedin' => 'LinkedIn',
        'researchgate' => 'ResearchGate',
        'orcid' => 'ORCID',
        'github' => 'GitHub',
        'other' => 'Website',
    ],

    'training_types' => [
        'workshop' => 'Workshop',
        'training' => 'Training Program',
        'summer_school' => 'Summer School',
        'facilitator' => 'Facilitator',
        'resource_person' => 'Resource Person',
    ],

    'consultancy_types' => [
        'advisory' => 'Advisory',
        'technical' => 'Technical Consultancy',
        'research' => 'Research Consultancy',
        'capacity_building' => 'Capacity Building',
        'digital_transformation' => 'Digital Transformation',
        'other' => 'Other',
    ],

    'software_solution_types' => [
        'web_app' => 'Web Application',
        'desktop' => 'Desktop Software',
        'mobile' => 'Mobile App',
        'data_platform' => 'Data / Analytics Platform',
        'integration' => 'System Integration',
        'other' => 'Other',
    ],

    'academic_platforms' => [
        'google_scholar' => 'Google Scholar',
        'orcid' => 'ORCID',
        'dblp' => 'DBLP',
        'wos' => 'Web of Science',
        'wos_reviewer' => 'WoS Reviewer',
        'scopus' => 'Scopus',
        'researchgate' => 'ResearchGate',
        'semantic_scholar' => 'Semantic Scholar',
        'github' => 'GitHub',
        'youtube' => 'YouTube',
    ],

    'social_platforms' => [
        'linkedin' => 'LinkedIn',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'x' => 'X (Twitter)',
        'github' => 'GitHub',
        'youtube' => 'YouTube',
    ],
];
