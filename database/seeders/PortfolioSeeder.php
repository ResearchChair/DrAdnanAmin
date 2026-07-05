<?php

namespace Database\Seeders;

use App\Models\AcademicProfile;
use App\Models\CitationStat;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Profile;
use App\Models\Publication;
use App\Models\ResearchActivity;
use App\Models\ShowcaseProduct;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('admin_security.login_email', 'admin@portfolio.local')],
            [
                'name' => ucfirst((string) config('admin_security.username', 'admin')),
                'password' => Hash::make((string) config('admin_security.password', 'admin')),
            ]
        );

        $profile = Profile::query()->updateOrCreate(
            ['email' => 'adnan@imsciences.edu.pk'],
            [
                'name' => 'Adnan Amin',
                'credentials' => 'Ph.D.',
                'title' => 'Assistant Professor & Program Director (Computer Science)',
                'affiliation' => 'Institute of Management Sciences (IMSciences), Peshawar, Pakistan',
                'secondary_affiliation' => 'Visiting Faculty, Al-Farabi National University Kazakhstan (online)',
                'phone' => null,
                'whatsapp' => '+923001234567',
                'location' => 'Peshawar, Pakistan',
                'tagline' => 'Artificial Intelligence · Machine Learning · Data Mining',
                'bio_html' => '<p>Dr. Adnan Amin received his Ph.D. with scholastic honours in Artificial Intelligence: Machine Learning and MS in Computer Science (specialisation in Databases/Data Mining) with distinction from the Institute of Management Sciences (IMSciences), Peshawar, Pakistan.</p><p>Currently serving as Assistant Professor (Computer Science) and Program Director at IMSciences. Research interests include customer churn prediction, continual deep learning in medical informatics, social network analysis, and DDoS attack detection in Edge-IIoT networks.</p>',
                'research_interests' => "Customer churn prediction in service-based industries\nContinual deep learning in medical informatics\nSocial network analysis and link prediction\nDDoS attack detection in Edge-IIoT and Digital Twins\nData augmentation and class imbalance handling",
                'orcid_id' => config('academic.orcid_id') ?: '0000-0000-0000-0000',
                'openalex_author_id' => config('academic.openalex_author_id'),
            ]
        );

        CitationStat::query()->updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'total_citations' => 1585,
                'h_index' => 17,
                'i10_index' => 19,
                'publication_count' => 42,
                'source' => 'google_scholar',
                'synced_at' => now(),
            ]
        );

        $academicProfiles = [
            ['platform' => 'google_scholar', 'label' => 'Google Scholar', 'url' => 'https://scholar.google.com/citations?user=j7skQdsAAAAJ', 'sort_order' => 1],
            ['platform' => 'orcid', 'label' => 'ORCID', 'url' => 'https://orcid.org/0000-0000-0000-0000', 'sort_order' => 2],
            ['platform' => 'dblp', 'label' => 'DBLP', 'url' => 'https://dblp.org/pid/00/0000.html', 'sort_order' => 3],
            ['platform' => 'wos', 'label' => 'Web of Science', 'url' => 'https://www.webofscience.com/wos/author/record/000000', 'sort_order' => 4],
            ['platform' => 'wos_reviewer', 'label' => 'WoS Reviewer', 'url' => 'https://publons.com/researcher/0000000', 'sort_order' => 5],
            ['platform' => 'scopus', 'label' => 'Scopus', 'url' => 'https://www.scopus.com/authid/detail.uri?authorId=00000000000', 'sort_order' => 6],
            ['platform' => 'researchgate', 'label' => 'ResearchGate', 'url' => 'https://www.researchgate.net/profile/Adnan-Amin', 'sort_order' => 7],
            ['platform' => 'github', 'label' => 'GitHub', 'url' => 'https://github.com/geoamins', 'sort_order' => 8],
            ['platform' => 'youtube', 'label' => 'YouTube', 'url' => 'https://www.youtube.com/@example', 'sort_order' => 9],
        ];

        foreach ($academicProfiles as $item) {
            AcademicProfile::query()->updateOrCreate(
                ['profile_id' => $profile->id, 'platform' => $item['platform']],
                array_merge($item, ['profile_id' => $profile->id, 'is_visible' => true])
            );
        }

        $socialLinks = [
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/example', 'sort_order' => 1],
            ['platform' => 'facebook', 'url' => 'https://facebook.com/example', 'sort_order' => 2],
            ['platform' => 'instagram', 'url' => 'https://instagram.com/example', 'sort_order' => 3],
            ['platform' => 'x', 'url' => 'https://x.com/example', 'sort_order' => 4],
        ];

        foreach ($socialLinks as $item) {
            SocialLink::query()->updateOrCreate(
                ['profile_id' => $profile->id, 'platform' => $item['platform']],
                array_merge($item, ['profile_id' => $profile->id, 'is_visible' => true])
            );
        }

        $publications = [
            [
                'title' => 'Comparing oversampling techniques to handle the class imbalance problem: A customer churn prediction case study',
                'type' => 'journal',
                'year' => 2023,
                'venue' => 'IEEE Access',
                'authors' => 'A. Amin, et al.',
                'doi' => '10.1109/ACCESS.2023.0000000',
                'citation_count' => 45,
                'featured' => true,
            ],
            [
                'title' => 'DDoS attack detection in Edge-IIoT networks using deep learning',
                'type' => 'conference',
                'year' => 2022,
                'venue' => 'IEEE Conference',
                'authors' => 'A. Amin, et al.',
                'featured' => true,
            ],
            [
                'title' => 'Social network analysis for link prediction: A comprehensive survey',
                'type' => 'book_chapter',
                'year' => 2021,
                'venue' => 'Springer',
                'authors' => 'A. Amin, et al.',
            ],
        ];

        foreach ($publications as $index => $pub) {
            Publication::query()->updateOrCreate(
                ['title' => $pub['title']],
                array_merge($pub, ['sort_order' => $index, 'is_visible' => true])
            );
        }

        ResearchActivity::query()->updateOrCreate(
            ['title' => 'Session Chair, International Conference on AI & Data Science'],
            [
                'type' => 'session_chair',
                'organization' => 'IEEE',
                'role' => 'Session Chair',
                'year' => 2024,
                'sort_order' => 1,
                'is_visible' => true,
            ]
        );

        ResearchActivity::query()->updateOrCreate(
            ['title' => 'Reviewer, Web of Science Indexed Journals'],
            [
                'type' => 'reviewer',
                'organization' => 'Clarivate Web of Science',
                'role' => 'Peer Reviewer',
                'year' => 2020,
                'year_end' => 'Present',
                'url' => 'https://publons.com/researcher/0000000',
                'sort_order' => 2,
                'is_visible' => true,
            ]
        );

        Student::query()->updateOrCreate(
            ['name' => 'Sample PhD Candidate', 'thesis_title' => 'Deep Learning for Epilepsy Seizure Prediction'],
            [
                'status' => 'in_progress',
                'degree' => 'Ph.D. Computer Science',
                'co_supervisors' => 'Dr. Co-Supervisor',
                'start_year' => 2023,
                'sort_order' => 1,
                'is_visible' => true,
            ]
        );

        Student::query()->updateOrCreate(
            ['name' => 'Sample MS Graduate', 'thesis_title' => 'Customer Churn Prediction using Ensemble Methods'],
            [
                'status' => 'completed',
                'degree' => 'MS Computer Science',
                'completion_year' => 2022,
                'sort_order' => 2,
                'is_visible' => true,
            ]
        );

        TrainingSession::query()->updateOrCreate(
            ['title' => 'Machine Learning Using Python'],
            [
                'type' => 'workshop',
                'event_name' => 'Faculty Development Program',
                'organization' => 'IMSciences',
                'role' => 'Resource Person',
                'year' => 2024,
                'sort_order' => 1,
                'is_visible' => true,
            ]
        );

        TrainingSession::query()->updateOrCreate(
            ['title' => 'Big Data Analytics using PySpark'],
            [
                'type' => 'summer_school',
                'event_name' => 'Summer School on ML',
                'role' => 'Facilitator',
                'year' => 2023,
                'sort_order' => 2,
                'is_visible' => true,
            ]
        );

        $album = GalleryAlbum::query()->updateOrCreate(
            ['title' => 'Academic Events'],
            [
                'description' => 'Conference presentations, workshops, and academic gatherings.',
                'sort_order' => 1,
                'is_visible' => true,
            ]
        );

        GalleryImage::query()->updateOrCreate(
            ['gallery_album_id' => $album->id, 'title' => 'Conference Presentation'],
            [
                'image_path' => 'gallery/placeholder.jpg',
                'caption' => 'Presenting research at an international conference',
                'sort_order' => 1,
                'is_featured' => true,
            ]
        );

        SiteSetting::set('accent_color', '#5B2C6F');
        SiteSetting::set('secondary_color', '#C17AA8');
        SiteSetting::set('surface_color', '#FFF9F5');
        SiteSetting::set('surface_muted_color', '#F5EBE8');
        SiteSetting::set('meta_description', 'Academic portfolio showcasing research publications, students, and professional activities.');
        SiteSetting::set('contact_message', 'Feel free to reach out for research collaboration, supervision inquiries, or speaking engagements.');

        $products = [
            [
                'name' => 'IMDigital',
                'tagline' => 'Digital Learning & Innovation',
                'description' => 'IMDigital delivers digital learning solutions, training kits, and innovation-driven educational resources for academia and industry.',
                'url' => 'https://imdigital.example.com',
                'sort_order' => 1,
            ],
            [
                'name' => 'ResearchChair',
                'tagline' => 'Research Leadership Platform',
                'description' => 'ResearchChair supports conference session management, research committee workflows, and academic event coordination.',
                'url' => 'https://researchchair.example.com',
                'sort_order' => 2,
            ],
        ];

        foreach ($products as $product) {
            ShowcaseProduct::query()->updateOrCreate(
                ['name' => $product['name']],
                array_merge($product, ['is_visible' => true])
            );
        }
    }
}
