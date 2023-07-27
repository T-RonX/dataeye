rebuild:
	docker stop taskory-php && docker remove taskory-php && docker rmi taskory_app \
	&& docker-compose up -d
