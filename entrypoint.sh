#!/bin/bash

# اجبار Apache إنه يسمع على PORT اللي Railway بيبعته
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

# تعديل الـ VirtualHost ليشتغل على الـ PORT
cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:${PORT}>
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# ربط لوجز Laravel بـ stdout علشان Railway يشوفها
ln -sf /dev/stdout /var/www/html/storage/logs/laravel.log

# تشغيل Apache
apache2-foreground
