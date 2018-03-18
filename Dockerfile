
FROM ubuntu:18.04
EXPOSE 80

# Make all installs non interactive
RUN echo 'APT::Get::Assume-Yes "true";\nAPT::Get::force-yes "true";' | tee /etc/apt/apt.conf.d/always-yes
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

RUN \
    apt-get update && \
    apt-get install mysql-server apache2 libapache2-mod-php7.2 git make wget rsync php7.2-mysql php7.2-zip zip

RUN a2enmod headers

RUN mkdir /var/www/weave-gitstats
COPY mysql_disable_strict_mode.cnf /etc/mysql/conf.d/disable_strict_mode.cnf
COPY apache-virtualhost.conf /etc/apache2/sites-available/weave-gitstats.conf
COPY . /var/www/weave-gitstats
RUN a2ensite weave-gitstats

ENTRYPOINT ["/var/www/weave-gitstats/docker-start"]
CMD tail -f /dev/null
