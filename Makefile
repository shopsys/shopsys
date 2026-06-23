# ------------------------------------------------------------------------------
# Default target: Help
# ------------------------------------------------------------------------------

TEST_LOCALE ?= en
B2B_DOMAIN_ID ?= 2
B2B_BASE_URL ?= http://127.0.0.2:8000

.DEFAULT_GOAL := help

help: ## Displays list of available commands
	@echo ""
	@echo "Available commands:"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "; printf "  %-40s %s\n", "Command", "Description"; printf "  %-40s %s\n", "----------------------------------------", "-----------"} /^[a-zA-Z0-9_-]+:.*?## / && !/^_/ { printf "  %-40s %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@echo ""

# ------------------------------------------------------------------------------
# Phony targets - declaration of targets that are not files
# ------------------------------------------------------------------------------

.PHONY: help mutagen-up mutagen-up-build mutagen-up-build-no-cache mutagen-stop mutagen-down generate-schema generate-schema-native check-fix php-checks php-lock-icons php-translations \
	storefront-checks storefront-translations check-schema run-acceptance-tests-base \
	run-acceptance-tests-regression selected-acceptance-tests-base selected-acceptance-tests-regression \
	run-specific-test-regression run-specific-test-base \
	open-acceptance-tests-base open-acceptance-tests-regression run-smoke-tests \
	generate-snapshots-info-table prepare-data-for-acceptance-tests cypress-prepare cypress-cleanup \
	check-licenses environment-prod environment-dev

# ------------------------------------------------------------------------------
# 🐳 Docker Compose (macOS with Mutagen)
# ------------------------------------------------------------------------------

mutagen-up: ## Starts Docker environment with Mutagen sync (macOS)
	./project-base/scripts/mutagen-up.sh

mutagen-up-build: ## Starts Docker environment with Mutagen sync and rebuilds images (macOS)
	./project-base/scripts/mutagen-up.sh --build

mutagen-up-build-no-cache: ## Starts Docker environment with Mutagen sync and rebuilds images without cache (macOS)
	./project-base/scripts/mutagen-up.sh --build --no-cache

mutagen-stop: ## Stops Docker environment and Mutagen sync, keeps containers (macOS)
	./project-base/scripts/mutagen-stop.sh

mutagen-down: ## Removes Docker containers and stops Mutagen sync (macOS)
	./project-base/scripts/mutagen-down.sh

# ------------------------------------------------------------------------------
# 🔧 Generating GraphQL schema and types
# ------------------------------------------------------------------------------

generate-schema: ## Generates GraphQL schema and frontend types (in Docker)
	docker compose exec php-fpm php ./bin/console graphql:validate
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker compose cp php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker compose cp /tmp/schema.graphql storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	docker compose exec storefront pnpm run gql
	docker compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native: ## Generates GraphQL schema and frontend types (natively)
	php ./bin/console graphql:validate
	php phing frontend-api-generate-graphql-schema
	cp project-base/app/schema.graphql project-base/storefront/schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	cd project-base/storefront; pnpm run gql
	rm -rf project-base/storefront/schema.graphql

# ------------------------------------------------------------------------------
# 🔁 Switching application environment (backend + storefront)
# ------------------------------------------------------------------------------

# Switches the whole application to the given environment.
# $(1) = backend environment passed to phing (dev|prod)
# $(2) = storefront run command (dev|build)
# The storefront run command lives in the STOREFRONT_RUN_COMMAND env variable in docker-compose.yml
# (read by the storefront entrypoint). The target rewrites that value and recreates the container to apply it.
define switch_environment
	@echo "🔁 Switching whole application to '$(1)' environment..."
	@echo "🐘 Switching backend..."
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=$(1) environment-change
	@echo "🛍️  Setting STOREFRONT_RUN_COMMAND=$(2) in docker-compose.yml and recreating the storefront container..."
	sed -i.bak -E 's@^([[:space:]]*- STOREFRONT_RUN_COMMAND=)(dev|build)[[:space:]]*$$@\1$(2)@' docker-compose.yml && rm -f docker-compose.yml.bak
	@grep -qE '^[[:space:]]*- STOREFRONT_RUN_COMMAND=$(2)$$' docker-compose.yml || { echo "❌ Failed to set STOREFRONT_RUN_COMMAND=$(2) in docker-compose.yml"; exit 1; }
	docker compose up -d --force-recreate storefront
	@echo "✅ Application switched to '$(1)' environment (storefront running '$(2)')."
	@if [ "$(2)" = "build" ]; then echo "ℹ️  Storefront is building the production bundle in the background; follow it with 'docker compose logs -f storefront'."; fi
endef

environment-prod: ## Switches the whole application (backend + storefront) to the production environment
	$(call switch_environment,prod,build)

environment-dev: ## Switches the whole application (backend + storefront) to the development environment
	$(call switch_environment,dev,dev)

# ------------------------------------------------------------------------------
# ✅ Code Checks and Fixes (PHP and JS/TS)
# ------------------------------------------------------------------------------

check-fix: generate-schema php-checks php-translations storefront-checks storefront-knip storefront-translations storefront-styles-for-admin check-licenses generate-icons-for-styleguide ## Runs all code checks (backend & storefront) and attempts to fix issues

php-checks: ## Runs PHP checks (coding standards, PHPStan) and attempts to fix issues
	docker compose exec php-fpm php phing standards-fix phpstan

php-lock-icons: ## Updates the UX icon lock
	docker compose exec php-fpm php bin/console ux:icons:lock

php-translations: ## Updates translation files of the backend
	docker compose exec php-fpm php phing translations-dump

storefront-checks: ## Runs Storefront (JS/TS) checks and attempts to fix issues
	docker compose exec storefront pnpm run check--fix

storefront-knip: ## Runs Storefront knip to check for unused files
	docker compose exec storefront pnpm run knip

storefront-translations: ## Updates translation files of the storefront
	docker compose exec storefront pnpm run translate

storefront-styles-for-admin: ## Rebuilds the storefront styles for admin
	docker compose exec storefront pnpm run compile-tailwind-for-admin

check-schema: ## Checks if generated GraphQL schema is correct
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker compose cp php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker compose cp /tmp/schema.graphql storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	docker compose exec storefront sh check-code-gen.sh

# ------------------------------------------------------------------------------
# 🧪 Testing & Quality Assurance
# ------------------------------------------------------------------------------

define prepare-data-for-acceptance-tests
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-create test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
endef

define cypress-prepare
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress --force-recreate
endef

define cypress-cleanup
	docker compose stop storefront-cypress
	docker compose rm -f storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef

define run_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=run -e TEST_LOCALE=$(TEST_LOCALE) -e B2B_DOMAIN_ID=$(B2B_DOMAIN_ID) -e B2B_BASE_URL=$(B2B_BASE_URL) cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

define selected_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running selected acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=selected -e TEST_LOCALE=$(TEST_LOCALE) -e B2B_DOMAIN_ID=$(B2B_DOMAIN_ID) -e B2B_BASE_URL=$(B2B_BASE_URL) cypress || true
	@echo "✅ Selected acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

define run_specific_acceptance_test
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running specific acceptance test: $(2) of type $(1)..."
	docker compose build cypress
	-docker compose run --rm -e TYPE=$(1) -e SPEC=$(2) -e TEST_LOCALE=$(TEST_LOCALE) -e B2B_DOMAIN_ID=$(B2B_DOMAIN_ID) -e B2B_BASE_URL=$(B2B_BASE_URL) cypress || true
	@echo "✅ Specific acceptance test $(2) of type $(1) finished."
	$(call cypress-cleanup)
endef

IS_WSL := $(shell uname -r | grep -i microsoft)
ifeq ($(IS_WSL),)
    # MacOS or Linux
    get_ip = $(shell ifconfig | awk '/^[a-z0-9]+: /{iface=substr($$1, 1, length($$1)-1)} /status: active/{print iface}' | head -1 | xargs -I {} ifconfig {} | awk '/inet /{print $$2; exit}')
else
    # WSL
    get_ip = $(shell awk '/nameserver / {print $$2; exit}' /etc/resolv.conf)
endif

define open_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@if [ "$(IS_WSL)" = "" ]; then \
		xhost + $(get_ip); \
	fi
	@echo "▶️ Opening acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e DISPLAY=$(get_ip):0 -e COMMAND=open -e TEST_LOCALE=$(TEST_LOCALE) -e B2B_DOMAIN_ID=$(B2B_DOMAIN_ID) -e B2B_BASE_URL=$(B2B_BASE_URL) cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

# Cypress Acceptance Tests

run-acceptance-tests-base: ## Runs the base set of acceptance tests (headless)
	$(call run_acceptance_tests,base)

run-acceptance-tests-regression: ## Runs the regression (project-specific) set of acceptance tests (headless)
	$(call run_acceptance_tests,regression)

selected-acceptance-tests-base: ## Runs selected base acceptance tests (interactive selection, headless)
	$(call selected_acceptance_tests,base)

selected-acceptance-tests-regression: ## Runs selected regression acceptance tests (interactive selection, headless)
	$(call selected_acceptance_tests,regression)

run-specific-test-regression: ## Runs a specific regression acceptance test (interactive selection, headless)
	@if [ -z "$(SPEC)" ]; then \
		echo "❌ Error: SPEC parameter is required. Usage: make run-specific-test-regression SPEC=e2e/filterAndSort/categoryDetailFilterAndSort.cy.ts"; \
		exit 1; \
	fi
	$(call run_specific_acceptance_test,regression,$(SPEC))

run-specific-test-base: ## Runs a specific base acceptance test (interactive selection, headless)
	@if [ -z "$(SPEC)" ]; then \
		echo "❌ Error: SPEC parameter is required. Usage: make run-specific-test-base SPEC=e2e/filterAndSort/categoryDetailFilterAndSort.cy.ts"; \
		exit 1; \
	fi
	$(call run_specific_acceptance_test,base,$(SPEC))

open-acceptance-tests-base: ## Opens the Cypress GUI for debugging base acceptance tests
	$(call open_acceptance_tests,base)

open-acceptance-tests-regression: ## Opens the Cypress GUI for debugging regression acceptance tests
	$(call open_acceptance_tests,regression)

run-smoke-tests: ## Runs smoke tests (Cypress)
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running smoke tests..."
	-docker compose run --rm -e TYPE=null -e COMMAND=smoke -e TEST_LOCALE=$(TEST_LOCALE) -e B2B_DOMAIN_ID=$(B2B_DOMAIN_ID) -e B2B_BASE_URL=$(B2B_BASE_URL) cypress || true
	@echo "✅ Smoke tests finished."
	$(call cypress-cleanup)

# ------------------------------------------------------------------------------
# 📸 Snapshots & Utilities
# ------------------------------------------------------------------------------

generate-snapshots-info-table: ## Generates overview table of Cypress snapshots
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Generating snapshots info table..."
	-docker compose exec storefront-cypress npm run generate-snapshots-table --prefix cypress || true
	@echo "✅ Snapshots info table generation finished."
	$(call cypress-cleanup)

# ------------------------------------------------------------------------------
# 📦 Checking dependencies licenses
# ------------------------------------------------------------------------------

check-licenses: ## Checks dependency licenses in Composer and NPM (php-fpm & storefront)
	@echo "🔍 Checking dependency licenses..."
	@docker compose exec -T php-fpm bash -lc "project-base/app/scripts/check-licenses.sh" && \
	 docker compose exec -T storefront sh -lc "sh scripts/check-licenses.sh" && \
	 echo "✅ All license checks passed"

# ------------------------------------------------------------------------------
# 💅 Compiling Tailwind CSS for admin
# ------------------------------------------------------------------------------

generate-tailwind-for-admin:
	@echo "🚀 Compiling Tailwind CSS for admin..."
	rm -rf project-base/storefront/public/tailwind-for-admin/style.css
	mkdir -p project-base/storefront/public/tailwind-for-admin
	docker compose exec storefront pnpm compile-tailwind-for-admin
	@echo "✅ Tailwind CSS compiled to: project-base/storefront/public/tailwind-for-admin/style.css"
	@echo "🔧 Rebuilding backend admin assets..."
	docker compose exec php-fpm php phing npm-dev
	@echo "🎉 Admin assets rebuilt! Tailwind classes are now available in GrapesJS."

# ------------------------------------------------------------------------------
# 🎨 Icon Generator for Styleguide
# ------------------------------------------------------------------------------

generate-icons-for-styleguide: ## Generates StyleguideIcons component from all icon files
	@echo "🚀 Generating StyleguideIcons component..."
	@echo ""
	@echo "🗑️  Deleting existing file..."
	@rm -f project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "✅ Deleted"
	@echo ""
	@echo "🔄 Generating new component..."
	@echo "/*" > project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo " * This file is auto-generated. Do not edit manually." >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo " * To regenerate this file, run: make generate-icons-for-styleguide" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo " */" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "import { StyleguideSection } from './StyleguideElements';" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@for file in project-base/storefront/components/Basic/Icon/*.tsx; do \
		name=$$(basename "$$file" .tsx); \
		if [ "$$name" != "iconsListGeneratorScript" ]; then \
			echo "import { $$name } from 'components/Basic/Icon/$$name';" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx; \
		fi \
	done
	@echo "" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "export const StyleguideIcons = () => (" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "    <StyleguideSection title=\"Icons\">" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "        <div className=\"grid grid-cols-2 gap-6 md:grid-cols-4 lg:grid-cols-6\">" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@for file in project-base/storefront/components/Basic/Icon/*.tsx; do \
		name=$$(basename "$$file" .tsx); \
		if [ "$$name" != "iconsListGeneratorScript" ]; then \
			echo "            <div className=\"border-border-less flex flex-col items-center gap-2 rounded-lg border p-4 transition-shadow hover:shadow-md\">" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx; \
			echo "                <$$name className=\"size-10\" />" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx; \
			echo "                <span className=\"text-center text-xs break-all\">$$name</span>" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx; \
			echo "            </div>" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx; \
		fi \
	done
	@echo "        </div>" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "    </StyleguideSection>" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo ");" >> project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx
	@echo "✅ Generated $$(ls -1 project-base/storefront/components/Basic/Icon/*.tsx | grep -v Script | wc -l | xargs) icons"
	@echo ""
	@echo "🎉 StyleguideIcons generation complete!"
	@echo "📁 File: project-base/storefront/components/Pages/Styleguide/StyleguideIcons.generated.tsx"
