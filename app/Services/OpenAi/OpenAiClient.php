<?php

namespace App\Services\OpenAi;

use App\Services\Llm\LlmClient;

/**
 * @deprecated Use App\Services\Llm\LlmClient
 */
class OpenAiClient
{
    public function __construct(protected LlmClient $llm) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, model: string, prompt_tokens: int|null, completion_tokens: int|null, provider?: string}
     */
    public function chat(array $messages, ?int $maxTokens = null): array
    {
        return $this->llm->chat($messages, LlmClient::PROVIDER_OPENAI, $maxTokens);
    }
}
