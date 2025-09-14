<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class FastElasticsearchService
{
    protected $client;
    protected $host;
    protected $index;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 5.0,
            'connect_timeout' => 2.0,
        ]);
        $this->host = config('scout.elasticsearch.hosts.0', 'http://145.223.98.97:9201');
        $this->index = 'pages';
    }

    /**
     * Perform fast search directly with Elasticsearch
     *
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        try {
            $searchBody = $this->buildSearchBody($query, $filters, $page, $perPage);
            
            $response = $this->client->post($this->host . '/' . $this->index . '/_search', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $searchBody
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            return $this->formatSearchResults($result, $page, $perPage);

        } catch (RequestException $e) {
            throw new \Exception('Elasticsearch search failed: ' . $e->getMessage());
        }
    }

    /**
     * Build Elasticsearch search body
     *
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    protected function buildSearchBody(string $query, array $filters, int $page, int $perPage): array
    {
        $from = ($page - 1) * $perPage;
        
        $searchBody = [
            'from' => $from,
            'size' => $perPage,
            '_source' => [
                'id',
                'content',
                'page_number',
                'book_id',
                'book_title',
                'author_names',
                'book_section_id'
            ],
            'query' => [
                'bool' => [
                    'must' => [],
                    'filter' => []
                ]
            ],
            'highlight' => [
                'fields' => [
                    'content' => [
                        'fragment_size' => 200,
                        'number_of_fragments' => 1
                    ]
                ]
            ]
        ];

        // Add main search query
        if (!empty($query)) {
            $searchBody['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['content^2', 'book_title^1.5', 'author_names'],
                    'analyzer' => 'arabic_analyzer',
                    'fuzziness' => 'AUTO'
                ]
            ];
        } else {
            $searchBody['query']['bool']['must'][] = [
                'match_all' => new \stdClass()
            ];
        }

        // Add filters
        if (!empty($filters['author_id'])) {
            $searchBody['query']['bool']['filter'][] = [
                'terms' => ['author_ids' => [(int) $filters['author_id']]]
            ];
        }

        if (!empty($filters['section_id'])) {
            $searchBody['query']['bool']['filter'][] = [
                'term' => ['book_section_id' => (int) $filters['section_id']]
            ];
        }

        return $searchBody;
    }

    /**
     * Format Elasticsearch results
     *
     * @param array $esResult
     * @param int $page
     * @param int $perPage
     * @return array
     */
    protected function formatSearchResults(array $esResult, int $page, int $perPage): array
    {
        $hits = $esResult['hits']['hits'] ?? [];
        $total = $esResult['hits']['total']['value'] ?? 0;

        $results = array_map(function ($hit) {
            $source = $hit['_source'];
            $highlight = $hit['highlight']['content'][0] ?? null;
            
            return [
                'id' => $source['id'],
                'page_number' => $source['page_number'],
                'content' => $highlight ?: mb_substr($source['content'], 0, 200),
                'book_title' => $source['book_title'] ?? 'غير محدد',
                'author_name' => $source['author_names'] ?? 'غير محدد',
                'book_id' => $source['book_id'],
                'book_section_id' => $source['book_section_id'] ?? null,
            ];
        }, $hits);

        return [
            'results' => $results,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'from' => ($page - 1) * $perPage + 1,
            'to' => min($page * $perPage, $total)
        ];
    }

    /**
     * Get search suggestions
     *
     * @param string $query
     * @param int $size
     * @return array
     */
    public function getSuggestions(string $query, int $size = 5): array
    {
        try {
            $searchBody = [
                'size' => 0,
                'suggest' => [
                    'text' => $query,
                    'content_suggest' => [
                        'term' => [
                            'field' => 'content',
                            'size' => $size
                        ]
                    ]
                ]
            ];

            $response = $this->client->post($this->host . '/' . $this->index . '/_search', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $searchBody
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            $suggestions = [];
            if (isset($result['suggest']['content_suggest'][0]['options'])) {
                foreach ($result['suggest']['content_suggest'][0]['options'] as $option) {
                    $suggestions[] = $option['text'];
                }
            }

            return $suggestions;

        } catch (RequestException $e) {
            return [];
        }
    }
}