rebuild: rebuild-app rebuild-db rebuild-web up

rebuild-app:
	docker stop taskory-app && docker remove taskory-app && docker rmi taskory_app

rebuild-db:
	docker stop taskory-db && docker remove taskory-db && docker rmi mysql:8.0.34

rebuild-web:
	docker stop taskory-web && docker remove taskory-web && docker rmi nginx:alpine

up:
	docker-compose up -d

down:
	docker-compose down
