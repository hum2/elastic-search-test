<?php

namespace EsearchTest;

use Elasticsearch\ClientBuilder;

require 'vendor/autoload.php';

$hosts = [
    'elastic-search01:9200',
    'elastic-search02:9200',
    'elastic-search03:9200',
];

// この検証ではどんなデータセットでもかまわないため、ここについては言及しません
$params = [
    'index' => 'bbs',
    'type'  => 'newsoku',
    'body'  => [
        'query'     => [
            'simple_query_string' => [
                'query'  => 'VIP',
                'fields' => ['title', 'body']
            ]
        ],
        'highlight' => [
            'fragment_size' => 150,
            'fields'        => ['content' => ['type' => 'plain'], 'subject' => ['type' => 'plain']]
        ],
        'sort'      => ['date' => ['order' => 'asc', 'missing' => '_last']],
        'from'      => 0,
        'size'      => 10,
    ]
];

try {
    $client   = ClientBuilder::create()->setHosts($hosts)->build();
    $response = $client->cluster()->stats();
    // node_count = 3
    print("cluster_name = {$response['cluster_name']}, node_count = {$response['nodes']['count']}\n");

    // このタイミングでnode一台を停止する(service elasticsearch stop)
    sleep(10);
    $response = $client->cluster()->stats();
    // node_count = 2
    print("cluster_name = {$response['cluster_name']}, node_count = {$response['nodes']['count']}\n");

    // 問題なく結果が返ってくる
    var_dump($client->search($params));
} catch (\Exception $e) {
    var_dump($e->getMessage());
}


