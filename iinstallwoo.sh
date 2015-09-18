# ./db.sh -u root -p 12345 -h localhost -db test
# ./installwoo.sh -u root -p 123456 -h localhost -db wootechtheme
#http://stackoverflow.com/questions/2772335/create-database-in-shell-script-convert-from-php
while echo $1 | grep -q ^-; do
    eval $( echo $1 | sed 's/^-//' )=$2
    shift
    shift
done

echo $u;
echo $p
echo $h
echo $db;

#mysql -u $u  -p$p -h $h -Bse "CREATE DATABASE $db;"  ./db.sh -u root -p 123456 -h localhost -db magento1921


mysql -u $u  -p$p -h $h -Bse "CREATE DATABASE $db;";

mysql -u $u -p$p $db < /home/luuthanhthuy/work/setup-website/website/wordpress_setup/woowithdata/woo.sql;

mkdir -p /var/www/html/woomodule/$db;

echo gaube128 | sudo -S cp -r /home/luuthanhthuy/work/setup-website/website/wordpress_setup/woowithdata/wpbook/* /var/www/html/woomodule/$db/ ;

echo gaube128 | sudo -S chmod -R 777  /var/www/html/woomodule/$db ;



