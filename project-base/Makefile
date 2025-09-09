# ------------------------------------------------------------------------------
# Default target: Help
# ------------------------------------------------------------------------------

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

.PHONY: help generate-schema generate-schema-native check-fix php-checks php-lock-icons php-translations \
	storefront-checks storefront-translations run-acceptance-tests-base \
	run-acceptance-tests-regression selected-acceptance-tests-base selected-acceptance-tests-regression \
	run-specific-test-regression run-specific-test-base \
	open-acceptance-tests-base open-acceptance-tests-regression run-smoke-tests \
	generate-snapshots-info-table _prepare-data-for-acceptance-tests _cypress-prepare _cypress-cleanup \
	check-licenses

# ------------------------------------------------------------------------------
# 🔧 Generating GraphQL schema and types
# ------------------------------------------------------------------------------

generate-schema: ## Generates GraphQL schema and frontend types (in Docker)
	docker compose exec php-fpm php ./bin/console graphql:validate
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker compose cp php-fpm:/var/www/html/schema.graphql /tmp/schema.graphql
	docker compose cp /tmp/schema.graphql storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	docker compose exec storefront npm run gql
	docker compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native: ## Generates GraphQL schema and frontend types (natively)
	cd app; php ./bin/console graphql:validate
	cd app; php phing frontend-api-generate-graphql-schema
	cp app/schema.graphql storefront/schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	cd storefront; npm run gql
	rm -rf storefront/schema.graphql

# ------------------------------------------------------------------------------
# ✅ Code Checks and Fixes (PHP & JS/TS)
# ------------------------------------------------------------------------------

check-fix: generate-schema php-checks php-translations storefront-checks storefront-translations check-licenses ## Runs all code checks (backend & storefront) and attempts to fix issues

php-checks: ## Runs PHP checks (coding standards, PHPStan) and attempts to fix issues
	docker compose exec php-fpm php phing standards-fix phpstan

php-lock-icons: ## Updates the UX icon lock
	docker compose exec php-fpm php bin/console ux:icons:lock

php-translations: ## Updates translation files of the backend
	docker compose exec php-fpm php phing translations-dump

storefront-checks: ## Runs Storefront (JS/TS) checks and attempts to fix issues
	docker compose exec storefront pnpm run check--fix

storefront-translations: ## Updates translation files of the storefront
	docker compose exec storefront pnpm run translate

# ------------------------------------------------------------------------------
# 🧪 Testing & Quality Assurance
# ------------------------------------------------------------------------------

# Internal helper functions (not shown in help)
_prepare-data-for-acceptance-tests:
	$(call prepare-data-for-acceptance-tests)

_cypress-prepare:
	$(call cypress-prepare)

_cypress-cleanup:
	$(call cypress-cleanup)

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
	$(call _prepare-data-for-acceptance-tests)
	$(call _cypress-prepare)
	@echo "▶️ Running acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=run cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call _cypress-cleanup)
endef

define selected_acceptance_tests
	$(call _prepare-data-for-acceptance-tests)
	$(call _cypress-prepare)
	@echo "▶️ Running selected acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=selected cypress || true
	@echo "✅ Selected acceptance tests of type $(1) finished."
	$(call _cypress-cleanup)
endef

define run_specific_acceptance_test
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running specific acceptance test: $(2) of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e SPEC=$(2) cypress || true
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
	$(call _prepare-data-for-acceptance-tests)
	$(call _cypress-prepare)
	@if [ "$(IS_WSL)" = "" ]; then \
		xhost + $(get_ip); \
	fi
	@echo "▶️ Opening acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e DISPLAY=$(get_ip):0 -e COMMAND=open cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call _cypress-cleanup)
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

run-specific-test-base: ## Runs a specific base acceptance test (interactive selection, headless)
	@if [ -z "$(SPEC)" ]; then \
		echo "❌ Error: SPEC parameter is required. Usage: make run-specific-test-base SPEC=e2e/filterAndSort/categoryDetailFilterAndSort.cy.ts"; \
		exit 1; \
	fi
	$(call run_specific_acceptance_test,base,$(SPEC))

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

run-specific-test-actual: ## Runs a specific actual acceptance test (interactive selection, headless)
	@if [ -z "$(SPEC)" ]; then \
		echo "❌ Error: SPEC parameter is required. Usage: make run-specific-test-actual SPEC=e2e/filterAndSort/categoryDetailFilterAndSort.cy.ts"; \
		exit 1; \
	fi
	$(call run_specific_acceptance_test,actual,$(SPEC))

open-acceptance-tests-base: ## Opens the Cypress GUI for debugging base acceptance tests
	$(call open_acceptance_tests,base)

open-acceptance-tests-regression: ## Opens the Cypress GUI for debugging regression acceptance tests
	$(call open_acceptance_tests,regression)

run-smoke-tests: ## Runs smoke tests (Cypress)
	$(call _prepare-data-for-acceptance-tests)
	$(call _cypress-prepare)
	@echo "▶️ Running smoke tests..."
	-docker compose run --rm -e TYPE=null -e COMMAND=smoke cypress || true
	@echo "✅ Smoke tests finished."
	$(call _cypress-cleanup)

# ------------------------------------------------------------------------------
# 📸 Snapshots & Utilities
# ------------------------------------------------------------------------------

generate-snapshots-info-table: ## Generates overview table of Cypress snapshots
	$(call _prepare-data-for-acceptance-tests)
	$(call _cypress-prepare)
	@echo "▶️ Generating snapshots info table..."
	-docker compose exec storefront-cypress npm run generate-snapshots-table --prefix cypress || true
	@echo "✅ Snapshots info table generation finished."
	$(call _cypress-cleanup)
# ------------------------------------------------------------------------------
# 📦 Checking dependencies licenses
# ------------------------------------------------------------------------------

check-licenses: ## Checks dependency licenses in Composer and NPM (php-fpm & storefront)
	@echo "🔍 Checking dependency licenses..."
	@docker compose exec -T php-fpm bash -lc "scripts/check-licenses.sh" && \
	 docker compose exec -T storefront sh -lc "sh scripts/check-licenses.sh" && \
	 echo "✅ All license checks passed"
