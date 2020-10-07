# Docs

## Create database file

## Config environment

Copy `docs/env.example.php` to root dir with name `.env.php`
Open file and modify settings.

## Handler static files
   
### Option 1: Using symlink

```shell script
mkdir -p public/static
cd public/static
ln -s ../../storage/templates theme
ln -s ../../storage/uploads uploads
```

### Option 2: using nginx

```nginx
location /static/theme {
   alias /var/www/blog/storage/templates;
}

location /static/uploads {
   alias /var/www/blog/storage/uploads;
}
```
