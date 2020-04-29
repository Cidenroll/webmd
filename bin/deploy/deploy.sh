sudo rm /var/www/webmd && \
sudo ln -s /var/www/webmd_current /var/www/webmd && \
cd /var/www/webmd && \
sudo APP_ENV=$APP_ENV DATABASE_URL=$DATABASE_URL php bin/console doctrine:migrations:migrate && \
sudo chown -h www-data:www-data /var/www/webmd && \
sudo service apache2 restart