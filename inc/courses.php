<?php
// The course catalogue. Prices are set HERE (on the server) — the browser can't change them.
// Access links (joining links) are secret and live in the server settings file, not here:
//   ACCESS_LINK_<SLUG in capitals, - replaced by _>   e.g. ACCESS_LINK_AI_TOOLKIT=https://...

declare(strict_types=1);

const COURSES = [
    'ai-toolkit' => [
        'title'    => 'AI Toolkit',
        'tagline'  => 'Let AI simplify your everyday tasks',
        'format'   => 'Live',
        'price'    => 1100, // paise (₹11)
        'schedule' => 'Live online session — date & time shared by email after enrolment',
        'summary'  => 'A practical, beginner-friendly live session on using today’s AI assistants to save time on everyday work — no technical background needed.',
        'learn'    => [
            'Write emails, messages and documents faster with AI',
            'Summarise long articles, PDFs and notes in seconds',
            'Plan your day, study schedule or project with AI help',
            'Ask better questions (prompts) to get useful answers',
            'Use AI safely: checking facts and protecting your data',
        ],
        'for'      => ['Students', 'Working professionals', 'Teachers', 'Small business owners'],
    ],
];

function course(string $slug): ?array {
    return isset(COURSES[$slug]) ? COURSES[$slug] + ['slug' => $slug] : null;
}

function course_access_link(string $slug): string {
    return env('ACCESS_LINK_' . strtoupper(str_replace('-', '_', $slug)));
}
