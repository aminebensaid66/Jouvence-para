.PHONY: check lint-php check-style check-json compose-config

check: lint-php check-style check-json

lint-php:
	@scripts/lint-php.sh

check-style:
	@php scripts/check-style.php

check-json:
	@php scripts/check-json.php

compose-config:
	@docker compose config --quiet
