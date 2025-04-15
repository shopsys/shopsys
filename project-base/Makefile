generate-schema:
	docker compose exec php-fpm php ./bin/console graphql:validate
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker compose cp php-fpm:/var/www/html/schema.graphql /tmp/schema.graphql
	docker compose cp /tmp/schema.graphql storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	docker compose exec storefront npm run gql
	docker compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	cd app; php ./bin/console graphql:validate
	cd app; php phing frontend-api-generate-graphql-schema
	cp app/schema.graphql storefront/schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	cd storefront; npm run gql
	rm -rf storefront/schema.graphql

check-fix: generate-schema php-checks php-lock-icons php-translations storefront-checks storefront-translations

php-checks:
	docker compose exec php-fpm php phing standards-fix phpstan

php-lock-icons:
	docker compose exec php-fpm php bin/console ux:icons:lock

php-translations:
	docker compose exec php-fpm php phing translations-dump

storefront-checks:
	docker compose exec storefront pnpm run check--fix

storefront-translations:
	docker compose exec storefront pnpm run translate

define prepare-data-for-acceptance-tests
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-create test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
endef

.PHONY: prepare-data-for-acceptance-tests
prepare-data-for-acceptance-tests:
	$(call prepare-data-for-acceptance-tests)

define cypress-prepare
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress --force-recreate
endef

.PHONY: cypress-prepare
cypress-prepare:
	$(call cypress-prepare)

define cypress-cleanup
  docker compose stop storefront-cypress
	docker compose rm -f storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef

.PHONY: cypress-cleanup
cypress-cleanup:
	$(call cypress-cleanup)

define run_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=run cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

.PHONY: run-acceptance-tests-base
run-acceptance-tests-base:
	$(call run_acceptance_tests,base)

.PHONY: run-acceptance-tests-actual
run-acceptance-tests-actual:
	$(call run_acceptance_tests,actual)

define selected_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running selected acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=selected cypress || true
	@echo "✅ Selected acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

.PHONY: selected-acceptance-tests-base
selected-acceptance-tests-base:
	$(call selected_acceptance_tests,base)

.PHONY: selected-acceptance-tests-actual
selected-acceptance-tests-actual:
	$(call selected_acceptance_tests,actual)

IS_WSL := $(shell uname -r | grep -i microsoft)

ifeq ($(IS_WSL),)
get_ip = $(shell ifconfig | awk '/^[a-z0-9]+: /{iface=substr($$1, 1, length($$1)-1)} /status: active/{print iface}' | head -1 | xargs -I {} ifconfig {} | awk '/inet /{print $$2; exit}')
else
get_ip = $(shell awk '/nameserver / {print $$2; exit}' /etc/resolv.conf)
endif

define open_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@if [ "$(IS_WSL)" = "" ]; then \
		xhost + $(get_ip); \
	fi
	@echo "▶️ Opening acceptance tests of type $(1)..."
	-docker compose run --rm -e TYPE=$(1) -e DISPLAY=$(get_ip):0 -e COMMAND=open cypress || true
	@echo "✅ Acceptance tests of type $(1) finished."
	$(call cypress-cleanup)
endef

.PHONY: open-acceptance-tests-base
open-acceptance-tests-base:
	$(call open_acceptance_tests,base)

.PHONY: open-acceptance-tests-actual
open-acceptance-tests-actual:
	$(call open_acceptance_tests,actual)

generate-snapshots-info-table:
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Generating snapshots info table..."
	-docker compose exec storefront-cypress npm run generate-snapshots-table --prefix cypress || true
	@echo "✅ Snapshots info table generation finished."
	$(call cypress-cleanup)

.PHONY: run-smoke-tests
run-smoke-tests:
	$(call prepare-data-for-acceptance-tests)
	$(call cypress-prepare)
	@echo "▶️ Running smoke tests..."
	-docker compose run --rm -e TYPE=null -e COMMAND=smoke cypress || true
	@echo "✅ Smoke tests finished."
	$(call cypress-cleanup)
