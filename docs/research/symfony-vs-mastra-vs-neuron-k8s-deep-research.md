# Architektonický Deep Research: Symfony vs. Mastra vs. Neuron

Datum vyhodnocení: 9. března 2026
Kontext: Symfony 7.2+, PHP 8.4, lokálně Docker Compose, produkce Kubernetes

Tento dokument porovnává 3 integrační cesty AI agenta:
- Mastra (TypeScript) jako samostatná microservice vedle Symfony
- Symfony AI (oficiální iniciativa)
- Neuron AI (PHP framework)

## Executive Summary

- Pokud je priorita rychlé dodání pokročilých agentních funkcí (workflow suspend/resume, streaming, observability), Mastra dává nejlepší time-to-feature.
- Pokud je priorita nízká provozní složitost, jednodušší ownership a jeden stack, čistě PHP přístup vyhrává.
- Z čistě PHP variant je Symfony AI strategicky nejvíc „future-proof“ v Symfony ekosystému, ale k 9. 3. 2026 je stále ve fázi rychlé evoluce (komponenty jsou označeny jako experimental).
- Neuron AI je pragmatická PHP alternativa pro workflow + human-in-the-loop bez Node microservice režie.

## 1. Deployment & Networking v Kubernetes

### 1.1 Mastra Hybrid (Symfony + Mastra)

Doporučený komunikační model:
- Symfony Pod -> Mastra Pod přes interní `ClusterIP` Service
- Primárně REST/SSE (jednodušší debugging, kompatibilita s browser streamingem)
- gRPC používat jen pokud chcete striktně typovaný kontrakt service-to-service a neřešíte browserové SSE na hraně

Doporučení pro sdílení kontextu:
- Nepoužívat Redis sidecar per pod pro sdílený chat state
- Použít samostatnou Redis service (nebo Redis cluster) jako sdílený state/cache layer
- Kanonický long-term stav konverzace držet v PostgreSQL

Proč:
- Sidecar Redis rozbíjí sdílení stavu mezi replikami
- Samostatný Redis + Postgres udrží konzistenci při horizontálním škálování

### 1.2 PHP Native (Symfony AI / Neuron)

Škálování agentů:
- Držet web pody bezstavové
- Konverzační historii a workflow checkpointy ukládat mimo pod (Redis/Postgres)
- Dlouhé LLM volání přes worker pattern (Messenger queue) pro robustní provoz

Liveness/readiness v K8s:
- Probes musí mířit na lightweight health endpoint (nikdy ne na LLM call)
- Readiness řeší připravenost přijímat traffic, ne dokončení dlouhého requestu
- U streamingu nepoužívat krátké timeouty na ingress/mesh route

## 2. Workflows & State Machines

### 2.1 Srovnání přístupů

Mastra (Node/Edge workflow model):
- Velmi silná orchestrace pro agentní scénáře
- Nativní suspend/resume workflow a stream-resume pattern
- Dobré pro „čekání na uživatele“ napříč distribuovaným během

Symfony Workflow:
- Stabilní state machine engine, ale AI orchestrace je víc „build-your-own"
- Přerušení workflow je možné, ale vyžaduje vlastní persistenci + korelaci událostí
- Silné pro business process flow, méně out-of-the-box pro agentní runtime

Neuron AI Nodes:
- Human-in-the-loop + interruption/resume je přímo součást workflow modelu
- PHP-native ergonomie, menší integrační tření než hybrid
- Menší ekosystém než Mastra, ale dobrý kompromis mezi schopnostmi a jednoduchostí

### 2.2 Kdo lépe zvládá „přerušení“ v distribuovaném provozu

Pořadí pro advanced AI workflow interruption:
1. Mastra
2. Neuron AI
3. Symfony Workflow (bez custom nadstavby)

## 3. Produkční Observability („Mastra feel“) v K8s

### 3.1 Jednotná trace mapa pro Mastra + Symfony

Doporučený stack:
- OpenTelemetry SDK v Node (Mastra) i PHP (Symfony/Neuron)
- OTel Collector jako DaemonSet nebo Deployment (gateway pattern)
- Export do Jaeger (traces) + Sentry (APM/AI pohled)

Klíč:
- Propagovat `traceparent` mezi Symfony <-> Mastra voláními
- Nemitigovat traces separátně podle jazyka; agregovat přes jeden Collector pipeline

### 3.2 Jak zobrazit tokeny, latency, tool calls

- Token usage zapisovat jako span attributes z provider response (`input_tokens`, `output_tokens`, `model`, `provider`)
- Tool call vytvářet jako child spans (`tool.name`, `tool.latency_ms`, `tool.status`)
- Pro real-time monitoring mít dashboard nad Collector exportem (Jaeger/Sentry)

Poznámka k aktuálnímu stavu v tomto repo:
- `mastra-service/src/mastra/index.ts` používá observability exportéry, ale storage je zde nastavené na `:memory:`
- Pro produkci je potřeba persistentní store (Postgres/Redis/LibSQL file podle cíle)

## 4. Streaming & Memory (Persistence)

### 4.1 Streaming přes service mesh bez bufferování

Aby SSE nebufferovalo po cestě:
- Ingress (NGINX):
  - `nginx.ingress.kubernetes.io/proxy-buffering: "off"`
  - `nginx.ingress.kubernetes.io/proxy-request-buffering: "off"`
  - `nginx.ingress.kubernetes.io/proxy-read-timeout: "3600"`
  - `nginx.ingress.kubernetes.io/proxy-http-version: "1.1"`
- Aplikace musí vracet:
  - `Content-Type: text/event-stream`
  - `Cache-Control: no-cache`
  - `X-Accel-Buffering: no`
- Istio/Linkerd route timeouty nastavit pro stream-friendly chování (neukončovat dlouhé odpovědi)

Poznámka k aktuálnímu stavu v repo:
- `project-base/docker/nginx/nginx.conf` má `proxy_http_version 1.1` a dlouhé timeouty
- Pro robustní SSE je vhodné explicitně přidat anti-buffering direktivy i v produkčním ingressu

### 4.2 Distributed Memory schéma (Mastra + Symfony společně)

Doporučený pattern:
- PostgreSQL = source of truth (chat messages, workflow checkpoints, tool audit)
- Redis = hot context cache + locks + short-term session state

Příklad datového modelu:
- `chat_threads(id, tenant_id, user_id, channel, created_at, updated_at)`
- `chat_messages(id, thread_id, role, content_json, token_in, token_out, created_at)`
- `workflow_runs(id, thread_id, engine, state, checkpoint_json, waiting_for, updated_at)`
- `tool_calls(id, run_id, tool_name, args_json, result_json, latency_ms, status, created_at)`

Pravidla:
- Thread ID musí být cross-stack kompatibilní (stejné ID používá Symfony i Mastra)
- Resource scoping přes tenant/user namespace (`tenant:{id}:user:{id}`)
- Při restartu podu se vše obnoví z Postgres/Redis, ne z lokálního FS

## 5. Tool Calling & Security

### 5.1 Jak bezpečně volat Symfony služby z Mastry

Preferovaný model:
- Mastra nevolá DB napřímo
- Mastra volá interní Symfony API (private service, mTLS/JWT, network policy)
- Domain logika a autorizace zůstává v Symfony boundary

Proč:
- Přímý DB přístup z Mastry porušuje zapouzdření domény
- Obchází business pravidla, ACL a audit vrstvy
- Zvyšuje riziko nekompatibility při změně schématu

Výjimka (read-only analytika):
- Pokud nutné, jen přes separátní read-only DB user
- Omezit na views/materialized views
- Bez write práv, bez přístupu k citlivým tabulkám

## Rozhodovací Matice (1-10)

Skóre: 10 = nejlepší.

| Varianta | Rychlost vývoje | K8s overhead | Observability | Udržitelnost | Celkem |
|---|---:|---:|---:|---:|---:|
| Mastra (hybrid TS+PHP) | 9 | 4 | 9 | 6 | 28 |
| Symfony AI (native PHP) | 6 | 9 | 7 | 8 | 30 |
| Neuron AI (native PHP) | 8 | 8 | 6 | 7 | 29 |

Interpretace:
- Mastra vyhrává, pokud potřebujete nejrychleji dodat advanced AI capabilities.
- Symfony AI vyhrává, pokud optimalizujete dlouhodobě na nízkou platform complexity.
- Neuron je silný middle-ground pro PHP-only tým.

## K8s Architektonický Diagram (Mermaid)

### A) Hybrid model (Symfony + Mastra)

```mermaid
flowchart LR
  U[User / Browser] --> IG[Ingress + Service Mesh]
  IG --> SYM[Symfony Web Pod]
  IG --> MAS[Mastra Pod]

  SYM <--> MAS

  SYM --> REDIS[(Redis Service)]
  MAS --> REDIS

  SYM --> PG[(PostgreSQL)]
  MAS --> PG

  SYM --> OTEL[OTel Collector]
  MAS --> OTEL

  OTEL --> JAEGER[Jaeger]
  OTEL --> SENTRY[Sentry]
```

### B) Native PHP model (Symfony AI / Neuron)

```mermaid
flowchart LR
  U[User / Browser] --> IG[Ingress + Service Mesh]
  IG --> WEB[Symfony Web Pod]
  WEB --> WORKER[Symfony Worker Pod]

  WEB --> REDIS[(Redis Service)]
  WORKER --> REDIS

  WEB --> PG[(PostgreSQL)]
  WORKER --> PG

  WEB --> OTEL[OTel Collector]
  WORKER --> OTEL

  OTEL --> JAEGER[Jaeger]
  OTEL --> SENTRY[Sentry]
```

## Docker Compose ukázka (lokální Symfony + Mastra + Redis + OTel)

```yaml
services:
  php-fpm:
    image: shopsys-php-fpm:dev
    environment:
      OTEL_EXPORTER_OTLP_ENDPOINT: http://otel-collector:4318
      OTEL_SERVICE_NAME: symfony-app

  webserver:
    image: nginx:1.27-alpine
    depends_on:
      - php-fpm
      - mastra
    ports:
      - "8000:8080"

  mastra:
    build:
      context: ./mastra-service
    environment:
      OTEL_EXPORTER_OTLP_ENDPOINT: http://otel-collector:4318
      OTEL_SERVICE_NAME: mastra-service
      REDIS_URL: redis://redis:6379
      DATABASE_URL: postgresql://root:root@postgres:5432/shopsys
    ports:
      - "4111:4111"
    depends_on:
      - redis
      - otel-collector

  redis:
    image: redis:7.4-alpine

  otel-collector:
    image: otel/opentelemetry-collector-contrib:0.121.0
    command: ["--config=/etc/otelcol-contrib/config.yaml"]
    volumes:
      - ./docker/otel/collector-config.yaml:/etc/otelcol-contrib/config.yaml:ro
    ports:
      - "4317:4317"
      - "4318:4318"

  jaeger:
    image: jaegertracing/all-in-one:1.62
    ports:
      - "16686:16686"
      - "14250:14250"
```

Doporučené minimum pro `collector-config.yaml`:

```yaml
receivers:
  otlp:
    protocols:
      grpc:
      http:

processors:
  batch:

exporters:
  debug:
  otlp/jaeger:
    endpoint: jaeger:4317
    tls:
      insecure: true

service:
  pipelines:
    traces:
      receivers: [otlp]
      processors: [batch]
      exporters: [debug, otlp/jaeger]
```

## Verdikt

Microservice režie Mastry se vyplatí když:
- Potřebujete teď hned advanced agent orchestration (suspend/resume, streaming workflows, bohaté traces)
- Máte tým, který bez problému provozuje Node + PHP v jednom K8s clusteru
- Přínos rychlosti dodání AI funkcí převáží vyšší provozní komplexitu

Čisté PHP (Symfony AI / Neuron) je lepší když:
- Priorita je jednoduchý provoz, jeden stack, nižší K8s overhead
- Chcete minimalizovat cross-language ownership a incident surface
- Agentní scénáře jsou středně komplexní nebo je můžete stavět iterativně

Pragmatické rozhodnutí pro tento kontext:
- Pokud roadmapa obsahuje heavy AI orchestration v nejbližším kvartálu: zvolit Mastra hybrid, ale striktně přes interní API boundary a společný OTel pipeline.
- Pokud roadmapa preferuje platform simplicity a dlouhodobou maintainability: zůstat v PHP (Symfony AI nebo Neuron), s důrazem na externí persistenci stavu a jednotnou observability.

## Zdroje

- Symfony AI initiative: https://symfony.com/blog/kicking-off-the-symfony-ai-initiative
- Symfony AI v0.1.0 release: https://symfony.com/blog/symfony-ai-v0-1-0-first-tagged-release
- Symfony AI repository: https://github.com/symfony/ai
- Mastra workflows overview: https://mastra.ai/docs/workflows/overview
- Mastra suspend/resume workflows: https://mastra.ai/docs/workflows/suspend-and-resume
- Mastra workflow streaming: https://mastra.ai/docs/streaming/workflow-streaming
- Mastra memory storage: https://mastra.ai/docs/memory/storage
- Mastra OTEL exporters: https://mastra.ai/docs/observability/tracing/exporters/otel
- Neuron AI human-in-the-loop: https://docs.neuron-ai.dev/v2/workflow/human-in-the-loop.md
- Neuron AI workflow persistence: https://docs.neuron-ai.dev/v2/workflow/persistence.md
- Neuron AI workflow streaming: https://docs.neuron-ai.dev/v2/workflow/streaming.md
- Kubernetes probes: https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-startup-probes/
- Istio request timeouts: https://istio.io/latest/docs/tasks/traffic-management/request-timeouts/
- OTel PHP docs: https://opentelemetry.io/docs/languages/php/
- OTel Collector docs: https://opentelemetry.io/docs/collector/
- Ingress NGINX annotations: https://kubernetes.github.io/ingress-nginx/user-guide/nginx-configuration/annotations/
- Sentry distributed tracing: https://docs.sentry.io/concepts/key-terms/tracing/distributed-tracing/
- Sentry AI monitoring: https://docs.sentry.io/ai/monitoring/agents/getting-started/
- Jaeger architecture: https://www.jaegertracing.io/docs/2.5/architecture/

## Repo-specific technické poznámky

- Aktuální reverse proxy na Mastru je v `project-base/docker/nginx/nginx.conf` (`/mastra/* -> mastra:4111`).
- Aktuální Mastra config je v `mastra-service/src/mastra/index.ts`.
- Aktuální SQL agent memory je v `mastra-service/src/mastra/agents/sql-agent.ts`.

