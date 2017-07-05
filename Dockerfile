
FROM ubuntu:17.04
EXPOSE 80

# Make all installs non interactive
RUN echo 'APT::Get::Assume-Yes "true";\nAPT::Get::force-yes "true";' | tee /etc/apt/apt.conf.d/always-yes
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

RUN \
    apt-get update && \
    apt-get install mysql-server apache2 libapache2-mod-php7.0 git make wget rsync php7.0-mysql php7.0-zip zip

RUN a2enmod headers

RUN mkdir /var/www/weave-gitstats
COPY mysql_disable_strict_mode.cnf /etc/mysql/conf.d/disable_strict_mode.cnf
COPY apache-virtualhost.conf /etc/apache2/sites-available/weave-gitstats.conf
COPY . /var/www/weave-gitstats
RUN a2ensite weave-gitstats

ENTRYPOINT /usr/bin/make -C /var/www/weave-gitstats docker-start
