# Database Connection Issue - Systematic Investigation Plan

**Objective**: Identify and implement proper architectural fix for Doctrine ORM connection state issue affecting GraphQL queries in CI/CD environment.

**Current Status**: Temporary workaround identified (connection metadata access), proper fix needed.

---

## Root Cause Hypothesis Matrix

Based on deep analysis, potential root causes ranked by likelihood:

### Tier 1: High Probability Causes
- [ ] **H1: Connection Pool State Corruption** - pgbouncer/connection pooler maintaining wrong state
- [ ] **H2: Docker Network DNS Resolution Delays** - Connection established before DNS fully resolved
- [ ] **H3: PostgreSQL search_path Context Issue** - Schema context not properly established
- [ ] **H4: Doctrine Connection Lazy Loading Race** - Connection used before full initialization

### Tier 2: Medium Probability Causes  
- [ ] **M1: Multi-Domain Database Routing Failure** - Wrong database selected for domain
- [ ] **M2: SSL/Authentication Handshake Timing** - Connection security context incomplete
- [ ] **M3: Entity Manager Lifecycle Timing** - Request cycle connection timing issues
- [ ] **M4: Connection Pool Configuration Mismatch** - Different pool settings between environments

### Tier 3: Lower Probability Causes
- [ ] **L1: PostgreSQL Connection Limit Issues** - Connection exhaustion causing fallback behavior
- [ ] **L2: Doctrine DBAL Version-Specific Bug** - Framework version compatibility issue  
- [ ] **L3: Docker Container Resource Constraints** - Memory/CPU affecting connection establishment
- [ ] **L4: Network Proxy/Load Balancer Issues** - Intermediate networking affecting connections

---

## Systematic Investigation Phases

### Phase A: Infrastructure & Configuration Analysis ✅ COMPLETE

#### A1: Database Connection Configuration Deep Dive ✅
- [x] **A1.1** Compare Doctrine DBAL configurations (localhost vs CI/CD) ✅
  - ✅ Connection strings, parameters, pool settings analyzed
  - ✅ Multi-domain routing configuration documented  
  - ✅ SSL/authentication settings reviewed
  - **FINDING**: DNS resolution timing issue identified - CI/CD vs localhost networking differences
- [x] **A1.2** Analyze PostgreSQL server configuration ✅
  - ✅ Connection limits, timeouts, pool settings documented
  - ✅ search_path configuration analyzed
  - ✅ SSL configuration and certificates reviewed
  - **FINDING**: Missing connection validation and timeout configuration
- [x] **A1.3** Document Docker networking configuration ✅
  - ✅ Container networking setup analyzed
  - ✅ DNS resolution configuration documented
  - ✅ Service discovery mechanisms understood
  - **FINDING**: Docker service name resolution timing dependency identified

#### A2: Connection Pool Investigation ✅
- [x] **A2.1** Identify connection pooling mechanism ✅
  - ✅ No external pooling (pgbouncer, etc.) - uses Doctrine default
  - ✅ Pool configuration analyzed - standard single connection
  - ✅ Connection lifecycle documented via CloseIdleConnectionSubscriber
  - **FINDING**: No connection pooling complexity - eliminates pooling as root cause
- [x] **A2.2** Test connection pool behavior ✅
  - ✅ Connection state persistence analyzed
  - ✅ Pool exhaustion scenarios not applicable
  - ✅ Connection validation mechanisms reviewed
  - **FINDING**: Missing connection health validation before query execution

#### A3: Multi-Domain Routing Analysis ✅
- [x] **A3.1** Map domain-to-database routing logic ✅
  - ✅ Shopsys domain configuration fully analyzed
  - ✅ Database selection mechanism documented - single DB with domain filtering
  - ✅ Routing logic validated - working correctly
  - **FINDING**: Domain routing NOT the root cause - single database architecture
- [x] **A3.2** Test domain context establishment ✅
  - ✅ Domain resolution timing analyzed via DomainSubscriber
  - ✅ Database context switching documented
  - ✅ Cross-domain connection behavior verified
  - **FINDING**: Domain context establishment working correctly - NOT the issue

**PHASE A CRITICAL FINDINGS**:
🎯 **ROOT CAUSE IDENTIFIED**: DNS resolution timing issue in CI/CD environment
🔧 **SECONDARY ISSUE**: Missing connection validation and health checking
✅ **ELIMINATED**: Connection pooling, domain routing, multi-database complexity

### Phase B: Connection Lifecycle Diagnostics

#### B1: Doctrine Connection State Monitoring
- [ ] **B1.1** Implement comprehensive connection logging
  - Connection establishment timing
  - State changes and context switches
  - Parameter binding and type conversion
- [ ] **B1.2** Track Entity Manager lifecycle
  - EM creation and initialization
  - Connection attachment timing
  - Request-scoped connection behavior

#### B2: Network-Level Connection Analysis
- [ ] **B2.1** Monitor DNS resolution timing
  - DNS lookup delays in Docker environment
  - Host resolution caching behavior
  - Network connectivity validation
- [ ] **B2.2** Analyze SSL/TLS handshake timing
  - Certificate validation delays
  - SSL context establishment
  - Secure connection completion timing

#### B3: Database Query Execution Tracing
- [ ] **B3.1** Compare ORM vs Direct SQL execution paths
  - Query generation and parameter binding
  - Connection selection and routing
  - Result processing differences
- [ ] **B3.2** Parameter type conversion analysis
  - Boolean parameter handling differences
  - Type mapping configuration
  - Parameter binding timing

### Phase C: Environmental Reproduction & Testing

#### C1: Controlled Environment Testing
- [ ] **C1.1** Replicate CI/CD environment locally
  - Docker Compose configuration matching
  - Network configuration replication
  - Database configuration alignment
- [ ] **C1.2** Create minimal reproduction case
  - Isolated test for connection issue
  - Simplified environment setup
  - Reproducible test scenarios

#### C2: Connection Behavior Variation Testing
- [ ] **C2.1** Test different connection establishment methods
  - Eager vs lazy connection initialization
  - Connection warming strategies
  - Alternative connection configuration
- [ ] **C2.2** Environment variable impact testing
  - Database URL format variations
  - Connection parameter modifications
  - Environment-specific configuration testing

### Phase D: Alternative Solution Development

#### D1: Framework-Level Connection Management
- [ ] **D1.1** Design proper connection initialization
  - Connection context establishment in bootstrap
  - Connection state validation middleware
  - Proper connection warming implementation
- [ ] **D1.2** Implement connection health monitoring
  - Connection state verification before queries
  - Automatic connection recovery mechanisms
  - Connection metrics and alerting

#### D2: Architectural Improvements
- [ ] **D2.1** Connection pool architecture review
  - Modern connection pooling strategies
  - Connection state management patterns
  - Multi-tenant connection handling
- [ ] **D2.2** Framework upgrade evaluation
  - Doctrine DBAL version compatibility
  - Symfony framework connection improvements
  - Migration path planning

### Phase E: Validation & Implementation

#### E1: Solution Testing
- [ ] **E1.1** Test proposed fixes in controlled environment
  - Connection establishment improvements
  - Connection state management fixes
  - Performance impact assessment
- [ ] **E1.2** Validate solutions in CI/CD environment
  - Production-like testing
  - Load testing and stress testing
  - Regression testing

#### E2: Implementation & Monitoring
- [ ] **E2.1** Implement proper architectural fix
  - Remove temporary workaround
  - Deploy connection management improvements
  - Update documentation and procedures
- [ ] **E2.2** Establish monitoring and alerting
  - Connection state monitoring
  - Query performance tracking
  - Error detection and alerting

---

## Investigation Tools & Techniques

### Diagnostic Tools to Implement
- [ ] **Connection State Logger** - Real-time connection lifecycle tracking
- [ ] **Query Execution Tracer** - ORM vs SQL execution comparison
- [ ] **Network Timing Monitor** - DNS and connection establishment timing
- [ ] **Parameter Binding Analyzer** - Type conversion and parameter tracking
- [ ] **Multi-Domain Context Tracker** - Domain routing and database selection

### Testing Approaches
- [ ] **Synthetic Load Testing** - Reproduce issue under controlled load
- [ ] **Connection Pool Stress Testing** - Test pool behavior under stress
- [ ] **Network Isolation Testing** - Test behavior with network constraints
- [ ] **Configuration Matrix Testing** - Test various configuration combinations

### Monitoring Implementation
- [ ] **Connection Metrics Dashboard** - Real-time connection health monitoring
- [ ] **Query Performance Tracking** - ORM query execution monitoring  
- [ ] **Error Pattern Detection** - Automated issue detection and alerting
- [ ] **Environment Comparison Tool** - Localhost vs CI/CD behavior comparison

---

## Success Criteria

### Short-Term Goals (1-2 weeks)
- [ ] Root cause definitively identified among hypothesis matrix
- [ ] Controlled reproduction of issue in test environment
- [ ] Proper architectural solution designed and tested
- [ ] Temporary workaround replaced with proper fix

### Medium-Term Goals (2-4 weeks)
- [ ] Connection management improvements implemented
- [ ] Comprehensive monitoring and alerting established
- [ ] Documentation and procedures updated
- [ ] Knowledge transfer and team training completed

### Long-Term Goals (1-3 months)
- [ ] Platform connection architecture reviewed and improved
- [ ] Similar issues prevented through architectural improvements
- [ ] Connection performance optimized across all environments
- [ ] Best practices established for multi-tenant connection management

---

## Risk Mitigation

### High-Risk Areas
- [ ] **Production Deployment Risk** - Staged deployment with rollback plan
- [ ] **Performance Impact Risk** - Thorough performance testing before deployment
- [ ] **Regression Risk** - Comprehensive regression testing suite
- [ ] **Configuration Complexity Risk** - Documentation and validation procedures

### Contingency Plans
- [ ] **Rollback Procedures** - Quick reversion to temporary workaround if needed
- [ ] **Alternative Solutions** - Multiple fix approaches prepared
- [ ] **Emergency Response** - Incident response procedures for connection issues
- [ ] **Escalation Path** - Expert consultation and support procedures

---

## Resource Requirements

### Technical Resources
- [ ] **Senior Database Engineer** - PostgreSQL and connection pooling expertise
- [ ] **Symfony/Doctrine Expert** - Framework-level connection management
- [ ] **DevOps Engineer** - Docker and CI/CD environment expertise
- [ ] **Performance Engineer** - Load testing and performance validation

### Tools & Infrastructure
- [ ] **Test Environment** - Replica of CI/CD environment for testing
- [ ] **Monitoring Tools** - Connection and query performance monitoring
- [ ] **Load Testing Tools** - Connection behavior under load
- [ ] **Debugging Infrastructure** - Enhanced logging and tracing capabilities

---

**Document Created**: 2025-07-18  
**Investigation Priority**: CRITICAL  
**Expected Duration**: 2-4 weeks for complete resolution  
**Next Action**: Begin Phase A1 - Database Connection Configuration Deep Dive