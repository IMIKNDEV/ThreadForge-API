<?php

namespace App\Jobs;

use App\AI\Schemas\PostGenerationSchema;
use App\Models\Post;
use App\Models\RawContent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Laravel\Ai\StructuredAnonymousAgent;

class GeneratePostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public RawContent $rawContent) {}

    public function backoff(): array
    {
        return [10, 30];
    }

    public function handle(): void
    {
        $rawContent = $this->rawContent->load('blueprint');
        $blueprint = $rawContent->blueprint;

        $systemPrompt = sprintf(
            "You are a social media post generator. Follow these campaign rules exactly:\n".
            "- Tone: %s\n".
            "- Max hashtags: %d\n".
            "- Max characters: %d\n".
            "- Banned words: %s\n".
            "- Extra rules: %s\n\n".
            "Generate a post based on the provided content. ".
            "The hook must not exceed %d characters. ".
            "The technical_readability_score must be an integer between 0 and 100. ".
            "Return the structured data exactly as specified by the schema.",
            $blueprint->tone,
            $blueprint->max_hashtag,
            $blueprint->max_characters,
            $blueprint->banned_word ?? 'none',
            $blueprint->extra_rules ?? 'none',
            $blueprint->max_characters,
        );

        $agent = new StructuredAnonymousAgent(
            instructions: $systemPrompt,
            messages: [],
            tools: [],
            schema: fn ($schema) => PostGenerationSchema::definition($schema),
        );

        $response = $agent->prompt(
            prompt: sprintf(
                "Generate a social media post from this content:\n\n%s",
                $rawContent->body,
            ),
        );

        $data = $response->toArray();

        $technicalScore = (int) ($data['technical_readability_score'] ?? 0);
        $technicalScore = max(0, min(100, $technicalScore));

        Post::create([
            'raw_content_id' => $rawContent->id,
            'hook' => $data['hook'] ?? '',
            'body_points' => $data['body_points'] ?? [],
            'technical_readability_score' => $technicalScore,
            'suggested_hashtags' => $data['suggested_hashtags'] ?? [],
            'tone_compliance_justification' => $data['tone_compliance_justification'] ?? '',
            'statut_publication' => 'draft',
        ]);

        $rawContent->update(['status' => 'genere']);
    }

    public function failed(\Throwable $exception): void
    {
        $this->rawContent->update(['status' => 'erreur']);
    }
}
