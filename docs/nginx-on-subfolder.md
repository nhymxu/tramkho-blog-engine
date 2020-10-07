# Nginx config for app run on sub folder

## Simple nginx handle

```nginx
server {
    listen       80;
    server_name  localhost;

    root /var/www;

    # ... other config here

    location ^~ /blog {
        alias /var/www/blog/public;
        try_files $uri $uri/ @tramkho;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            fastcgi_pass   127.0.0.1:9000;
        }

        # Prevent access twig template file
        location ~ \.twig {
            deny all;
            return 404;
        }

    }

    location @tramkho {
        rewrite /blog/((?U).*)(/+)$ /blog/$1 redirect;
        rewrite /blog/(.*)$ /blog/index.php?/$1 last;
    }

    # ... other config here

    location ~ \.php$ {
        try_files                     $fastcgi_script_name =404;
        include fastcgi_params;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_pass                  127.0.0.1:9000;
        fastcgi_index                 index.php;
        fastcgi_buffers               8 16k;
        fastcgi_buffer_size           32k;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    }
}

```
