<?php

declare(strict_types=1);

namespace App\Service\Index;

use App\Classes\ElasticSearchBase;
use App\Classes\IndexableCourse;
use Exception;
use InvalidArgumentException;

class Curriculum extends ElasticSearchBase
{
    public const INDEX = 'ilios-curriculum';
    public const SESSION_ID_PREFIX = 'session_';

    public function search(string $query, bool $onlySuggest): array
    {
        if (!$this->enabled) {
            throw new Exception("Search is not configured, isEnabled() should be called before calling this method");
        }

        $suggestFields = [
            'courseTitle',
            'courseTerms',
            'courseMeshDescriptorIds',
            'courseMeshDescriptorNames',
            'courseLearningMaterialTitles',
            'sessionTitle',
            'sessionType',
            'sessionTerms',
            'sessionMeshDescriptorIds',
            'sessionMeshDescriptorNames',
            'sessionLearningMaterialTitles',
        ];
        $suggest = array_reduce($suggestFields, function ($carry, $field) use ($query) {
            $carry[$field] = [
                'prefix' => $query,
                'completion' => [
                    'field' => "${field}.cmp",
                    'skip_duplicates' => true,
                ]
            ];

            return $carry;
        }, []);

        $params = [
            'index' => self::INDEX,
            'body' => [
                'suggest' => $suggest,
                "_source" => [
                    'courseId',
                    'courseTitle',
                    'courseYear',
                    'sessionId',
                    'sessionTitle',
                    'school',
                ],
                'sort' => '_score',
                'size' => 1000
            ]
        ];

        if (!$onlySuggest) {
            $params['body']['query'] = $this->buildCurriculumSearch($query);
        }

        $results = $this->doSearch($params);

        return $this->parseCurriculumSearchResults($results);
    }

    /**
     * @param IndexableCourse[] $courses
     */
    public function index(array $courses): bool
    {
        foreach ($courses as $course) {
            if (!$course instanceof IndexableCourse) {
                throw new InvalidArgumentException(
                    sprintf(
                        '$courses must be an array of %s. %s found',
                        IndexableCourse::class,
                        $course::class
                    )
                );
            }
        }

        $input = array_reduce($courses, function (array $carry, IndexableCourse $item) {
            $sessions = $item->createIndexObjects();
            $sessionsWithMaterials = $this->attachLearningMaterialsToSession($sessions);
            return array_merge($carry, $sessionsWithMaterials);
        }, []);

        $result = $this->doBulkIndex(self::INDEX, $input);

        if ($result['errors']) {
            $errors = array_map(function (array $item) {
                if (array_key_exists('error', $item['index'])) {
                    return $item['index']['error']['reason'];
                }
            }, $result['items']);
            $clean = array_filter($errors);
            $str = join(';', array_unique($clean));
            $count = count($clean);
            throw new Exception("Failed to index all courses ${count} errors. Error text: ${str}");
        }

        return true;
    }

    /**
     * @param int $id
     */
    public function deleteCourse(int $id): bool
    {
        $result = $this->doDeleteByQuery([
            'index' => self::INDEX,
            'body' => [
                'query' => [
                    'term' => ['courseId' => $id]
                ]
            ]
        ]);

        return !count($result['failures']);
    }

    /**
     * @param int $id
     */
    public function deleteSession(int $id): bool
    {
        $result = $this->doDelete([
            'index' => self::INDEX,
            'id' => self::SESSION_ID_PREFIX . $id
        ]);

        return $result['result'] === 'deleted';
    }

    protected function attachLearningMaterialsToSession(array $sessions): array
    {
        $courseIds = array_column($sessions, 'courseFileLearningMaterialIds');
        $sessionIds = array_column($sessions, 'sessionFileLearningMaterialIds');
        $learningMaterialIds = array_values(array_unique(array_merge([], ...$courseIds, ...$sessionIds)));
        $materialsById = [];
        if (!empty($learningMaterialIds)) {
            $params = [
                'index' => LearningMaterials::INDEX,
                'body' => [
                    'query' => [
                        'terms' => [
                            'learningMaterialId' => $learningMaterialIds
                        ]
                    ],
                    "_source" => [
                        'learningMaterialId',
                        'material.content',
                    ]
                ]
            ];
            $results = $this->doSearch($params);

            $materialsById = array_reduce($results['hits']['hits'], function (array $carry, array $hit) {
                $result = $hit['_source'];
                $id = $result['learningMaterialId'];

                if (array_key_exists('material', $result)) {
                    $carry[$id][] = $result['material']['content'];
                }

                return $carry;
            }, []);
        }

        return array_map(function (array $session) use ($materialsById) {
            foreach ($session['sessionFileLearningMaterialIds'] as $id) {
                if (array_key_exists($id, $materialsById)) {
                    foreach ($materialsById[$id] as $value) {
                        $session['sessionLearningMaterialAttachments'][] = $value;
                    }
                }
            }
            unset($session['sessionFileLearningMaterialIds']);
            foreach ($session['courseFileLearningMaterialIds'] as $id) {
                if (array_key_exists($id, $materialsById)) {
                    foreach ($materialsById[$id] as $value) {
                        $session['courseLearningMaterialAttachments'][] = $value;
                    }
                }
            }
            unset($session['courseFileLearningMaterialIds']);

            return $session;
        }, $sessions);
    }

    /**
     * Construct the query to search the curriculum
     * @param string $query
     */
    protected function buildCurriculumSearch(string $query): array
    {
        $mustFields = [
            'courseId',
            'courseYear',
            'courseTitle',
            'courseTitle.ngram',
            'courseTerms',
            'courseTerms.ngram',
            'courseObjectives',
            'courseObjectives.ngram',
            'courseLearningMaterialTitles',
            'courseLearningMaterialTitles.ngram',
            'courseLearningMaterialDescriptions',
            'courseLearningMaterialDescriptions.ngram',
            'courseLearningMaterialCitation',
            'courseLearningMaterialCitation.ngram',
            'courseMeshDescriptorIds',
            'courseMeshDescriptorNames',
            'courseMeshDescriptorNames.ngram',
            'courseMeshDescriptorAnnotations',
            'courseMeshDescriptorAnnotations.ngram',
            'courseLearningMaterialAttachments',
            'courseLearningMaterialAttachments.ngram',
            'sessionId',
            'sessionTitle',
            'sessionTitle.ngram',
            'sessionDescription',
            'sessionDescription.ngram',
            'sessionType',
            'sessionTerms',
            'sessionTerms.ngram',
            'sessionObjectives',
            'sessionObjectives.ngram',
            'sessionLearningMaterialTitles',
            'sessionLearningMaterialTitles.ngram',
            'sessionLearningMaterialDescriptions',
            'sessionLearningMaterialDescriptions.ngram',
            'sessionLearningMaterialCitation',
            'sessionLearningMaterialCitation.ngram',
            'sessionMeshDescriptorIds',
            'sessionMeshDescriptorNames',
            'sessionMeshDescriptorNames.ngram',
            'sessionMeshDescriptorAnnotations',
            'sessionMeshDescriptorAnnotations.ngram',
            'sessionLearningMaterialAttachments',
            'sessionLearningMaterialAttachments.ngram',
        ];

        $shouldFields = [
            'courseTitle',
            'courseTerms',
            'courseObjectives',
            'courseLearningMaterialTitles',
            'courseLearningMaterialDescriptions',
            'courseLearningMaterialAttachments',
            'sessionTitle',
            'sessionDescription',
            'sessionType',
            'sessionTerms',
            'sessionObjectives',
            'sessionLearningMaterialTitles',
            'sessionLearningMaterialDescriptions',
            'sessionLearningMaterialAttachments',
        ];

        $mustMatch = array_map(fn($field) => [ 'match' => [ $field => [
            'query' => $query,
            '_name' => $field,
        ] ] ], $mustFields);

        /**
         * At least one of the mustMatch queries has to be a match
         * but we wrap it in a should block so they don't all have to match
         */
        $must = ['bool' => [
            'should' => $mustMatch
        ]];

        /**
         * The should queries are designed to boost the total score of
         * results that match more closely than the MUST set above so when
         * users enter a complete word like move it will score higher than
         * than a partial match on movement
         */
        $should = array_reduce(
            $shouldFields,
            function (array $carry, string $field) use ($query) {
                $matches = array_map(function (string $type) use ($field, $query) {
                    $fullField = "${field}.${type}";
                    return [ 'match' => [ $fullField => ['query' => $query, '_name' => $fullField] ] ];
                }, ['english', 'raw']);

                return array_merge($carry, $matches);
            },
            []
        );

        return [
            'bool' => [
                'must' => $must,
                'should' => $should,
            ]
        ];
    }

    protected function parseCurriculumSearchResults(array $results): array
    {
        $autocompleteSuggestions = array_reduce(
            $results['suggest'],
            function (array $carry, array $item) {
                $options = array_map(fn(array $arr) => $arr['text'], $item[0]['options']);

                return array_unique(array_merge($carry, $options));
            },
            []
        );

        $mappedResults = array_map(function (array $arr) {
            $courseMatches = array_filter(
                $arr['matched_queries'],
                fn(string $match) => str_starts_with($match, 'course')
            );
            $sessionMatches = array_filter(
                $arr['matched_queries'],
                fn(string $match) => str_starts_with($match, 'session')
            );
            $rhett = $arr['_source'];
            $rhett['score'] = $arr['_score'];
            $rhett['courseMatches'] = $courseMatches;
            $rhett['sessionMatches'] = $sessionMatches;

            return $rhett;
        }, $results['hits']['hits']);

        $courses = array_reduce($mappedResults, function (array $carry, array $item) {
            $id = $item['courseId'];
            if (!array_key_exists($id, $carry)) {
                $carry[$id] = [
                    'id' => $id,
                    'title' => $item['courseTitle'],
                    'year' => $item['courseYear'],
                    'school' => $item['school'],
                    'bestScore' => 0,
                    'sessions' => [],
                    'matchedIn' => [],
                ];
            }
            $courseMatches = array_map(function (string $match) {
                $split = explode('.', $match);
                $field = strtolower(substr($split[0], strlen('course')));
                if (strpos($field, 'meshdescriptor') !== false) {
                    $field = 'meshdescriptors';
                }
                if (strpos($field, 'learningmaterial') !== false) {
                    $field = 'learningmaterials';
                }

                return $field;
            }, $item['courseMatches']);
            $sessionMatches = array_map(function (string $match) {
                $split = explode('.', $match);
                $field = strtolower(substr($split[0], strlen('session')));
                if (strpos($field, 'meshdescriptor') !== false) {
                    $field = 'meshdescriptors';
                }
                if (strpos($field, 'learningmaterial') !== false) {
                    $field = 'learningmaterials';
                }

                return $field;
            }, $item['sessionMatches']);
            $carry[$id]['matchedIn'] = array_values(array_unique(
                array_merge($courseMatches, $carry[$id]['matchedIn'])
            ));
            if ($item['score'] > $carry[$id]['bestScore']) {
                $carry[$id]['bestScore'] = $item['score'];
            }
            $carry[$id]['sessions'][] = [
                'id' => $item['sessionId'],
                'title' => $item['sessionTitle'],
                'score' => $item['score'],
                'matchedIn' => array_values(array_unique($sessionMatches)),
            ];

            return $carry;
        }, []);

        usort($courses, fn($a, $b) => $b['bestScore'] <=> $a['bestScore']);

        return [
            'autocomplete' => $autocompleteSuggestions,
            'courses' => $courses
        ];
    }

    public static function getMapping(): array
    {
        $txtTypeField = [
            'type' => 'text',
            'analyzer' => 'standard',
            'fields' => [
                'ngram' => [
                    'type' => 'text',
                    'analyzer' => 'ngram_analyzer',
                    'search_analyzer' => 'string_search_analyzer',
                ],
                'english' => [
                    'type' => 'text',
                    'analyzer' => 'english',
                ],
                'raw' => [
                    'type' => 'text',
                    'analyzer' => 'keyword',
                ]
            ],
        ];
        $txtTypeFieldWithCompletion = $txtTypeField;
        $txtTypeFieldWithCompletion['fields']['cmp'] = ['type' => 'completion'];

        return [
            'settings' => [
                'analysis' => self::getAnalyzers(),
                'max_ngram_diff' =>  15,
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
            'mappings' => [
                '_meta' => [
                    'version' => '1',
                ],
                'properties' => [
                    'courseId' => [
                        'type' => 'keyword',
                    ],
                    'school' => [
                        'type' => 'keyword',
                        'fields' => [
                            'cmp' => [
                                'type' => 'completion'
                            ]
                        ],
                    ],
                    'courseYear' => [
                        'type' => 'keyword',
                    ],
                    'courseTitle' => $txtTypeFieldWithCompletion,
                    'courseTerms' => $txtTypeFieldWithCompletion,
                    'courseObjectives'  => $txtTypeField,
                    'courseLearningMaterialTitles'  => $txtTypeFieldWithCompletion,
                    'courseLearningMaterialDescriptions'  => $txtTypeField,
                    'courseLearningMaterialCitation'  => $txtTypeField,
                    'courseLearningMaterialAttachments'  => $txtTypeField,
                    'courseMeshDescriptorIds' => [
                        'type' => 'keyword',
                        'fields' => [
                            'cmp' => [
                                'type' => 'completion',
                                // we have to override the analyzer here because the default strips
                                // out numbers and mesh ids are mostly numbers
                                'analyzer' => 'standard',
                            ]
                        ],
                    ],
                    'courseMeshDescriptorNames' => $txtTypeFieldWithCompletion,
                    'courseMeshDescriptorAnnotations' => $txtTypeField,
                    'sessionId' => [
                        'type' => 'keyword',
                    ],
                    'sessionTitle' => $txtTypeFieldWithCompletion,
                    'sessionDescription' => $txtTypeField,
                    'sessionType' => [
                        'type' => 'keyword',
                        'fields' => [
                            'cmp' => [
                                'type' => 'completion'
                            ]
                        ],
                    ],
                    'sessionTerms' => $txtTypeFieldWithCompletion,
                    'sessionObjectives'  => $txtTypeField,
                    'sessionLearningMaterialTitles'  => $txtTypeFieldWithCompletion,
                    'sessionLearningMaterialDescriptions'  => $txtTypeField,
                    'sessionLearningMaterialCitation'  => $txtTypeField,
                    'sessionLearningMaterialAttachments'  => $txtTypeField,
                    'sessionMeshDescriptorIds' => [
                        'type' => 'keyword',
                        'fields' => [
                            'cmp' => [
                                'type' => 'completion',
                                // we have to override the analyzer here because the default strips
                                // out numbers and mesh ids are mostly numbers
                                'analyzer' => 'standard',
                            ]
                        ],
                    ],
                    'sessionMeshDescriptorNames' => $txtTypeFieldWithCompletion,
                    'sessionMeshDescriptorAnnotations' => $txtTypeField,
                ]
            ]
        ];
    }

    protected static function getAnalyzers(): array
    {
        return [
            'analyzer' => [
                'ngram_analyzer' => [
                    'tokenizer' => 'ngram_tokenizer',
                    'filter' => ['lowercase'],
                ],
                'string_search_analyzer' => [
                    'type' => 'custom',
                    'tokenizer' => 'keyword',
                    'filter' => ['lowercase', 'word_delimiter'],
                ],
            ],
            'tokenizer' => [
                'ngram_tokenizer' => [
                    'type' => 'ngram',
                    'min_gram' => 3,
                    'max_gram' => 15,
                    'token_chars' => [
                        'letter',
                        'digit'
                    ],
                ],
            ],
        ];
    }
}
