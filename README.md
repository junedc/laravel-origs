# laravel-origs


install composer inside docker

https://stackoverflow.com/questions/51443557/how-to-install-php-composer-inside-a-docker-container


curl -sS https://getcomposer.org/installer | php -- \
--install-dir=/usr/bin --filename=composer && chmod +x /usr/bin/composer 



 cd laravel-origs/
   14  composer require "bugsnag/bugsnag-laravel"
   16  composer require league/flysystem-aws-s3-v3
