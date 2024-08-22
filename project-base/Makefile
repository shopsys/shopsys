generate-schema:
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	docker compose exec storefront npm run gql
	docker compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	cd app; php phing frontend-api-generate-graphql-schema
	cp app/schema.graphql storefront/schema.graphql
	find project-base/storefront/graphql/requests -type f -name "*.generated.tsx" -exec rm {} \;
	cd storefront; npm run gql
	rm -rf storefront/schema.graphql

define prepare-data-for-acceptance-tests
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-create test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
endef

.PHONY: prepare-data-for-acceptance-tests
prepare-data-for-acceptance-tests:
	$(call prepare-data-for-acceptance-tests)

define run_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress --force-recreate
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=run cypress;
	docker rm -f shopsys-framework-storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef

.PHONY: run-acceptance-tests-base
run-acceptance-tests-base:
	$(call run_acceptance_tests,base)

.PHONY: run-acceptance-tests-actual
run-acceptance-tests-actual:
	$(call run_acceptance_tests,actual)

define selected_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress --force-recreate
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=selected cypress;
	docker rm -f shopsys-framework-storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
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
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress --force-recreate
	@if [ "$(IS_WSL)" = "" ]; then \
		xhost + $(get_ip); \
	fi
	-docker compose run --rm -e TYPE=$(1) -e DISPLAY=$(get_ip):0 -e COMMAND=open cypress;
	docker rm -f shopsys-framework-storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef


.PHONY: open-acceptance-tests-base
open-acceptance-tests-base:
	$(call open_acceptance_tests,base)

.PHONY: open-acceptance-tests-actual
open-acceptance-tests-actual:
	$(call open_acceptance_tests,actual)
