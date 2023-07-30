bash:
	docker exec -it taskory-app bash

rebuild: rebuild-app rebuild-db rebuild-web up vendors database

rebuild-app:
	docker stop taskory-app && docker remove taskory-app && docker rmi taskory_app && docker-compose up -d

rebuild-db:
	docker stop taskory-db && docker remove taskory-db && docker rmi mysql:8.0.34 && docker-compose up -d

rebuild-web:
	docker stop taskory-web && docker remove taskory-web && docker rmi nginx:alpine && docker-compose up -d

up:
	docker-compose up -d

down:
	docker-compose down

vendors:
	docker exec -t taskory-app composer --working-dir /var/www/html install

database:
	docker exec -t taskory-app php /var/www/html/bin/console doctrine:database:drop --if-exists --force \
	&& docker exec -t taskory-app php /var/www/html/bin/console doctrine:database:create --no-interaction \
	&& docker exec -t taskory-app php /var/www/html/bin/console doctrine:schema:create --no-interaction \
	&& docker exec -t taskory-app php /var/www/html/bin/console doctrine:migrations:version --add --all --no-interaction \
    && docker exec -t taskory-app php /var/www/html/bin/console doctrine:migrations:sync-metadata-storage --no-interaction
	make fixtures

fixtures:
	 docker exec -t taskory-app php /var/www/html/bin/console doctrine:fixtures:load --no-interaction
