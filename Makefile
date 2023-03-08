ssh:
	cd .. && cd laradock-multiple-php-version && docker compose exec workspace bash

ssh8:
	cd .. && cd laradock-multiple-php-version && docker-compose exec php-fpm-8.0 bash