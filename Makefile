.PHONY: check lint-php compose-config

check: lint-php compose-config

lint-php:
	@find wordpress -type f -name '*.php' -print0 | xargs -0 -n1 php -l

compose-config:
	@docker compose config --quiet
