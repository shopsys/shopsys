generate-schema:
	docker-compose exec php-fpm php phing frontend-api-generate-graphql-schema
	docker cp shopsys-framework-php-fpm:/var/www/html/schema.graphql /tmp/schema.graphql
	docker cp /tmp/schema.graphql shopsys-framework-storefront:/home/node/app/schema.graphql
	docker-compose exec -u root storefront chown node:node schema.graphql
	docker-compose exec storefront npm run gql
	docker-compose exec storefront rm -rf /home/node/app/schema.graphql

generate-schema-native:
	cd app; php phing frontend-api-generate-graphql-schema
	cp app/schema.graphql storefront/schema.graphql
	cd storefront; npm run gql
	rm -rf storefront/schema.graphql
