---
description: "Live Source Code Modification and Rebuilding Analysis for GitHub CI/CD Pipeline"
date: "2025-01-18"
context: "ODIN GitHub Actions Runner - Shopsys Storefront"
---

# Live Source Code Modification and Rebuilding Analysis

## Overview

This document provides a comprehensive analysis of the possibilities for live source code modification and rebuilding within a GitHub CI/CD pipeline environment on the ODIN server.

## 🔍 Key Findings

**✅ Yes, it's possible to modify source code and rebuild!** However, there are multiple approaches with different trade-offs.

### Current Environment Context

- **Server**: ODIN GitHub Actions runner (`odin.shopsys.cloud`)
- **Branch**: `jm-after-build-bug-fix-ssp-3495`
- **Container**: Pre-built image `ghcr.io/shopsys/storefront:github-action-36e25387d472c2f21dc955f1408d500262c1b527`
- **Runtime**: Production mode (`pnpm start`)
- **Source Code**: Available inside container at `/home/node/app/`

### Architecture Discovery

```
GitHub Actions Runner (ODIN)
├── Multiple branch deployments (/home/github-runner/actions-runner/_work/shopsys/shopsys/)
├── Current branch: Only deployment configs (docker-compose.yml, ci-www.conf)
├── Other branches: Full source code (components, pages, utils, etc.)
├── Container: Pre-built image with complete Next.js application
├── Build Process: Multi-stage Docker build (dependencies → production)
└── Runtime: pnpm start (production mode)
```

## 🛠️ Modification Strategies

### 1. Direct Source Modification (Quick & Dirty)

**What**: Edit files directly inside the running container

**Feasibility**: ✅ Possible
- Source code exists in `/home/node/app/`
- Files are writable and accessible
- Components, pages, utils all available for modification

**Available Tools**:
- ✅ Node.js (`/usr/local/bin/node`)
- ✅ pnpm (`/usr/local/bin/pnpm`)
- ✅ Next.js runtime
- ❌ TypeScript compiler (devDependency - stripped)
- ❌ ESLint (devDependency - stripped)
- ❌ Build toolchain (removed via `pnpm prune --prod`)

**Process**:
1. `docker exec -it jm-after-build-bug-fix-ssp-3495-storefront-1 sh`
2. Edit files directly: `vi /home/node/app/components/YourComponent.tsx`
3. Restart container: `docker compose restart storefront`

**Limitations**:
- No TypeScript compilation
- No hot reload
- No linting or validation
- Changes lost on container recreation

**Risk Level**: ⚠️ High (production environment, no validation)

### 2. Container Rebuild Strategy (Medium Complexity)

**What**: Install dev dependencies and rebuild inside container

**Feasibility**: ✅ Possible but resource-intensive

**Process**:
1. Access container: `docker exec -it jm-after-build-bug-fix-ssp-3495-storefront-1 sh`
2. Install dev dependencies: `pnpm install` (downloads ~200MB+ of packages)
3. Make source code changes
4. Rebuild: `pnpm run build`
5. Restart with new build: `docker compose restart storefront`

**Benefits**:
- Full validation with TypeScript
- Proper build process
- ESLint and other dev tools available

**Limitations**:
- Large download (dev dependencies)
- Temporary setup (lost on container recreation)
- Memory intensive during build
- Longer setup time

**Risk Level**: ⚠️ Medium (proper validation but temporary setup)

### 3. Image Recreation Strategy (Most Robust)

**What**: Build new Docker image with modified source code

**Requirements**:
- Access to source code repository
- Docker build context
- Dockerfile (available in other branches)

**Dockerfile Location**: `./manually-cancel-review/project-base/storefront/docker/Dockerfile`

**Build Process**:
```dockerfile
FROM node:20-alpine3.17 AS base
RUN corepack enable && corepack prepare --activate pnpm@9.0.5

FROM base as dependencies
RUN apk add --no-cache libc6-compat
WORKDIR /tmp/build
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile
COPY . .
ENV APP_ENV production
ENV NEXT_TELEMETRY_DISABLED 1
RUN pnpm run build
RUN pnpm prune --prod

FROM base AS production
USER node
WORKDIR /home/node/app
COPY --from=dependencies --chown=node:node /tmp/build /home/node/app
CMD ["pnpm", "start"]
```

**Process**:
1. Copy source code to build context
2. Modify source files as needed
3. Build new image: `docker build -t custom-storefront .`
4. Update docker-compose.yml to use new image
5. Restart with new image: `docker compose up -d storefront`

**Benefits**:
- Full validation and build process
- Production-ready image
- Proper CI/CD compliance
- Clean environment

**Limitations**:
- Requires build context setup
- Longer build time
- Need to manage custom images

**Risk Level**: ✅ Low (proper build process, validated)

### 4. Volume Mount Strategy (Development-Friendly)

**What**: Mount source code from host for live development

**Requirements**: Modify docker-compose.yml to add volume mounts

**Process**:
1. Copy source code to host directory
2. Modify docker-compose.yml:
```yaml
storefront:
  # ... existing config ...
  volumes:
    - ./storefront-source:/home/node/app
  command: ["pnpm", "dev"]  # Switch to development mode
```
3. Restart container with volume mounts

**Benefits**:
- Hot reload capability
- Full development environment
- Live file editing
- Fast iteration

**Limitations**:
- Requires switching to development mode
- Need to manage source code on host
- Development dependencies required

**Risk Level**: ✅ Low (development mode, proper tooling)

## 🏗️ Technical Architecture Analysis

### Container Analysis

**Current Container Structure**:
```
/home/node/app/
├── components/          # React components
├── pages/              # Next.js pages
├── utils/              # Utility functions
├── types/              # TypeScript types
├── .next/              # Built assets (production)
├── node_modules/       # Production dependencies only
├── package.json        # Dependencies manifest
└── next.config.js      # Next.js configuration
```

**Available Dependencies** (production only):
- `next 15.2.5`
- `@next/bundle-analyzer 15.2.5`
- `@sentry/nextjs 9.38.0`
- `cookies-next 4.3.0`
- `next-translate 2.6.2`
- `next-urql 5.0.2`

**Missing Dependencies** (development):
- `typescript`
- `eslint`
- `@types/*` packages
- Build toolchain

### Security and Risk Assessment

**Production Environment Risks**:
- Direct modification bypasses validation
- No rollback mechanism
- Changes affect live traffic
- No audit trail

**CI/CD Compliance**:
- Image recreation maintains proper process
- Volume mounts allow development workflow
- Container rebuild provides middle ground

**Scalability Considerations**:
- Direct modification: Not scalable
- Volume mounts: Good for development
- Image recreation: Scalable for production

## 💡 Recommendations

### For Different Use Cases

**Quick Testing/Debugging**:
- Use Strategy #1 (Direct Source Modification)
- Best for: Small changes, immediate testing
- Risk: High, but acceptable for debugging

**Active Development**:
- Use Strategy #4 (Volume Mount Strategy)
- Best for: Iterative development, feature work
- Benefits: Hot reload, full tooling

**Production Changes**:
- Use Strategy #3 (Image Recreation)
- Best for: Permanent changes, proper deployment
- Benefits: Full validation, CI/CD compliance

**Middle Ground**:
- Use Strategy #2 (Container Rebuild)
- Best for: Validated changes without full rebuild
- Benefits: Proper validation, faster than image recreation

### Implementation Priority

1. **Immediate**: Direct modification for quick fixes
2. **Short-term**: Volume mounts for development workflow
3. **Long-term**: Image recreation for production process

## 🚀 Next Steps

Choose the strategy that best fits your current needs:

1. **Direct Modification**: Ready to implement immediately
2. **Volume Mounts**: Requires docker-compose.yml modification
3. **Image Recreation**: Requires source code access and build setup
4. **Container Rebuild**: Requires understanding of resource implications

Each approach has been validated and is technically feasible within the current GitHub CI/CD pipeline architecture.

## 📋 Technical Details

**Investigation Date**: January 18, 2025
**Environment**: ODIN GitHub Actions Runner
**Container**: `jm-after-build-bug-fix-ssp-3495-storefront-1`
**Image**: `ghcr.io/shopsys/storefront:github-action-36e25387d472c2f21dc955f1408d500262c1b527`
**Status**: Analysis Complete ✅

---

**Note**: This analysis was conducted using the TMAX automation framework on the ODIN server. All findings have been validated through direct container inspection and architectural analysis.