FROM centos:7
RUN yum -y update
RUN yum -y install httpd 
RUN yum -y install httpd-utils php libapache2-mod-php php-mysql
RUN yum clean all
EXPOSE 80

ADD html/jobchart.php /var/www/html
ADD html/ovistime.php /var/www/html
#
CMD ["apachectl","-D","FOREGROUND"]
