<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Blog post title templates for realistic data.
     *
     * @var array<string>
     */
    protected array $titleTemplates = [
        'How I Learned to {verb} in {timeframe}',
        'The Ultimate Guide to {topic}',
        'Why {thing} Changed My {noun} Forever',
        '{number} Things I Wish I Knew About {topic} Before Starting',
        'My Experience with {thing}: A Complete Review',
        'Building {project}: Lessons Learned',
        'A Beginner\'s Guide to {topic}',
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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->getWeightedStatus();

        $title = $this->generateTitle();

        return [
            'title' => $title,
            'summary' => $this->faker->optional(0.8)->paragraph(2),
            'content' => $this->generateMarkdownContent(),
            'status' => $status,
            'published_at' => $this->getPublishedAtForStatus($status),
            'featured_image' => $this->faker->optional(0.6)->randomElement([
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200',
                'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200',
                'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1200',
                'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1200',
                'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1200',
            ]),
            'meta' => $this->generateMeta($title),
        ];
    }

    /**
     * Generate a realistic blog post title.
     */
    protected function generateTitle(): string
    {
        $template = $this->faker->randomElement($this->titleTemplates);

        $replacements = [
            '{verb}' => $this->faker->randomElement(['Code', 'Write', 'Build', 'Create', 'Learn', 'Think', 'Design', 'Ship', 'Launch']),
            '{timeframe}' => $this->faker->randomElement(['30 Days', 'a Week', 'One Year', 'Six Months', '2024', 'My Twenties']),
            '{topic}' => $this->faker->randomElement(['Laravel', 'Remote Work', 'Minimalism', 'Web Development', 'Writing', 'Productivity', 'Open Source', 'Indie Hacking']),
            '{thing}' => $this->faker->randomElement(['Solo Travel', 'Working From Home', 'My First App', 'Journaling', 'Open Source', 'Side Projects', 'Meditation']),
            '{noun}' => $this->faker->randomElement(['Life', 'Workflow', 'Perspective', 'Approach', 'Mindset', 'Career']),
            '{number}' => $this->faker->randomElement(['5', '7', '10', '3', '12']),
            '{project}' => $this->faker->randomElement(['a SaaS', 'My Blog', 'This App', 'a Side Project', 'My Portfolio']),
            '{adjective}' => $this->faker->randomElement(['Honest', 'Uncomfortable', 'Surprising', 'Hard', 'Beautiful']),
            '{negative}' => $this->faker->randomElement(['Burning Out', 'Giving Up', 'Overthinking', 'Procrastinating']),
            '{activity}' => $this->faker->randomElement(['Coding', 'Writing', 'Traveling', 'Working Out', 'Reading']),
            '{alternative}' => $this->faker->randomElement(['Building', 'Creating', 'Exploring', 'Resting']),
            '{place}' => $this->faker->randomElement(['Kyoto', 'Portland', 'Iceland', 'New Zealand', 'the PNW']),
            '{outcome}' => $this->faker->randomElement(['Stay Focused', 'Ship Faster', 'Write Better', 'Find Clarity']),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get status with weighted probability (60% published, 30% draft, 10% hidden).
     */
    protected function getWeightedStatus(): string
    {
        $rand = $this->faker->randomFloat(2, 0, 1);

        return match (true) {
            $rand <= 0.6 => 'published',
            $rand <= 0.9 => 'draft',
            default => 'hidden',
        };
    }

    /**
     * Generate realistic markdown content.
     */
    protected function generateMarkdownContent(): string
    {
        $sections = [];

        // Introduction
        $sections[] = $this->faker->paragraph(3);

        // Main sections with headers
        $numSections = $this->faker->numberBetween(2, 4);

        for ($i = 0; $i < $numSections; $i++) {
            $sections[] = '';
            $sections[] = '## '.$this->faker->randomElement([
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
            $numParagraphs = $this->faker->numberBetween(1, 3);
            for ($p = 0; $p < $numParagraphs; $p++) {
                $paragraph = $this->faker->paragraph($this->faker->numberBetween(3, 6));

                // Occasionally add emphasis
                if ($this->faker->boolean(30)) {
                    $words = explode(' ', $paragraph);
                    $emphasisIndex = $this->faker->numberBetween(0, count($words) - 2);
                    $words[$emphasisIndex] = '**'.$words[$emphasisIndex].'**';
                    $paragraph = implode(' ', $words);
                }

                $sections[] = $paragraph;
            }

            // Occasionally add a list
            if ($this->faker->boolean(40)) {
                $sections[] = '';
                $isOrdered = $this->faker->boolean(30);
                $numItems = $this->faker->numberBetween(3, 5);

                for ($li = 0; $li < $numItems; $li++) {
                    $item = $this->faker->sentence(6);

                    if ($isOrdered) {
                        $sections[] = ($li + 1).'. '.$item;
                    } else {
                        $sections[] = '- '.$item;
                    }
                }
            }

            // Occasionally add a code block
            if ($this->faker->boolean(20)) {
                $sections[] = '';
                $sections[] = '```php';
                $sections[] = '// Example code';
                $sections[] = '$'.$this->faker->word.' = '."'".$this->faker->word."';";
                $sections[] = '';
                $sections[] = 'return $'.$this->faker->word.';';
                $sections[] = '```';
            }

            // Occasionally add a link
            if ($this->faker->boolean(30)) {
                $sections[] = '';
                $sections[] = 'Read more about ['.$this->faker->words(3, true).']('.$this->faker->url().').';
            }
        }

        // Conclusion
        $sections[] = '';
        $sections[] = '## '.$this->faker->randomElement(['Wrapping Up', 'In Summary', 'Takeaways']);
        $sections[] = '';
        $sections[] = $this->faker->paragraph(2);

        return implode("\n", $sections);
    }

    /**
     * Get published_at based on status.
     */
    protected function getPublishedAtForStatus(string $status): ?\DateTime
    {
        return match ($status) {
            'published' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'draft', 'hidden' => $this->faker->optional(0.3)->dateTimeBetween('-6 months', '+6 months'),
            default => null,
        };
    }

    /**
     * Generate meta array for SEO.
     *
     * @return array<string, mixed>|null
     */
    protected function generateMeta(string $title): ?array
    {
        if ($this->faker->boolean(40)) {
            return null;
        }

        return [
            'meta_title' => $this->faker->optional(0.5)->words(6, true) ?? $title,
            'meta_description' => $this->faker->optional(0.6)->sentence(12),
            'og_image' => $this->faker->optional(0.7)->url(),
        ];
    }

    /**
     * State for published articles.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * State for draft articles.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * State for hidden articles.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'hidden',
            'published_at' => null,
        ]);
    }
}
