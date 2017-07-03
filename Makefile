
.PHONY: default deploy

include $(wildcard deploy.config)

default:
	curl -L "https://github.com/vrana/adminer/releases/download/v4.3.1/adminer-4.3.1-en.php" > adminer.php

deploy:
	rsync -va -e ssh . "$(SSH_PATH)"
	scp "config.ini.remote" "$(SSH_PATH)/config.ini"

