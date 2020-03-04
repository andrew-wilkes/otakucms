<?php

class Templates
{
  public static $nginx = "
# Otakucms server configuration template for NGINX

server {
  server_name #HOST#;
  root #FOLDER#;
  access_log /var/log/nginx/#HOST#.access.log;

  listen 80;
  listen 443 ssl;

  # You need to generate these ssl certificates. Visit: https://letsencrypt.org/
  # ssl_certificate /etc/letsencrypt/live/#HOST#/fullchain.pem;
  # ssl_certificate_key /etc/letsencrypt/live/#HOST#/privkey.pem;
  # ssl_trusted_certificate /etc/letsencrypt/live/#HOST#/chain.pem;

  index index.php index.html;

  location ~ ^/#DATA_FOLDER# {
    deny all;
    return 404;
  }

  location ~ ^/#CLASSES_FOLDER# {
    deny all;
    return 404;
  }

  location = /favicon.ico {
    log_not_found off;
    access_log off;
  }

  location = /robots.txt {
    allow all;
    log_not_found off;
    access_log off;
  }
  
  location / {
    try_files \$uri \$uri/ /index.php?route=\$uri;
  }

  # static file 404's aren't logged and expires header is set to maximum age
  location ~* \.(jpg|jpeg|gif|css|png|js|ico|html)$ {
    access_log off;
    log_not_found off;
    expires max;
  }
  
  location ~ \.php$ {
    try_files  \$uri =404;
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php7.0-fpm.sock;
    fastcgi_intercept_errors on;
  }

  location ~ /\.ht {
    deny all;
  }
}";

  public static $windows = '<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^data" ignoreCase="false" />
                    <action type="Redirect" url="error404" redirectType="Temporary" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^classes" ignoreCase="false" />
                    <action type="Redirect" url="error404" redirectType="Temporary" />
                </rule>
                <rule name="Imported Rule 3" stopProcessing="true">
                    <match url="^([-a-z0-9_./=]+)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php?route={R:1}" appendQueryString="false" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</configuration>';

  public static function htaccess($params)
  {
    return '
ErrorDocument 404 /error404

RewriteEngine On

RewriteBase ' . $params['base'] . '

RewriteRule ^classes error404 [L,R=404]
RewriteRule ^data error404 [L,R=404]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([-a-z0-9_./=]+)$ index.php?route=$1 [L]
';
  }
}
