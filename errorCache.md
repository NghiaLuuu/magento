docker exec -it -u www-data magento_php bin/magento cache:disable layout block_html full_page
docker restart magento_php


docker exec -it -u www-data magento_php bin/magento setup:static-content:deploy -f
sudo chmod -R 777 magento/pub/static magento/var magento/generated
docker exec -it -u www-data magento_php bin/magento cache:clean

docker exec -it magento_php bin/magento cache:flush (khi thay doi xml)

docker exec -it magento_php sh -c "rm -rf generated/code/* generated/metadata/*"

docker restart magento_php 

docker exec -it magento_php chmod -R 777 var/ generated/ pub/static/ (209)