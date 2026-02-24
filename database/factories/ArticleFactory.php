<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Status;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
final class ArticleFactory extends Factory
{
    /**
     * Sequence counter for unique slugs.
     */
    private static int $sequence = 0;

    /**
     * Blog post title templates for realistic data.
     *
     * @var array<string>
     */
    private array $titleTemplates = [
        'How I Learned to {verb} in {timeframe}',
        'The Ultimate Guide to {topic}',
        'Why {thing} Changed My {noun} Forever',
        '{number} Things I Wish I Knew About {topic} Before Starting',
        'My Experience with {thing}: A Complete Review',
        'Building {project}: Lessons Learned',
        "A Beginner's Guide to {topic}",
        'The {adjective} Truth About {thing}',
        'How to {verb} Without {negative}',
        'What {number} Days of {activity} Taught Me',
        'Exploring {place}: Hidden Gems and Local Favorites',
        'Why I Stopped {activity} and Started {alternative}',
        'The Future of {topic}: Trends to Watch',
        'Reflections on {timeframe} of {thing}',
        'How {thing} Helped Me {outcome}',
    ];

    /**
     * Reset the sequence counter (useful for tests).
     */
    public static function resetSequence(): void
    {
        self::$sequence = 0;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        self::$sequence++;

        $status = $this->getWeightedStatus();

        $title = $this->generateTitle();

        // Append sequence to title to ensure unique slugs
        if (self::$sequence > 1) {
            $title .= ' '.self::$sequence;
        }

        return [
            'user_id' => User::first()?->id ?? User::factory(),
            'title' => $title,
            'summary' => fake()->optional(0.8)->paragraph(2),
            'content' => $this->generateMarkdownContent(),
            'status' => $status,
            'published_at' => $this->getPublishedAtForStatus($status),
            'meta' => $this->generateMeta($title),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        // No automatic image attachment - use explicit states instead
        return $this;
    }

    /**
     * State for published articles.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Status::Published,
            'published_at' => now()->startOfSecond(),
        ]);
    }

    /**
     * State for draft articles.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Status::Draft,
            'published_at' => fake()->optional(0.3)->dateTimeBetween('-6 months', '+6 months'),
        ]);
    }

    /**
     * State for articles with a draft snapshot.
     *
     * @param  array<string, mixed>  $draftData
     */
    public function withDraft(array $draftData = []): static
    {
        return $this->state(fn (array $attributes): array => [
            'draft' => array_merge([
                'title' => 'Draft Title',
                'content' => 'Draft content here',
            ], $draftData),
        ]);
    }

    /**
     * Generate a realistic blog post title.
     */
    protected function generateTitle(): string
    {
        $template = \Illuminate\Support\Arr::random($this->titleTemplates);

        $replacements = [
            '{verb}' => \Illuminate\Support\Arr::random(['Code', 'Write', 'Build', 'Create', 'Learn', 'Think', 'Design', 'Ship', 'Launch']),
            '{timeframe}' => \Illuminate\Support\Arr::random(['30 Days', 'a Week', 'One Year', 'Six Months', '2024', 'My Twenties']),
            '{topic}' => \Illuminate\Support\Arr::random(['Laravel', 'Remote Work', 'Minimalism', 'Web Development', 'Writing', 'Productivity', 'Open Source', 'Indie Hacking']),
            '{thing}' => \Illuminate\Support\Arr::random(['Solo Travel', 'Working From Home', 'My First App', 'Journaling', 'Open Source', 'Side Projects', 'Meditation']),
            '{noun}' => \Illuminate\Support\Arr::random(['Life', 'Workflow', 'Perspective', 'Approach', 'Mindset', 'Career']),
            '{number}' => \Illuminate\Support\Arr::random(['5', '7', '10', '3', '12']),
            '{project}' => \Illuminate\Support\Arr::random(['a SaaS', 'My Blog', 'This App', 'a Side Project', 'My Portfolio']),
            '{adjective}' => \Illuminate\Support\Arr::random(['Honest', 'Uncomfortable', 'Surprising', 'Hard', 'Beautiful']),
            '{negative}' => \Illuminate\Support\Arr::random(['Burning Out', 'Giving Up', 'Overthinking', 'Procrastinating']),
            '{activity}' => \Illuminate\Support\Arr::random(['Coding', 'Writing', 'Traveling', 'Working Out', 'Reading']),
            '{alternative}' => \Illuminate\Support\Arr::random(['Building', 'Creating', 'Exploring', 'Resting']),
            '{place}' => \Illuminate\Support\Arr::random(['Kyoto', 'Portland', 'Iceland', 'New Zealand', 'the PNW']),
            '{outcome}' => \Illuminate\Support\Arr::random(['Stay Focused', 'Ship Faster', 'Write Better', 'Find Clarity']),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get status with weighted probability (80% published, 20% draft).
     */
    protected function getWeightedStatus(): Status
    {
        $rand = fake()->randomFloat(2, 0, 1);

        return match (true) {
            $rand <= 0.8 => Status::Published,
            default => Status::Draft,
        };
    }

    /**
     * Generate realistic markdown content.
     */
    protected function generateMarkdownContent(): string
    {
        $sections = [];

        // Introduction
        $sections[] = fake()->paragraph(3);

        // Main sections with headers
        $numSections = fake()->numberBetween(2, 4);

        for ($i = 0; $i < $numSections; $i++) {
            $sections[] = '';
            $sections[] = '## '.\Illuminate\Support\Arr::random([
                'Getting Started',
                'The Journey',
                'What I Learned',
                'The Process',
                'Key Insights',
                'Challenges Faced',
                'The Results',
                'Looking Back',
                'Taking Action',
                'Final Thoughts',
            ]);
            $sections[] = '';

            // Add paragraphs
            $numParagraphs = fake()->numberBetween(1, 3);
            for ($p = 0; $p < $numParagraphs; $p++) {
                $paragraph = fake()->paragraph(fake()->numberBetween(3, 6));

                // Occasionally add emphasis
                if (fake()->boolean(30)) {
                    $words = explode(' ', $paragraph);
                    $emphasisIndex = fake()->numberBetween(0, count($words) - 2);
                    $words[$emphasisIndex] = '**'.$words[$emphasisIndex].'**';
                    $paragraph = implode(' ', $words);
                }

                $sections[] = $paragraph;
            }

            // Occasionally add a list
            if (fake()->boolean(40)) {
                $sections[] = '';
                $isOrdered = fake()->boolean(30);
                $numItems = fake()->numberBetween(3, 5);

                for ($li = 0; $li < $numItems; $li++) {
                    $item = fake()->sentence(6);

                    $sections[] = $isOrdered ? ($li + 1).'. '.$item : '- '.$item;
                }
            }

            // Occasionally add a code block
            if (fake()->boolean(20)) {
                $sections[] = '';
                $sections[] = '```php';
                $sections[] = '// Example code';
                $sections[] = '$'.fake()->word.' = '."'".fake()->word."';";
                $sections[] = '';
                $sections[] = 'return $'.fake()->word.';';
                $sections[] = '```';
            }

            // Occasionally add a link
            if (fake()->boolean(30)) {
                $sections[] = '';
                $sections[] = 'Read more about ['.fake()->words(3, true).']('.fake()->url().').';
            }
        }

        // Conclusion
        $sections[] = '';
        $sections[] = '## '.\Illuminate\Support\Arr::random(['Wrapping Up', 'In Summary', 'Takeaways']);
        $sections[] = '';
        $sections[] = fake()->paragraph(2);

        return implode("\n", $sections);
    }

    /**
     * Get published_at based on status.
     */
    protected function getPublishedAtForStatus(Status $status): ?DateTimeImmutable
    {
        $date = match ($status) {
            Status::Published => fake()->dateTimeBetween('-1 year', 'now'),
            Status::Draft => fake()->optional(0.3)?->dateTimeBetween('-6 months', '+6 months'),
        };

        if ($date === null) {
            return null;
        }

        return DateTimeImmutable::createFromMutable($date);
    }

    /**
     * Generate meta array for SEO.
     *
     * @return array<string, mixed>|null
     */
    protected function generateMeta(string $title): ?array
    {
        if (fake()->boolean(40)) {
            return null;
        }

        return [
            'meta_title' => fake()->optional(0.5)->words(6, true) ?? $title,
            'meta_description' => fake()->optional(0.6)->sentence(12),
            'og_image' => fake()->optional(0.7)->url(),
        ];
    }
}
