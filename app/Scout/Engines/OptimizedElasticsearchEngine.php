<?php

namespace App\Scout\Engines;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Elasticsearch\Client as Elasticsearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\LazyCollection;

/**
 * Optimized Elasticsearch Engine for Laravel Scout
 * Based on Elasticsearch 7.17 best practices
 */
class OptimizedElasticsearchEngine extends Engine
{
    /**
     * The Elasticsearch client instance.
     */
    protected $elasticsearch;

    /**
     * Create a new OptimizedElasticsearchEngine instance.
     */
    public function __construct(Elasticsearch $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Update the given model in the index.
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $body = [];

        $models->each(function ($model) use (&$body) {
            $body[] = [
                'index' => [
                    '_index' => $model->searchableAs(),
                    '_id' => $model->getScoutKey(),
                ]
            ];

            $body[] = array_merge(
                $model->toSearchableArray(),
                $model->scoutMetadata()
            );
        });

        // Bulk index with optimized settings
        $this->elasticsearch->bulk([
            'body' => $body,
            'refresh' => 'wait_for', // للحصول على فهرسة فورية
            'timeout' => '30s',
            'retry_on_conflict' => 3,
        ]);
    }

    /**
     * Remove the given model from the index.
     */
    public function delete($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $body = [];

        $models->each(function ($model) use (&$body) {
            $body[] = [
                'delete' => [
                    '_index' => $model->searchableAs(),
                    '_id' => $model->getScoutKey(),
                ]
            ];
        });

        $this->elasticsearch->bulk([
            'body' => $body,
            'refresh' => 'wait_for',
            'timeout' => '10s',
        ]);
    }

    /**
     * Perform the given search on the engine.
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, [
            'filters' => $this->filters($builder),
            'from' => 0,
            'size' => $builder->limit ?: 10000,
        ]);
    }

    /**
     * Perform the given search on the engine with pagination.
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $result = $this->performSearch($builder, [
            'filters' => $this->filters($builder),
            'from' => (($page * $perPage) - $perPage),
            'size' => $perPage,
        ]);

        $result['nbPages'] = $this->getTotalCount($result) / $perPage;

        return $result;
    }

    /**
     * Perform the given search on the engine.
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $builder->model->searchableAs(),
            'body' => [
                'query' => $this->buildQuery($builder, $options['filters']),
                'highlight' => $this->buildHighlight(),
                '_source' => true,
            ],
            'timeout' => '10s', // Timeout سريع
            'preference' => '_local', // البحث في الـ local shard أولاً
        ];

        if (array_key_exists('from', $options)) {
            $params['body']['from'] = $options['from'];
        }

        if (array_key_exists('size', $options)) {
            $params['body']['size'] = $options['size'];
        }

        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $this->elasticsearch,
                $params
            );
        }

        return $this->elasticsearch->search($params);
    }

    /**
     * Build the search query for Elasticsearch.
     */
    protected function buildQuery(Builder $builder, $filters)
    {
        $query = [
            'bool' => [
                'must' => [],
                'filter' => [],
            ]
        ];

        // إضافة النص المراد البحث عنه
        if ($builder->query) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $builder->query,
                    'fields' => [
                        'content^3',    // أولوية عالية للمحتوى
                        'book_title^2', // أولوية متوسطة لعنوان الكتاب
                        'author_name^1.5', // أولوية للمؤلف
                    ],
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                    'operator' => 'or',
                    'minimum_should_match' => '75%',
                ]
            ];
        } else {
            // إذا لم يكن هناك نص، اعرض جميع النتائج
            $query['bool']['must'][] = ['match_all' => new \stdClass()];
        }

        // إضافة الفلاتر
        foreach ($filters as $field => $value) {
            $query['bool']['filter'][] = ['term' => [$field => $value]];
        }

        return $query;
    }

    /**
     * Build highlighting configuration.
     */
    protected function buildHighlight()
    {
        return [
            'fields' => [
                'content' => [
                    'fragment_size' => 150,
                    'number_of_fragments' => 1,
                    'pre_tags' => ['<mark class="highlight">'],
                    'post_tags' => ['</mark>'],
                ],
            ],
            'encoder' => 'html',
        ];
    }

    /**
     * Get the filter array for the query.
     */
    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->mapWithKeys(function ($value, $key) {
            return [$key => $value];
        })->all();
    }

    /**
     * Pluck and return the primary keys of the given results.
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return $model->newCollection();
        }

        $objectIds = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        $objectIdPositions = array_flip($objectIds);

        return $model->getScoutModelsByIds(
            $builder, $objectIds
        )->filter(function ($model) use ($objectIds) {
            return in_array($model->getScoutKey(), $objectIds);
        })->sortBy(function ($model) use ($objectIdPositions) {
            return $objectIdPositions[$model->getScoutKey()];
        })->values();
    }

    /**
     * Map the given results to instances of the given model via a lazy collection.
     */
    public function lazyMap(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return LazyCollection::make($model->newCollection());
        }

        $objectIds = collect($results['hits']['hits'])->pluck('_id')->values()->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->queryScoutModelsByIds(
            $builder, $objectIds
        )->cursor()->filter(function ($model) use ($objectIds) {
            return in_array($model->getScoutKey(), $objectIds);
        })->sortBy(function ($model) use ($objectIdPositions) {
            return $objectIdPositions[$model->getScoutKey()];
        })->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'] ?? $results['hits']['total'] ?? 0;
    }

    /**
     * Flush all of the model's records from the engine.
     */
    public function flush($model)
    {
        $indexName = $model->searchableAs();

        if ($this->elasticsearch->indices()->exists(['index' => $indexName])) {
            $this->elasticsearch->indices()->delete(['index' => $indexName]);
        }
    }

    /**
     * Create a search index.
     */
    public function createIndex($name, array $options = [])
    {
        $params = ['index' => $name];

        if (!empty($options)) {
            $params['body'] = $options;
        }

        $this->elasticsearch->indices()->create($params);
    }

    /**
     * Delete a search index.
     */
    public function deleteIndex($name)
    {
        $this->elasticsearch->indices()->delete(['index' => $name]);
    }
}