generate-schema:
	docker compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker compose exec -u root storefront chown node:node schema.graphql
	docker compose exec storefront npm run gql
	docker compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	cd app; php phing frontend-api-generate-graphql-schema
	cp app/schema.graphql storefront/schema.graphql
	cd storefront; npm run gql
	rm -rf storefront/schema.graphql

mutagen-up:
	./scripts/mutagen-up.sh

mutagen-up-build:
	./scripts/mutagen-up.sh --build

mutagen-up-build-no-cache:
	./scripts/mutagen-up.sh --build --no-cache

mutagen-stop:
	./scripts/mutagen-stop.sh

mutagen-down:
	./scripts/mutagen-down.sh

define run_acceptance_tests
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=test environment-change
	docker compose exec php-fpm php phing test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
	docker compose stop storefront
	docker compose up -d --wait storefront-cypress
	-docker compose run --rm -e TYPE=$(1) cypress;
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

generate-tailwind-for-admin:
	@echo "🚀 Compiling Tailwind CSS for admin..."
	rm -rf storefront/public/tailwind-for-admin/style.css
	mkdir -p storefront/public/tailwind-for-admin
	docker compose exec storefront pnpm compile-tailwind-for-admin
	@echo "✅ Tailwind CSS compiled to: storefront/public/tailwind-for-admin/style.css"
	@echo "🔧 Rebuilding backend admin assets..."
	docker compose exec php-fpm php phing npm-dev
	@echo "🎉 Admin assets rebuilt! Tailwind classes are now available in GrapesJS."