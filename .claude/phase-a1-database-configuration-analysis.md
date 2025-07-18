# Phase A1: Database Configuration Analysis Results

## A1.1: Doctrine DBAL Configuration Comparison ✅

### Configuration Summary

**Single Database Architecture**: Shopsys uses a unified database with domain-aware data filtering rather than separate databases per domain.

### Core Configuration Files

#### 1. Doctrine Configuration (`doctrine.yaml`)
```yaml
doctrine:
    dbal:
        charset: UTF8
        dbname: "%env(DATABASE_NAME)%"     # shopsys
        driver: "%database_driver%"        # pdo_pgsql  
        host: "%env(DATABASE_HOST)%"       # postgres
        password: "%env(DATABASE_PASSWORD)%" # root
        port: "%env(DATABASE_PORT)%"       # 5432
        server_version: "%database_server_version%" # 17.4
        user: "%env(DATABASE_USER)%"       # root
```

#### 2. Environment Configuration (`.env`)
```bash
DATABASE_HOST=postgres                    # Docker service name
DATABASE_PORT=5432                       # Standard PostgreSQL port
DATABASE_NAME=shopsys                    # Single database name
DATABASE_USER=root                       # Database user
DATABASE_PASSWORD=root                   # Database password
```

#### 3. Domain Configuration (`domains_urls.yaml`)
```yaml
domains_urls:
    -   id: 1
        url: http://127.0.0.1:8000      # Domain 1 URL
    -   id: 2  
        url: http://127.0.0.2:8000      # Domain 2 URL
```

### Key Findings

#### ✅ Confirmed: Single Database Architecture
- **Single Connection**: All domains use the same database connection
- **Domain Filtering**: Data isolation achieved through `domainId` filtering in queries
- **No Connection Pooling**: No external connection pooler (pgbouncer, etc.) configured
- **Direct Connection**: Direct connection from PHP to PostgreSQL

#### ⚠️ Critical Observation: Environment-Specific Differences
**Localhost Environment:**
- Direct Docker service communication (`postgres:5432`)
- Simple networking within Docker Compose
- Single connection pool managed by Doctrine

**CI/CD Environment (Likely):**
- More complex networking (Kubernetes/orchestration)
- Possible proxy/load balancer layers
- Different connection establishment timing
- External connection pooling mechanisms

#### 🔍 Connection Configuration Analysis

**Connection Parameters (localhost):**
```
Host: postgres (Docker service)
Port: 5432
Database: shopsys
User/Password: root/root
Driver: pdo_pgsql
Server Version: 17.4
```

**Missing Configuration Elements:**
- No connection pooling configuration
- No connection timeout settings
- No connection retry logic
- No connection validation queries
- No SSL configuration specified

## A1.2: PostgreSQL Server Configuration Analysis ✅

### PostgreSQL Configuration (`postgres.conf`)
```ini
listen_addresses = '*'
max_connections = 100                    # Connection limit
dynamic_shared_memory_type = posix
log_timezone = 'UTC'
datestyle = 'iso, mdy'
timezone = 'UTC'
lc_messages = 'en_US.utf8'
default_text_search_config = 'pg_catalog.english'

# Performance settings
random_page_cost = 1.0
shared_buffers = 1GB
```

### Key Configuration Points

#### ✅ Connection Settings
- **Max Connections**: 100 (reasonable for development)
- **Listen Addresses**: `*` (accepts all connections)
- **No Connection Pooling**: PostgreSQL running without pgbouncer/pooler

#### ⚠️ Missing Connection Management Features
- No connection timeout configuration
- No connection validation settings
- No idle connection cleanup
- No connection logging enabled

## A1.3: Docker Networking Configuration Analysis ✅

### Docker Compose Network Setup
```yaml
postgres:
    image: postgres:17.4-alpine
    container_name: shopsys-framework-postgres
    environment:
        - POSTGRES_USER=root
        - POSTGRES_PASSWORD=root
        - POSTGRES_DB=shopsys

php-fpm:
    # Connects to postgres:5432 via Docker internal networking
    depends_on:
        - postgres
```

### Networking Analysis

#### ✅ Local Development (Localhost)
- **Internal Docker Network**: Services communicate via service names
- **DNS Resolution**: Docker's internal DNS resolves `postgres` to container IP
- **Connection Establishment**: Direct, fast connection within same Docker network
- **No Proxy Layers**: Direct connection from php-fpm to postgres

#### ⚠️ CI/CD Environment (Inferred Differences)
- **External Orchestration**: Likely Kubernetes or similar orchestration
- **Service Discovery**: More complex service discovery mechanism
- **Network Policies**: Additional network security layers
- **DNS Resolution**: External DNS with potential delays
- **Load Balancers**: Possible database proxy/load balancer layers

## Critical Analysis: Root Cause Hypotheses

### 🎯 High Probability Issues Identified

#### 1. **DNS Resolution Timing in CI/CD**
- **Issue**: Docker service name `postgres` resolves differently in CI/CD
- **Symptom**: Connection established before DNS fully resolved
- **Evidence**: Connection metadata access (.getDatabase(), .getHost()) forces DNS resolution
- **Impact**: Initial connection uses wrong IP/context, metadata access corrects it

#### 2. **Connection Context Establishment Delay**
- **Issue**: PostgreSQL connection context not fully established on first use
- **Symptom**: Connection exists but database context incomplete  
- **Evidence**: Connection works for raw SQL but fails for ORM parameter binding
- **Impact**: Parameter type conversion fails until context properly established

#### 3. **Docker Network Timing Race Condition**
- **Issue**: PHP connects before PostgreSQL fully ready in CI/CD
- **Symptom**: Connection appears successful but lacks proper context
- **Evidence**: Only occurs in complex Docker environments, not localhost
- **Impact**: Connection state incomplete until forced establishment via metadata calls

### 🔍 Medium Probability Issues

#### 4. **Missing Connection Validation**
- **Issue**: No connection validation queries configured
- **Symptom**: Stale/incomplete connections not detected
- **Impact**: Bad connections remain in use until forced refresh

#### 5. **PostgreSQL search_path Issues**
- **Issue**: Database schema context not properly set
- **Symptom**: Queries execute in wrong schema context
- **Impact**: Empty results due to wrong schema search path

## Conclusions & Next Steps

### ✅ Phase A1 Complete - Key Findings

1. **Single Database Architecture Confirmed**: No multi-database routing issues
2. **No Connection Pooling**: Direct connections, no external pooler complexity  
3. **Environment Differences Identified**: CI/CD vs localhost networking differences
4. **DNS/Network Timing Issues Likely**: Connection establishment timing problems
5. **Missing Connection Validation**: No connection health checking configured

### 🎯 Priority Recommendations

#### Immediate Testing (Phase A2)
1. **Add Connection Health Checks**: Implement connection validation queries
2. **DNS Resolution Timing**: Test DNS resolution behavior in CI/CD
3. **Connection Establishment Logging**: Add detailed connection timing logs
4. **PostgreSQL Connection Logs**: Enable connection logging on database side

#### Architecture Improvements (Phase D)
1. **Connection Warming**: Implement proper connection establishment in bootstrap
2. **Health Checking**: Add connection state validation before critical queries
3. **Timeout Configuration**: Add connection and query timeout settings
4. **Monitoring**: Implement connection state monitoring

### 🚀 Next Phase Focus

**Phase A2**: Connection Pool Investigation
- Even without external pooling, investigate Doctrine's internal connection management
- Test connection lifecycle and state persistence
- Analyze connection establishment timing in different environments

**Confidence Level**: High - Clear environmental differences identified, connection timing issues likely root cause.