EXTENSION := PageForms

MW_VERSION ?= 1.35
SMW_VERSION ?= 4.0.1

EXTENSION_FOLDER := /var/www/html/extensions/$(EXTENSION)
IMAGE_NAME := $(shell echo $(EXTENSION) | tr A-Z a-z}):test-$(MW_VERSION)-$(SMW_VERSION)
PWD := $(shell bash -c "pwd -W 2>/dev/null || pwd")# this way it works on Windows and Linux
DOCKER_RUN_ARGS := --rm -v $(PWD)/coverage:$(EXTENSION_FOLDER)/coverage -w $(EXTENSION_FOLDER) $(IMAGE_NAME)
DOCKER_RUN := docker run $(DOCKER_RUN_ARGS)

.PHONY: all
all:

# ======== CI ========

.PHONY: ci
ci: build test

.PHONY: ci-coverage
ci-coverage: build test-coverage

.PHONY: build
build:
	docker build --tag $(IMAGE_NAME) \
		--build-arg=MW_VERSION=$(MW_VERSION) \
		--build-arg=SMW_VERSION=$(SMW_VERSION) \
		.

.PHONY: test
test: composer-test npm-test

.PHONY: test-coverage
test-coverage: composer-test-coverage npm-test-coverage

.PHONY: composer-test
composer-test:
	$(DOCKER_RUN) composer test

.PHONY: composer-test-coverage
composer-test-coverage:
	$(DOCKER_RUN) composer test-coverage

.PHONY: npm-test
npm-test:
	$(DOCKER_RUN) npm run test

.PHONY: npm-test-coverage
npm-test-coverage:
	$(DOCKER_RUN) npm run test-coverage

.PHONY: bash
bash:
	docker run -it -v $(PWD):/src $(DOCKER_RUN_ARGS) bash

# ======== Releasing ========

VERSION = `node -e 'console.log(require("./extension.json").version)'`

.PHONY: release
release: ci git-push gh-login
	gh release create $(VERSION)

.PHONY: git-push
git-push:
	git diff --quiet || (echo 'git directory has changes'; exit 1)
	git push

.PHONY: gh-login
gh-login: require-GH_API_TOKEN
	gh config set prompt disabled
	@echo $(GH_API_TOKEN) | gh auth login --with-token

.PHONY: require-GH_API_TOKEN
require-GH_API_TOKEN:
ifndef GH_API_TOKEN
	$(error GH_API_TOKEN is not set)
endif
