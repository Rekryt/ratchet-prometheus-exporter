# Ratchet Prometheus Exporter

Custom prometheus exporter with simple json config based on PHP, Ratchet HttpServer and PrometheusClientPHP.

## Installation

```
cp .env.example .env
docker-compose build
```

## Configuration

#### .env

```dotenv
COMPOSE_PROJECT_NAME=rpe
HOST=0.0.0.0
PORT=80
REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379
REDIS_DB=0
DEBUG=false
```

#### config.json (example with shell command in counter)

Modify your custom counter script: `scripts/counter.php` and add shell execution script to value property of `counter`, `grouge`, `histogram` or `summary` of timer.

```json
{
    "timers": [
        {
            "interval": 0.5,
            "counters": [
                {
                    "namespace": "scripts",
                    "name": "counter",
                    "help": "shell script counter",
                    "labels": ["type"],
                    "count": {
                        "value": "php scripts/counter.php",
                        "labels": ["test"]
                    }
                }
            ]
        }
    ]
}
```

#### config.json (example with constants in collectors)

```json
{
    "timers": [
        {
            "interval": 1,
            "counters": [
                {
                    "namespace": "test",
                    "name": "some_counter",
                    "help": "it increases",
                    "labels": ["type"],
                    "count": {
                        "value": 3,
                        "labels": ["blue"]
                    }
                }
            ],
            "gauges": [
                {
                    "namespace": "test",
                    "name": "some_gauge",
                    "help": "it sets",
                    "labels": ["type"],
                    "count": {
                        "value": 2.5,
                        "labels": ["blue"]
                    }
                }
            ],
            "histograms": [
                {
                    "namespace": "test",
                    "name": "some_histogram",
                    "help": "it some_histogram",
                    "labels": ["type"],
                    "buckets": [0.1, 1, 2, 3.5, 4, 5, 6, 7, 8, 9],
                    "count": {
                        "value": 3.5,
                        "labels": ["blue"]
                    }
                }
            ],
            "summaries": [
                {
                    "namespace": "test",
                    "name": "some_summary",
                    "help": "it observes a sliding window",
                    "labels": ["type"],
                    "maxAgeSeconds": 84600,
                    "quantiles": [0.01, 0.05, 0.5, 0.95, 0.99],
                    "count": {
                        "value": 5,
                        "labels": ["blue"]
                    }
                }
            ]
        }
    ]
}
```

## Using

```
docker-compose up -d
```

You can see the metrics:

```
curl http://localhost:80/
```

Then configure prometheus:
https://prometheus.io/docs/introduction/first_steps/#configuring-prometheus

## Links

https://github.com/ratchetphp/Ratchet
https://github.com/promphp/prometheus_client_php
