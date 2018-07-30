# Installation Using Docker - application setup

This guide expects that you have already set up your Docker environment.
If you haven't already done that check the [Installation Using Docker](installation-using-docker.md).

## 1. Setup the microservice for product search
Installing of Shopsys Framework from a project-base requires manual cloning of the [repository of the microservice](https://github.com/shopsys/microservice-product-search).
Clone the repository inside a separate directory and configure the container in your `docker-compose.yml`:
```yaml
    microservice-product-search:
        build:
            context: ../microservice-product-search/docker
            args:
                www_data_uid: 1000
                www_data_gid: 1000
        container_name: shopsys-framework-microservice-product-search
        volumes:
            - ../microservice-product-search:/var/www/html
            - ../microservice-product-search/docker/php-ini-overrides.ini:/usr/local/etc/php/php.ini
        links:
            - postgres
        depends_on:
            - postgres
```
*Note: If you have edited the docker-compose.yml, you should re-run the docker-compose command --force-recreate -d
*Note: There can be differences in the configuration on different OS. It should be similar to the `php-fpm`'s configuration.*

### 1.1. Connect into terminal of the Docker container
```
docker exec -it shopsys-framework-microservice-product-search sh
```
### 1.2. Install dependencies
```
composer install
```
### 1.3. Run the server
Since the microservice acts as a fully independent unit, it needs a separate web server.
In the current version of this experimental microservice, the PHP's built-in Web Server is used.

You can start the server by executing the command:
```
php bin/console server:run *:8000
```

In this moment the microservice is ready for the requests processing.

*Note: You can switch the running process to run in the background by pressing `CTRL+Z` and executing `bg`.*

## 2. Setup the Shopsys Framework application
Now that the Docker environment is prepared and the product search microservice is up and running, we can setup the application itself.

### 2.1. Connect into terminal of the Docker container
```
docker exec -it shopsys-framework-php-fpm sh
```

### 2.2. Install dependencies and configure parameters
```
composer install
```

If you are installing the application in production environment, you should install composer optimized.
The optimized composer speed up your application.
```
composer install -o
```

Composer will prompt you to insert token to avoid GitHub API rate limit. You can create this token on `https://github.com/settings/tokens/new`.
This token is reusable so keep it for further usage.

Composer will prompt you to set parameters ([description of parameters](native-installation.md#2-install-dependencies-and-configure-parameters)).
The default parameters suggested by composer are currently set for application running in Docker so you can just use these.

Only exception is the `secret` parameter - you should input a random string to be used for security purposes.
It is not necessary for development though.

For development choose `n` when asked `Build in production environment? (Y/n)`.

It will set the environment in your application to `dev` (this will, for example, show Symfony Web Debug Toolbar).

### 2.3. Configure domains
Create `domains_urls.yml` from `domains_urls.yml.dist`.

```
cp app/config/domains_urls.yml.dist app/config/domains_urls.yml
```

### 2.4. Create databases
```
php phing db-create
php phing test-db-create
```

### 2.5. Build the application
```
php phing build-demo-dev
php phing img-demo
```

## 3. See it in your browser!

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) to see running application.

You can also login into the administration section on [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/) with default credentials:
* Username: `admin` or `superadmin` (the latter has access to advanced options)
* Password: `admin123`

You can also manage the application database using [Adminer](https://www.adminer.org) by going to [http://127.0.0.1:1100](http://127.0.0.1:1100)
and Redis storage using [Redis admin](https://github.com/ErikDubbelboer/phpRedisAdmin) by going to [http://127.0.0.1:1600](http://127.0.0.1:1600).

Elasticsearch API is available on the address [http://127.0.0.1:9200](http://127.0.0.1:9200).
You can use e.g. [Postman](https://www.getpostman.com/apps) or [Kibana](https://www.elastic.co/downloads/kibana) for Elasticseacrh management.
