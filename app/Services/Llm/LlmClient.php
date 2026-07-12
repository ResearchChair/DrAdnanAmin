<?php

namespace App\Services\Llm;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class LlmClient
{
    public const PROVIDER_AUTO = 'auto';

    public const PROVIDER_OPENAI = 'openai';

    public const PROVIDER_GROQ = 'groq';

    /**
     * @return array<string, string> provider => label (only configured keys)
     */
    public function availableProviderOptions(bool $includeAuto = true): array
    {
        $options = [];
        if ($includeAuto) {
            $configured = $this->configuredProviders();
            if (count($configured) > 1) {
                $options[self::PROVIDER_AUTO] = 'Auto (failover if quota / errors)';
            } elseif (count($configured) === 1) {
                $only = $configured[0];
                $options[self::PROVIDER_AUTO] = 'Auto → '.$this->label($only);
            }
        }

        foreach ($this->allProviderKeys() as $key) {
            if ($this->isConfigured($key)) {
                $options[$key] = $this->label($key).' ('.$this->model($key).')';
            }
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public function configuredProviders(): array
    {
        return array_values(array_filter(
            $this->allProviderKeys(),
            fn (string $key): bool => $this->isConfigured($key)
        ));
    }

    public function statusSummary(): string
    {
        $parts = [];
        foreach ($this->allProviderKeys() as $key) {
            $parts[] = $this->isConfigured($key)
                ? $this->label($key).': OK ('.$this->model($key).')'
                : $this->label($key).': missing API key';
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{
     *   content: string,
     *   model: string,
     *   provider: string,
     *   attempted_providers: list<string>,
     *   failover: bool,
     *   prompt_tokens: int|null,
     *   completion_tokens: int|null
     * }
     */
    public function chat(array $messages, string $provider = self::PROVIDER_AUTO, ?int $maxTokens = null): array
    {
        $chain = $this->resolveProviderChain($provider);
        if ($chain === []) {
            throw new RuntimeException('No LLM providers are configured. Add OPENAI_API_KEY and/or GROQ_API_KEY to .env.');
        }

        $errors = [];
        $attempted = [];

        foreach ($chain as $index => $providerKey) {
            if ($this->isCoolingDown($providerKey) && count($chain) > 1) {
                $errors[] = $this->label($providerKey).': cooling down after quota/rate-limit';
                continue;
            }

            $attempted[] = $providerKey;

            try {
                $result = $this->chatWithProvider($providerKey, $messages, $maxTokens);
                $result['provider'] = $providerKey;
                $result['attempted_providers'] = $attempted;
                $result['failover'] = $index > 0 || count($attempted) > 1;

                return $result;
            } catch (RuntimeException $e) {
                $errors[] = $this->label($providerKey).': '.$e->getMessage();

                if ($this->isQuotaOrRateLimitError($e) || $this->isTransientProviderError($e)) {
                    $this->markCoolDown($providerKey);
                    continue;
                }

                // Non-recoverable for this provider (auth, bad request) — still try next if auto/chain
                if (count($chain) > 1) {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('All LLM providers failed. '.implode(' | ', $errors));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, model: string, prompt_tokens: int|null, completion_tokens: int|null}
     */
    protected function chatWithProvider(string $provider, array $messages, ?int $maxTokens = null): array
    {
        $config = $this->providerConfig($provider);
        $apiKey = $config['api_key'] ?? null;
        if (! filled($apiKey)) {
            throw new RuntimeException($this->label($provider).' API key is not configured.');
        }

        $payload = [
            'model' => $config['model'],
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => $maxTokens ?? (int) ($config['max_tokens'] ?? 3500),
        ];

        try {
            $response = Http::baseUrl(rtrim((string) $config['base_url'], '/'))
                ->withToken($apiKey)
                ->acceptJson()
                ->timeout((int) ($config['timeout'] ?? 90))
                ->post('/chat/completions', $payload)
                ->throw()
                ->json();
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response?->json('error.message')
                ?? $e->response?->body()
                ?? $e->getMessage();
            $message = is_string($body) ? Str::limit(trim($body), 300) : 'HTTP '.$status;

            throw new RuntimeException($message.' (HTTP '.($status ?? 'n/a').')', previous: $e);
        }

        $content = trim((string) data_get($response, 'choices.0.message.content', ''));
        if ($content === '') {
            throw new RuntimeException('Empty response from model.');
        }

        return [
            'content' => $content,
            'model' => (string) data_get($response, 'model', $config['model']),
            'prompt_tokens' => data_get($response, 'usage.prompt_tokens'),
            'completion_tokens' => data_get($response, 'usage.completion_tokens'),
        ];
    }

    /**
     * @return list<string>
     */
    protected function resolveProviderChain(string $provider): array
    {
        $provider = strtolower(trim($provider));

        if ($provider !== self::PROVIDER_AUTO && $provider !== '') {
            if (! in_array($provider, $this->allProviderKeys(), true)) {
                throw new RuntimeException('Unknown LLM provider: '.$provider);
            }
            if (! $this->isConfigured($provider)) {
                throw new RuntimeException($this->label($provider).' is not configured (missing API key).');
            }

            // Manual pick: try chosen first, then other configured providers on quota failure
            $rest = array_values(array_filter(
                $this->failoverOrderConfigured(),
                fn (string $key): bool => $key !== $provider
            ));

            return array_merge([$provider], $rest);
        }

        return $this->failoverOrderConfigured();
    }

    /**
     * @return list<string>
     */
    protected function failoverOrderConfigured(): array
    {
        $order = config('llm.failover_order', ['openai', 'groq']);
        if (! is_array($order) || $order === []) {
            $order = ['openai', 'groq'];
        }

        return array_values(array_filter(
            $order,
            fn ($key): bool => is_string($key) && $this->isConfigured($key)
        ));
    }

    /**
     * @return list<string>
     */
    protected function allProviderKeys(): array
    {
        return array_keys(config('llm.providers', []));
    }

    protected function isConfigured(string $provider): bool
    {
        return filled(data_get(config('llm.providers'), $provider.'.api_key'));
    }

    protected function label(string $provider): string
    {
        return (string) data_get(config('llm.providers'), $provider.'.label', ucfirst($provider));
    }

    protected function model(string $provider): string
    {
        return (string) data_get(config('llm.providers'), $provider.'.model', 'n/a');
    }

    /**
     * @return array<string, mixed>
     */
    protected function providerConfig(string $provider): array
    {
        $config = config('llm.providers.'.$provider);
        if (! is_array($config)) {
            throw new RuntimeException('Missing config for provider: '.$provider);
        }

        return $config;
    }

    protected function isQuotaOrRateLimitError(RuntimeException $e): bool
    {
        $message = Str::lower($e->getMessage());
        $needles = [
            'rate limit',
            'rate_limit',
            'quota',
            'insufficient_quota',
            'billing',
            'tokens',
            'too many requests',
            '429',
            'credit',
            'exceeded',
            'capacity',
            'overloaded',
        ];

        foreach ($needles as $needle) {
            if (Str::contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function isTransientProviderError(RuntimeException $e): bool
    {
        $message = Str::lower($e->getMessage());

        return Str::contains($message, ['500', '502', '503', '504', 'timeout', 'timed out']);
    }

    protected function cooldownKey(string $provider): string
    {
        return 'llm:cooldown:'.$provider;
    }

    protected function isCoolingDown(string $provider): bool
    {
        return Cache::has($this->cooldownKey($provider));
    }

    protected function markCoolDown(string $provider): void
    {
        Cache::put(
            $this->cooldownKey($provider),
            true,
            now()->addSeconds((int) config('llm.cooldown_seconds', 300))
        );
    }
}
