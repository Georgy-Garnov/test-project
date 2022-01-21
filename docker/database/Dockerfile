FROM mysql:5.7.34
# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime 
CMD ["mysqld"]
EXPOSE 3306