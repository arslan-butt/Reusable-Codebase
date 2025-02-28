<?php

namespace App\Services\Api;

use App\Services\BaseService;
use App\Models\Base\User\OAuthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Validator;
use Closure;

/**
 * Class LegacyApiService
 *
 * This class provides a service for making flexible API requests to a legacy API.
 * It supports various HTTP methods, custom headers, and parameters, with retry logic.
 *
 * @package App\Services\Api
 */
class LegacyApiService extends BaseService
{
    protected int $maxRetries = 3; // Default maximum number of retries
    protected int $retryDelayMilliseconds = 1000; // Default delay between retries in milliseconds
    protected string $apiVersion = 'v1'; // Default API version
    protected bool $shouldRetry = false; // Whether to retry failed requests

    /**
     * Get the validation rules that apply to the service.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'method' => 'required|in:post,get,put,delete',
            'headers' => 'array',
            'params' => 'array',
            'resource' => 'required|string',
            'debug' => 'boolean',
            'apiVersion' => 'string',
            'shouldRetry' => 'boolean',
            'maxRetries' => 'integer',
            'retryDelayMilliseconds' => 'integer',
        ];
    }

    /**
     * Execute the API request based on the provided data.
     *
     * @param array<string, mixed> $data An associative array containing API request parameters.
     * @param Closure|null $retryCondition A closure to determine if a retry is needed.
     * @param array<array<string, mixed>> $files Files to send with the request.
     * @return Response
     */
    public function execute(array $data, Closure $retryCondition = null, array $files = []): Response
    {
        $this->validate($data);

        $apiUrl = $this->getApiUrl($data);
        $method = $data['method'];
        $params = $this->getParams($data);
        $headers = $this->getHeaders($data);
        $debug = $data['debug'] ?? false;
        $shouldRetry = $data['shouldRetry'] ?? $this->shouldRetry;

        // Remove Content-Type header if files are being sent
        if (!empty($files)) {
            unset($headers['Content-Type']);
        }

        $response = Http::withOptions(['debug' => $debug])
            ->withHeaders($headers);

        // Attach files if provided
        foreach ($files as $file) {
            $response->attach($file['title'], file_get_contents($file['file']->getRealPath()), $file['name']);
        }

        // Make the API request
        $response = $response->{$method}($apiUrl, $params);

        // Retry logic
        if ($shouldRetry && $retryCondition !== null && $retryCondition($response, $data)) {
            return $this->retry($data, $retryCondition);
        }

        return $response;
    }

    /**
     * Get the API URL based on the provided data.
     *
     * @param array<string, mixed> $data An associative array containing data for constructing the API URL.
     * @return string
     */
    protected function getApiUrl(array $data): string
    {
        return $data['apiUrl'] ?? config('app.old_api_url') . "/api/{$this->getApiVersion($data)}{$data['resource']}";
    }

    /**
     * Get the API version from the data or use the default.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    protected function getApiVersion(array $data): string
    {
        return $data['apiVersion'] ?? $this->apiVersion;
    }

    /**
     * Get the parameters for the API request.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getParams(array $data): array
    {
        return $data['params'] ?? [];
    }

    /**
     * Get the headers for the API request, merging custom headers with default headers.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    protected function getHeaders(array $data): array
    {
        $headers = $this->getDefaultHeaders();

        if (isset($data['headers']) && is_array($data['headers'])) {
            $headers = array_merge($headers, $data['headers']);
        }

        return $headers;
    }

    /**
     * Get the default headers for API requests.
     *
     * @return array<string, string>
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'authorization' => 'OAuth2 ' . OAuthToken::getUserOAuthToken(),
            'Content-Type' => 'application/json',
            'X-Device-Id' => 'web',
        ];
    }

    /**
     * Retry the API request based on the provided data and retry condition.
     *
     * @param array<string, mixed> $data
     * @param Closure $retryCondition
     * @return Response
     */
    protected function retry(array $data, Closure $retryCondition): Response
    {
        $maxRetries = $this->getMaxRetries($data);
        $retryDelayMilliseconds = $this->getRetryDelayMilliseconds($data);
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $response = $this->execute($data);

            if ($retryCondition($response, $data)) {
                return $response;
            }

            $retryCount++;
            if ($retryCount < $maxRetries) {
                usleep($retryDelayMilliseconds * 1000);
            }
        }

        return Response::make('All retries failed', 500);
    }

    /**
     * Get the maximum number of retries from the data or use the default.
     *
     * @param array<string, mixed> $data
     * @return int
     */
    protected function getMaxRetries(array $data): int
    {
        return $data['maxRetries'] ?? $this->maxRetries;
    }

    /**
     * Get the retry delay in milliseconds from the data or use the default.
     *
     * @param array<string, mixed> $data
     * @return int
     */
    protected function getRetryDelayMilliseconds(array $data): int
    {
        return $data['retryDelayMilliseconds'] ?? $this->retryDelayMilliseconds;
    }
}
