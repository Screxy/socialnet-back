build.container.dev:
	docker build -t socialnet-backend-php:dev .
	docker run -d --name socialnet-backend-php socialnet-backend-php:dev
	docker cp socialnet-backend-php:/var/www/vendor/. ./vendor
	docker cp socialnet-backend-php:/var/www/composer.lock ./composer.lock
	docker stop socialnet-backend-php
	docker rm socialnet-backend-php
	docker-compose up -d

build-dev: build.container.dev

up:
	docker-compose up -d

down:
	docker-compose down
