<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected string $baseUrl;
    protected ?string $readToken;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.tmdb.base_url', 'https://api.themoviedb.org/3'), '/');
        $this->readToken = config('services.tmdb.read_access_token');
        $this->apiKey = config('services.tmdb.api_key');
    }

    /**
     * Generic GET request to TMDB.
     * Uses Bearer token if available, otherwise falls back to api_key query param.
     * Throws an exception on non-2xx responses.
     *
     * @param string $path e.g. 'movie/550'
     * @param array $query
     * @return array|null
     */
    public function get(string $path, array $query = []): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $client = Http::acceptJson();

        if ($this->readToken) {
            $client = $client->withToken($this->readToken);
        }

        // If no bearer token, add api_key to query
        if (!$this->readToken && $this->apiKey) {
            $query = array_merge(['api_key' => $this->apiKey], $query);
        }

        $response = $client->get($url, $query);

        // Throw for HTTP errors so callers can catch exceptions or let them bubble up
        $response->throw();

        return $response->json();
    }

    /**
     * Convenience: get movie by TMDB id
     */
    public function getMovie(int $id, array $append = []): ?array
    {
        $path = sprintf('movie/%d', $id);
        if (!empty($append)) {
            $query = ['append_to_response' => implode(',', $append)];
        } else {
            $query = [];
        }

        return $this->get($path, $query);
    }

    /**
     * Check whether a YouTube video key is embeddable by using YouTube's oEmbed endpoint.
     * Returns true when the oEmbed request succeeds (200) which generally indicates the video
     * can be embedded in an iframe. We keep this lightweight and tolerant: any network error
     * results in false so we don't accidentally enable a non-embeddable video.
     */
    public function isYoutubeEmbeddable(string $key): bool
    {
        // Use the public oEmbed endpoint — returns JSON when embeddable
        $watchUrl = 'https://www.youtube.com/watch?v=' . $key;
        $oembed = 'https://www.youtube.com/oembed?format=json&url=' . urlencode($watchUrl);

        try {
            $resp = Http::timeout(5)->get($oembed);
            return $resp->successful();
        } catch (\Exception $e) {
            // treat any exception as non-embeddable (fail-safe)
            logger()->debug('YouTube oEmbed check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build a privacy-enhanced YouTube embed URL (youtube-nocookie) with sane params.
     */
    public function buildYoutubeEmbedUrl(string $key): string
    {
        return 'https://www.youtube-nocookie.com/embed/' . $key . '?rel=0&modestbranding=1&controls=1';
    }
}
