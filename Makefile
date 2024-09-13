install:
	composer install -n
	./vendor/bin/transformers install -n

serve:
	php -t ./public -S 0.0.0.0:8080