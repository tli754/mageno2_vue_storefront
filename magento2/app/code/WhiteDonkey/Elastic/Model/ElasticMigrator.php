<?php

namespace WhiteDonkey\Elastic\Model;

use WhiteDonkey\Elastic\Model\ElasticClient;

class ElasticMigrator
{
    protected $_client;
    protected $_keepSource = false;

    function __construct()
    {
        $elasticClient = new ElasticClient();
        $this->_client = $elasticClient->getClient();
    }

    public function getReadAlias(string $indexTypeName) {
        return "catalog.{$indexTypeName}";
    }

    public function getWriteAlias(string $indexTypeName) {
        return "catalog.${indexTypeName}.write";
    }

    protected function _realNames(string $indexTypeName)
    {
        try {
            return array_keys($this->_client->indices()->getAlias(['name' => $this->getReadAlias($indexTypeName), 'ignore_unavailable' => true]));
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function _getIndexConfigs()
    {
        return [
            'product' => [
                'mappings' => [
                    'product' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'name' => [
                                'type' => 'text',
                                'fields' => [
                                    'raw' => [
                                        'type' => 'keyword',
                                    ]
                                ]
                            ],
                            'storeId' => ['type' => 'integer'],
                            'visibleInCategoryIds' => ['type' => 'integer'],
                            'filterables' => ['type' => 'keyword'],
                            'url' => ['type' => 'keyword'],
                            'nestedSlug' => ['type' => 'keyword'],
                            'urlKey' => ['type' => 'keyword'],
                            'urlKeyAliases' => ['type' => 'keyword'],
                            'price' => ['type' => 'double'],
                            'revenue' => ['type' => 'double'],
                            'specialPrice' => ['type' => 'double'],
                            'specialFrom' => ['type' => 'date'],
                            'specialTo' => ['type' => 'date'],
                            'weight' => ['type' => 'double'],
                            'quantity' => ['type' => 'integer'],
                            'reviewId' => ['type' => 'integer'],
                            'statusId' => ['type' => 'integer'],
                            'saleCount' => ['type' => 'integer'],
                            'viewCount' => ['type' => 'integer'],
                            'reviewGroup' => ['type' => 'integer'],
                            'popularRank' => ['type' => 'integer'],
                            'relatedProductBlacklist' => ['type' => 'keyword'],
                            'productVideos' => [
                                'properties' => [
                                'id' => ['type' => 'text'],
                                'label' => ['type' => 'text'],
                                'position' => ['type' => 'integer'],
                                'thumbnail' => ['type' => 'text'],
                                'url' => ['type' => 'text'],
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            'category' => [
                'mappings' => [
                    'category' => [
                        'properties' => [
                            'storeId' => ['type' => 'integer'],
                            'level' => ['type' => 'integer'],
                            'magentoId' => ['type' => 'integer'],
                            'parentId' => ['type' => 'integer'],
                            'pathIds' => ['type' => 'integer'],
                            'position' => ['type' => 'integer'],
                            'product_count' => ['type' => 'integer'],
                            'googleCategory' => ['type' => 'integer'],
                            'hasQcard' => ['type' => 'boolean'],
                            'h1Heading' => ['type' => 'text'],
                            'includeInMenu' => ['type' => 'boolean'],
                            'isActive' => ['type' => 'boolean'],
                            'url' => ['type' => 'keyword'],
                            'nestedSlug' => ['type' => 'keyword'],
                            'urlKey' => ['type' => 'keyword'],
                            'urlPathAliases' => ['type' => 'keyword'],
                            'filterUrls' => ['type' => 'keyword'],
                            'revenue' => ['type' => 'float'],
                            'avgViews' => ['type' => 'float'],
                        ]
                    ]
                ]
            ],
            'review' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'phrase_cloud_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => ['standard', 'lowercase', 'stop', 'custom_stop_words', 'minimal_plural_stemmer', 'phrase_shingles_token_filter']
                            ],
                        ],
                        'filter' => [
                            'custom_stop_words' => [
                                'type' => 'stop',
                                'stopwords' => ['our', 'i', 'have', 'my', 'a', 'is', 'this', 'have', 'has'], //@todo: make this configurable from magento admin
                            ],
                            'phrase_shingles_token_filter' => [
                                'type' => 'shingle',
                                'output_unigrams' => true,
                                'max_shingle_size' => 3,
                            ],
                            'minimal_plural_stemmer' => [
                                'type' => 'stemmer',
                                'name' => 'minimal_english',
                            ],
                        ]
                    ],
                ],
                'mappings' => [
                    'review' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'magentoId' => ['type' => 'integer'],
                            'recommend' => ['type' => 'boolean'],
                            'title' => ['type' => 'text'],
                            'storeId' => ['type' => 'integer'],
                            'rating' => ['type' => 'integer'],
                            'createdAt' => ['type' => 'date'],
                            'statusId' => ['type' => 'integer'],
                            'replyComment' => ['type' => 'text', 'index' => false],
                            'nickname' => ['type' => 'text', 'index' => false],
                            'entityId' => ['type' => 'integer'],
                            'entityPkValue' => ['type' => 'integer'],
                            'location' => ['type' => 'text', 'index' => false],
                            'detail' => [
                                'type' => 'text',
                                'similarity' => 'BM25',
                                'index' => true,
                                'fields' => [
                                    'phrases' => [
                                        'type' => 'text',
                                        'similarity' => 'BM25',
                                        'analyzer' => 'phrase_cloud_analyzer'
                                    ],
                                ]
                            ],
                            'imageData' => [
                                'properties' => [
                                    'u' => ['type' => 'text'],
                                    'o' => ['type' => 'text'],
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'question' => [
                'mappings' => [
                    'question' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'status' => ['type' => 'keyword'],
                            'magentoId' => ['type' => 'integer'],
                            'createdAt' => ['type' => 'date'],
                            'question' => ['type' => 'text'],
                            'answer' => ['type' => 'text'],
                            'answeredAt' => ['type' => 'date'],
                            'votes' => ['type' => 'integer'],
                        ]
                    ]
                ]
            ],
            'page' => [
                'mappings' => [
                    'page' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'magentoId' => ['type' => 'integer'],
                            'url' => ['type' => 'keyword'],
                            'title' => ['type' => 'text', 'index' => false],
                            'rootTemplate' => ['type' => 'keyword', 'index' => false],
                            'layoutUpdateXml' => ['type' => 'text', 'index' => false],
                            'metaKeywords' => ['type' => 'text', 'index' => false],
                            'metaDescription' => ['type' => 'text', 'index' => false],
                            'contentHeading' => ['type' => 'text'],
                            'isActive' => ['type' => 'boolean'],
                            'isFeatured' => ['type' => 'boolean'],
                            'menuPosition' => ['type' => 'integer'],
                            'contentCategoryId' => ['type' => 'integer'],
                        ]
                    ]
                ]
            ],
            'content_category' => [
                'mappings' => [
                    'contentCategory' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'magentoId' => ['type' => 'integer'],
                            'url' => ['type' => 'keyword'],
                            'urlKey' => ['type' => 'keyword'],
                            'title' => ['type' => 'text'],
                            'nestedUrlKey' => ['type' => 'keyword'],
                            'nestedTitle' => ['type' => 'text', 'index' => false],
                            'shortDescription' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function _makeHash(array $body)
    {
        return substr(md5(json_encode($body)), 0, 8);
    }

    protected function _generateName(string $indexTypeName, array $body)
    {
        return implode('.', [$this->getReadAlias($indexTypeName), time(), $this->_makeHash($body)]);
    }

    protected function _swapAlias(string $sourceIndex, string $targetIndex, string $alias)
    {
        if ($this->_client->indices()->existsAlias(['name' => $alias, 'index' => $sourceIndex])) {
            return $this->_client->indices()->updateAliases(['body' => ['actions' => [
                ['remove' => ['index' => $sourceIndex, 'alias' => $alias]],
                ['add' => ['index' => $targetIndex, 'alias' => $alias]],
            ]]]);
        } else {
            return $this->_client->indices()->updateAliases(['body' => ['actions' => [
                ['add' => ['index' => $targetIndex, 'alias' => $alias]],
            ]]]);
        }
    }

    /**
     * @param string $targetIndex
     * @param string $indexTypeName
     * @param array $body
     * @throws \Exception
     */
    protected function _createFirstIndex(string $targetIndex, string $indexTypeName, array $body)
    {
        if ($this->_client->indices()->exists(['index' => $targetIndex])) {
            throw new \Exception("Index {$targetIndex} already exists");
        }
        $body = array_merge(
            $body,
            ['aliases' => [$this->getReadAlias($indexTypeName) => new \stdClass(), $this->getWriteAlias($indexTypeName) => new \stdClass()]]
        );
        $this->_client->indices()->create(['index' => $targetIndex, 'body' => $body]);
        //@todo: populate the new index
    }

    protected function _migrateIndex(string $sourceIndex, string $targetIndex, string $indexTypeName, array $body)
    {
        if ($this->_makeHash($body) == end(explode('.', $sourceIndex))) {
            return;
        }
        $this->_client->indices()->create(['index' => $targetIndex, 'body' => $body]);
        $this->_client->indices()->refresh(['index' => $targetIndex]);
        $this->_swapAlias($sourceIndex, $targetIndex, $this->getWriteAlias($indexTypeName));
        $this->_client->reindex([
            'body' => ['source' => ['index' => $sourceIndex], 'dest' => ['index' => $targetIndex]]
        ]);
        $this->_swapAlias($sourceIndex, $targetIndex, $this->getReadAlias($indexTypeName));
        if (!$this->_keepSource) {
            $this->_client->indices()->delete(['index' => $sourceIndex]);
        }
        echo "{$sourceIndex} => {$targetIndex}\n";
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        foreach ($this->_getIndexConfigs() as $indexTypeName => $body) {
            $targetIndex = $this->_generateName($indexTypeName, $body);
            $indices = $this->_realNames($indexTypeName);
            if (count($indices) > 0) {
                rsort($indices);
                $this->_migrateIndex($indices[0], $targetIndex, $indexTypeName, $body);
            } else {
                $this->_createFirstIndex($targetIndex, $indexTypeName, $body);
            }
        }
    }
}
