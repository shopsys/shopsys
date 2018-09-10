# Installation Using Docker - application setup

This guide expects that you have already set up your Docker environment.
If you haven't already done that check the [Installation Using Docker](installation-using-docker.md).

## 1. Setup the Shopsys Framework application
Now that the Docker environment is prepared we can setup the application itself.

### 1.1. Connect into terminal of the Docker container
```
docker exec -it shopsys-framework-php-fpm sh
```

### 1.2. Install dependencies and configure parameters

Composer requires token to avoid GitHub API rate limit. 
You can create this token on `https://github.com/settings/tokens/new`.
Go to your `docker-compose.yml` file, find `php-fpm` container and save this in `enviroment` variable `COMPOSER_AUTH` (replace text "place-your-token-here" with your token).

```
composer install
```

If you are installing the application in production environment, you should install composer optimized.
The optimized composer speed up your application.
```
composer install -o
```

Composer will prompt you to set parameters ([description of parameters](native-installation.md#2-install-dependencies-and-configure-parameters)).
The default parameters suggested by composer are currently set for application running in Docker so you can just use these.

Only exception is the `secret` parameter - you should input a random string to be used for security purposes.
It is not necessary for development though.

For development choose `n` when asked `Build in production environment? (Y/n)`.

It will set the environment in your application to `dev` (this will, for example, show Symfony Web Debug Toolbar).

### 1.3. Configure domains
Create `domains_urls.yml` from `domains_urls.yml.dist`.

```
cp app/config/domains_urls.yml.dist app/config/domains_urls.yml
```

### 1.4. Create databases
```
php phing db-create
php phing test-db-create
```

### 1.5. Build the application
```
php phing build-demo-dev
php phing img-demo
```

## 2. See it in your browser!

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) to see running application.

You can also login into the administration section on [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/) with default credentials:
* Username: `admin` or `superadmin` (the latter has access to advanced options)
* Password: `admin123`

You can also manage the application database using [Adminer](https://www.adminer.org) by going to [http://127.0.0.1:1100](http://127.0.0.1:1100)
and Redis storage using [Redis admin](https://github.com/ErikDubbelboer/phpRedisAdmin) by going to [http://127.0.0.1:1600](http://127.0.0.1:1600).

Elasticsearch API is available on the address [http://127.0.0.1:9200](http://127.0.0.1:9200).
You can use e.g. [Postman](https://www.getpostman.com/apps) or [Kibana](https://www.elastic.co/downloads/kibana) for Elasticseacrh management.
