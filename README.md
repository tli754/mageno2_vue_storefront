# Magento 2 + Vue Storefront

A dockerized e-commerce stack combining Magento 2 as the backend with Vue Storefront as the PWA frontend.

## Architecture

| Service | Description | Port |
|---------|-------------|------|
| `magento2` | Magento 2 PHP backend | 8081, 8082, 8083 |
| `storefront` | Vue Storefront PWA frontend | 3000 |
| `storefront-api` | Vue Storefront API middleware | 8080 |
| `mysql` | Percona MySQL database | 33061 |
| `elasticsearch` | Elasticsearch 5.6 catalog index | 9200, 9300 |
| `kibana` | Elasticsearch UI | 5602 |
| `redis-api` | Redis cache for storefront API | 6379 |
| `redis-session` | Redis session store for Magento | — |
| `redis-cache` | Redis page cache for Magento | — |
| `rabbitmq` | Message queue (order processing) | 5672, 55672 |
| `nginx` | Reverse proxy / SSL termination | 80, 443 |

## Prerequisites

- Docker and Docker Compose
- Copy `.env.example` to `.env` and fill in credentials (see [Environment Variables](#environment-variables))

## Getting Started

```bash
# Clone the repo
git clone https://github.com/tli754/mageno2_vue_storefront.git
cd magento2-vue

# Start all services
docker-compose up -d

# Check service status
docker-compose ps
```

The storefront will be available at `http://localhost:3000` and the API at `http://localhost:8080`.

## Development Workflow

Source directories are volume-mounted into containers, so changes take effect without rebuilding images.

```bash
# Attach to a running service for debugging
docker-compose exec storefront sh
docker-compose exec storefront-api sh

# View logs
docker-compose logs -f storefront
docker-compose logs -f storefront-api
```

### Storefront API

```bash
docker-compose exec storefront-api yarn dev        # start dev server with hot reload
docker-compose exec storefront-api yarn build      # compile TypeScript
docker-compose exec storefront-api yarn test       # run linter
docker-compose exec storefront-api yarn test:unit  # run unit tests
```

### Seeding Elasticsearch

```bash
# Restore catalog data from a dump file
docker-compose exec storefront-api yarn restore

# Run database migrations
docker-compose exec storefront-api yarn migrate
```

## Environment Variables

Create a `.env` file in the project root:

```env
DOCKERHOST=<your-host-ip>
MYSQL_USER=<db-user>
MYSQL_PASSWORD=<db-password>
RABBITMQ_USER=<rabbitmq-user>
RABBITMQ_PASS=<rabbitmq-password>
```

## Production Deployment

Images are built and pushed to Docker Hub via Bitbucket Pipelines on every `release-*` tag.

```bash
# Tag a release to trigger CI builds
git tag release-1.0.0
git push origin release-1.0.0
```

Three images are built and published:
- `qweyha520/magento2:release-0.1.6.1`
- `qweyha520/storefront-api:release-0.1.5.2`
- `qweyha520/storefront:release-0.1.5.4`

Deploy to a Docker Swarm:

```bash
docker stack deploy -c docker-compose.production.yml public
```

## Project Structure

```
.
├── magento2/              # Magento 2 application code
├── vue-storefront/        # Vue Storefront PWA frontend
├── vue-storefront-api/    # Vue Storefront API middleware
├── tools/                 # Nginx config, PHP ini, supervisor config
├── docker-compose.yml               # Development stack
├── docker-compose.production.yml    # Production swarm stack
├── docker-compose.test.yml          # Test/staging stack
└── bitbucket-pipelines.yml          # CI/CD pipeline
```
