.PHONY: check lint-php check-style check-json check-secrets test-unit test-integration test-e2e test-fast compose-config build verify-build

check: lint-php check-style check-json check-secrets test-unit test-integration

lint-php:
	@scripts/lint-php.sh

check-style:
	@php scripts/check-style.php

check-json:
	@php scripts/check-json.php

check-secrets:
	@php scripts/check-secrets.php

test-unit:
	@php tests/run.php unit

test-integration:
	@php tests/run.php integration

test-e2e:
	@php tests/e2e/smoke.php

test-fast: test-unit test-integration

compose-config:
	@docker compose config --quiet

build:
	@scripts/build-release.sh

verify-build:
	@scripts/verify-reproducible-build.sh
