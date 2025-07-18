# Docker Health Check Solution Implementation

## Solution Overview

Implemented Docker Compose health check solution to fix the DNS resolution timing issue that was causing Doctrine ORM connection state problems in CI/CD environments.

## Root Cause Addressed

**Problem**: PHP-FPM container starts and attempts database connections before PostgreSQL is fully ready to accept connections, causing:
- Incomplete connection context establishment
- Parameter binding failures (`invalid input syntax for type boolean: ""`)
- Empty query results despite data existing

**Solution**: Docker Compose service dependencies with health checks ensure PostgreSQL is fully ready before PHP-FPM starts.

## Implementation Details

### 1. PostgreSQL Health Check Added

```yaml
postgres:
    image: postgres:17.4-alpine
    container_name: shopsys-framework-postgres
    # ... existing configuration ...
    healthcheck:
        test: ["CMD-SHELL", "pg_isready -U root -d shopsys"]
        interval: 5s
        timeout: 5s
        retries: 5
        start_period: 30s
```

**Health Check Parameters**:
- **Test Command**: `pg_isready -U root -d shopsys` - Checks if PostgreSQL is ready to accept connections
- **Interval**: 5 seconds between health checks
- **Timeout**: 5 seconds for each health check command
- **Retries**: 5 attempts before marking as unhealthy
- **Start Period**: 30 seconds grace period during container startup

### 2. PHP-FPM Dependencies Updated

```yaml
php-fpm:
    # ... existing configuration ...
    depends_on:
        postgres:
            condition: service_healthy  # Wait for PostgreSQL health check to pass
        redis:
            condition: service_started  # Wait for Redis to start
        elasticsearch:
            condition: service_started  # Wait for Elasticsearch to start
```

**Dependency Conditions**:
- **postgres**: `service_healthy` - Waits for health check to pass
- **redis**: `service_started` - Waits for service to start (no health check needed)
- **elasticsearch**: `service_started` - Waits for service to start

### 3. PHP-Consumer Dependencies Updated

```yaml
php-consumer:
    # ... existing configuration ...
    depends_on:
        php-fpm:
            condition: service_started  # Wait for PHP-FPM to start
        postgres:
            condition: service_healthy  # Wait for PostgreSQL health check to pass
        rabbitmq:
            condition: service_started  # Wait for RabbitMQ to start
```

## Temporary Workaround Disabled

Disabled the connection metadata access workaround to test if the Docker health check solution resolves the issue:

### SliderItemRepository.php
```php
// === CONNECTION INITIALIZATION FIX - DISABLED FOR DOCKER HEALTH CHECK TEST ===
// Temporary workaround replaced with Docker Compose health check solution
// $connection->getDatabase();
// $connection->getHost();
error_log("🔍 [SLIDER_DIAG] Testing Docker health check solution - workaround disabled");
```

### PromotedCategoryRepository.php
```php
// === CONNECTION INITIALIZATION FIX - DISABLED FOR DOCKER HEALTH CHECK TEST ===
// Temporary workaround replaced with Docker Compose health check solution
// $connection->getDatabase();
// $connection->getHost();
error_log("🔍 [PROMOTED_DIAG] Testing Docker health check solution - workaround disabled");
```

## Expected Benefits

### 1. Proper Service Initialization Order
- PostgreSQL fully ready before PHP attempts connections
- Eliminates timing race conditions
- Ensures database context properly established

### 2. Improved Reliability
- Consistent service startup sequence
- Reduces intermittent connection issues
- Better CI/CD environment stability

### 3. Clean Architecture
- No application-level workarounds needed
- Infrastructure-level solution
- Follows Docker Compose best practices

## Testing Strategy

### 1. Local Testing
```bash
# Stop all services
docker compose down

# Start with health check solution
docker compose up -d

# Monitor startup sequence
docker compose logs -f postgres php-fpm

# Test GraphQL queries
curl -X POST http://localhost:8000/graphql/SliderItemsQuery
curl -X POST http://localhost:8000/graphql/PromotedCategoriesQuery
```

### 2. Health Check Monitoring
```bash
# Check PostgreSQL health status
docker compose ps postgres

# Monitor health check logs
docker compose logs postgres | grep "pg_isready"

# Verify startup order
docker compose events
```

### 3. CI/CD Environment Testing
- Deploy to CI/CD environment with health check configuration
- Monitor container startup sequence
- Verify GraphQL queries return proper results without workaround
- Check logs for absence of connection timing issues

## Rollback Plan

If the Docker health check solution doesn't resolve the issue:

### 1. Re-enable Workaround
```php
// Re-enable connection metadata access
$connection->getDatabase();
$connection->getHost();
```

### 2. Alternative Solutions
- Implement connection validation in application bootstrap
- Add connection retry logic with exponential backoff
- Implement proper connection warming in Symfony services

## Monitoring & Validation

### Success Indicators
- ✅ No "empty result" errors in logs
- ✅ No parameter binding errors
- ✅ Consistent query results on first request
- ✅ Proper container startup sequence

### Failure Indicators
- ❌ Continued empty query results
- ❌ Parameter binding errors persist
- ❌ Container startup failures
- ❌ Health check failures

## Next Steps

1. **Test Locally**: Verify health check solution works in local Docker environment
2. **Deploy to CI/CD**: Test in production-like CI/CD environment
3. **Monitor Results**: Check for resolution of connection timing issues
4. **Document Results**: Update investigation with success/failure analysis
5. **Clean Up**: Remove debug logging if solution successful

## Technical Notes

### Health Check Command Details
```bash
pg_isready -U root -d shopsys
```
- **Purpose**: Tests if PostgreSQL can accept connections
- **User**: `root` (matches application connection user)
- **Database**: `shopsys` (matches application database)
- **Return Codes**: 0=ready, 1=rejecting connections, 2=no response, 3=no attempt

### Docker Compose Version Requirements
- Requires Docker Compose version 2.0+ for `condition` syntax
- Health check feature requires Docker Engine 1.12+
- Compatible with both Compose V1 and V2 syntax

## Implementation Status

- ✅ PostgreSQL health check configured
- ✅ PHP-FPM dependencies updated with health condition
- ✅ PHP-Consumer dependencies updated
- ✅ Temporary workaround disabled for testing
- ✅ Documentation created
- 🔄 **Ready for testing and validation**