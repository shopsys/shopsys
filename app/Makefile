run-acceptance-tests:
	docker compose exec php-fpm php phing -D production.confirm.action=y -D change.environment=acc environment-change
	docker compose exec php-fpm php phing test-db-demo test-elasticsearch-index-recreate test-elasticsearch-export
	docker compose up --force-recreate cypress
	docker compose exec php-fpm php phing -D change.environment=dev environment-change
