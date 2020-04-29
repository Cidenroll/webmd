sudo rm -R /var/www/webmd_old && \
sudo cp -R /var/www/webmd_current /var/www/webmd_old/ && \
sudo rm /var/www/webmd && \
sudo rm -R /var/www/webmd_current && \
# Create symlink to older version && \
sudo ln -s /var/www/webmd_old /var/www/webmd