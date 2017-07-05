
.PHONY: default deploy docker-start
SHELL=/bin/bash

include $(wildcard deploy.config)

default:
	curl -L "https://github.com/vrana/adminer/releases/download/v4.3.1/adminer-4.3.1-en.php" > adminer.php

deploy:
	rsync -va -e ssh . "$(SSH_PATH)"
	scp "config.ini.remote" "$(SSH_PATH)/config.ini"

docker-update:
	HASH="$$(docker build -q .)" && \
	docker tag "$$HASH" suramon/weave-gitstats
	docker push suramon/weave-gitstats

docker-start:
	service mysql restart
	service apache2 restart
	echo -e "git_url = \"$$GIT_URL\"\nmysql_host = 127.0.0.1\nmysql_user = root\nmysql_pass = \"\"\nmysql_db = gitstats" > config.ini
	./load-git-stats
	@echo -e "\nYou can view your statistics on: http://localhost:8080\nLeave this window open"
	@tail -f /dev/null
