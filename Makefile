
.PHONY: default update-all deploy demowebsite-update docker-update
SHELL=/bin/bash

include $(wildcard deploy.config)

default:
	curl -L "https://github.com/vrana/adminer/releases/download/v4.3.1/adminer-4.3.1-en.php" > adminer.php

update-all:
	git push origin master
	$(MAKE) deploy
	$(MAKE) demowebsite-update
	$(MAKE) docker-update

deploy:
	rsync -va --delete -e ssh . "$(SSH_PATH)"
	scp "config.ini.remote" "$(SSH_PATH)/config.ini"

demowebsite-update:
	DIR="$$(mktemp -d)" && \
	git clone -b demowebsite --single-branch git@github.com:SuRaMoN/weave-gitstats.git "$$DIR" && \
	rm -Rf "$$DIR"/* && \
	docker run -p 8080:80 -e GIT_URL="https://github.com/symfony/yaml.git" "$$(docker build -q .)" ./export php://stdout > "$$DIR/weave.zip" && \
	cd "$$DIR" && \
	rm -Rf *.csv && \
	unzip "weave.zip" && rm "weave.zip" && \
	git add . && git add -u . && \
	git commit --amend -m 'Demo website' && \
	git push -f origin demowebsite && \
	rm -Rf "$$DIR"

docker-update:
	HASH="$$(docker build -q .)" && \
	docker tag "$$HASH" suramon/weave-gitstats
	docker push suramon/weave-gitstats
