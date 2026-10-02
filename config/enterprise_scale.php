<?php
/**
 * FastNetStays Enterprise Deployment Config Blueprint
 * Standardized configuration mapping for high-scale Kubernetes & Cloud clusters.
 */
return [
    'Scale' => [
        'target_concurrency' => '5,000,000+',
        'architecture' => 'Stateless Kubernetes Pods + Distributed Redis + Cloudflare Edge',
        'recommended_nodes' => 'Auto-scaling Horizontal Pod Autoscaler (HPA) min 50, max 500 pods',
        'redis' => [
            'cluster_enabled' => true,
            'session_engine' => 'Cake\Cache\Engine\RedisEngine',
            'session_prefix' => 'fastnet_sess_',
            'cache_prefix' => 'fastnet_cache_',
        ],
        'cdn' => [
            'assets_host' => 'cdn.fastnetstays.com',
            'cache_control' => 'public, max-age=31536000, immutable',
        ]
    ]
];
