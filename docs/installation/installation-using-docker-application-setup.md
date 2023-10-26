# Installation Using Docker - application setup

This guide expects that you have already set up your Docker environment.
If you have not already done that check the [Installation using Docker](./installation-guide.md#installation-using-docker).

## 1. Set up Shopsys Platform application

Now that the Docker environment is prepared we can setup the application itself.

### 1.1. Connect into terminal of the Docker container

```sh
docker-compose exec php-fpm bash
```

### 1.2. Install dependencies and configure parameters

```sh
composer install
```

!!! note

    During composer installation there will be installed 3-rd party software as dependencies of Shopsys Platform with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

### 1.3. Create databases

```sh
php phing db-create test-db-create
```

!!! hint

    In this step you were using multiple Phing targets.<br>
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

### 1.4. Build the application

```sh
php phing build-demo-dev-quick error-pages-generate
```

!!! note

    During the execution of `build-demo-dev phing target`, there will be installed 3-rd party software as dependencies of Shopsys Platform by [composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies) and [npm](https://docs.npmjs.com/about-the-public-npm-registry) with licenses that are described in document [Open Source License Acknowledgements and Third-Party Copyrights](https://github.com/shopsys/shopsys/blob/master/open-source-license-acknowledgements-and-third-party-copyrights.md)

## 2. See it in your browser!

Open [http://127.0.0.1:8000/](http://127.0.0.1:8000/) to see running application.

You can also log in into the administration section on [http://127.0.0.1:8000/admin/](http://127.0.0.1:8000/admin/) with default credentials:

-   Username: `admin` or `superadmin` (the latter has access to advanced options)
-   Password: `admin123`

You can also manage the application database using [Adminer](https://www.adminer.org) by going to [http://127.0.0.1:1100](http://127.0.0.1:1100)
and Redis storage using [Redis commander](https://github.com/joeferner/redis-commander) by going to [http://127.0.0.1:1600](http://127.0.0.1:1600).

Elasticsearch API is available on the address [http://127.0.0.1:9200](http://127.0.0.1:9200).
You can use [Kibana](https://www.elastic.co/downloads/kibana) for Elasticseacrh management, it is available on [http://127.0.0.1:5601](http://127.0.0.1:5601).

If you need to inspect your application logs, use `docker-compose logs` command.
For more information about logging see [the separate article](../introduction/logging.md).

_And now you can [start building your application](../introduction/start-building-your-application.md)._
