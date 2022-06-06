run: build-docker
	docker-compose up

build-docker:
	docker-compose build --build-arg uid=${DOCKER_UID} --build-arg gid=${DOCKER_GID} app
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app npm install
	docker-compose exec app npm run build
	docker-compose exec app bin/console doctrine:migrations:migrate --no-interaction
	docker-compose down

build:
	cd code && composer install && npm run build
