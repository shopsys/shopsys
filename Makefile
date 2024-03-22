generate-schema:
	docker-compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker-compose exec -u root storefront chown node:node schema.graphql
	docker-compose exec storefront pnpm run gql
	docker-compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	php phing frontend-api-generate-graphql-schema
	cp project-base/app/schema.graphql project-base/storefront/schema.graphql
	cd project-base/storefront; pnpm run gql
	rm -rf project-base/storefront/schema.graphql

check-schema:
	docker-compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/project-base/app/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker-compose exec -u root storefront chown node:node schema.graphql
	docker-compose exec storefront sh check-code-gen.sh

prepare-data-for-acceptance-tests:
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export

define run_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress
	-docker compose run --rm -e TYPE=$(1) -e COMMAND=run cypress;
	docker compose stop storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef

.PHONY: run-acceptance-tests-base
run-acceptance-tests-base:
	$(call run_acceptance_tests,base)

.PHONY: run-acceptance-tests-actual
run-acceptance-tests-actual:
	$(call run_acceptance_tests,actual)

get_ip = $(shell ifconfig | awk '/^[a-z0-9]+: /{iface=substr($$1, 1, length($$1)-1)} /status: active/{print iface}' | head -1 | xargs -I {} ifconfig {} | awk '/inet /{print $$2; exit}')

define open_acceptance_tests
	$(call prepare-data-for-acceptance-tests)
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress
	xhost + $(get_ip);
	-docker compose run --rm -e TYPE=$(1) -e DISPLAY=$(get_ip):0 -e COMMAND=open cypress;
	docker compose stop storefront-cypress
	docker compose up -d storefront
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
endef


.PHONY: open-acceptance-tests-base
open-acceptance-tests-base:
	$(call open_acceptance_tests,base)

.PHONY: open-acceptance-tests-actual
open-acceptance-tests-actual:
	$(call open_acceptance_tests,actual)
