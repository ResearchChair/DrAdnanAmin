<?php

return [
    'orcid_id' => env('ACADEMIC_ORCID_ID', ''),
    'openalex_author_id' => env('ACADEMIC_OPENALEX_AUTHOR_ID', ''),
    'openalex_email' => env('ACADEMIC_OPENALEX_EMAIL', 'portfolio@example.com'),

    'publication_types' => [
        'journal' => 'Journal Article',
        'conference' => 'Conference Paper',
        'book_chapter' => 'Book Chapter',
        'preprint' => 'Preprint',
        'book' => 'Book',
        'other' => 'Other',
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
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ],

    'training_types' => [
        'workshop' => 'Workshop',
        'training' => 'Training Program',
        'summer_school' => 'Summer School',
        'facilitator' => 'Facilitator',
        'resource_person' => 'Resource Person',
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
