build.container.dev:
	docker run -d --name socialnet-backend-php screxy/socialnet-backend-php:dev
	docker cp socialnet-backend-php:/var/www/vendor/. ./vendor
	docker cp socialnet-backend-php:/var/www/composer.lock ./composer.lock
	docker stop socialnet-backend-php
	docker rm socialnet-backend-php
	docker compose up -d

build-dev: build.container.dev

build-prod:
	docker build -t screxy/socialnet-backend-php:prod .

up:
	docker compose up -d

down:
	docker compose down
