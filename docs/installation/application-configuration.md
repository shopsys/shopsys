# Application Configuration

The application is configurable by [Symfony configuration files](https://symfony.com/doc/4.4/configuration.html#configuration-parameters) or via [environment variables](https://symfony.com/doc/4.4/configuration.html#configuration-environments) which allows you to overwrite them.

## Configuration parameters

For operating Shopsys Platform it is needed to have correctly set connections to external services via ENV variables.

!!! note

    All default values use default ports for all external services like PostgreSQL database, elasticsearch, redis, ...

!!! tip

    Host values can be modified or can be aliased for your Operating System via `/etc/hosts` or `C:\Windows\System32\drivers\etc\hosts`

Environment variables are really handy to configure the right setting in the desired application environment.
You may want to set some settings in a different way (such as production, test, or CI servers).
[Setting environment variables](../introduction/setting-environment-variables.md) depends on environment of your application.

!!! tip

    To improve performance you can optionally run `composer dump-env`. [See Symfony documentation for further information.](https://symfony.com/doc/4.4/configuration.html#configuring-environment-variables-in-production)

### Application

`DATABASE_HOST` (default: `'postgres'`)
: access data of your PostgreSQL database

`DATABASE_PORT` (default: `null`)
: ...

`DATABASE_NAME` (default: `'shopsys'`)
: ...

`DATABASE_USER` (default: `'root'`)
: ...

`DATABASE_PASSWORD` (default: `'root'`)
: ...

`ELASTICSEARCH_HOST` (default: `'elasticsearch:9200'`)
: host of your Elasticsearch, you can use multiple hosts like `'["elasticsearch:9200", "elasticsearch2:9200"]'`

`REDIS_HOST` (default: `'redis'`)
: host of your Redis storage (credentials are not supported right now)

`REDIS_PREFIX` (default: `''`)
: separates more projects that use the same redis service

`MAILER_DSN` (default: `smtp://smtp-server:25?verify_peer=false`, overridden to `smtp://smtp-server:1025?verify_peer=false` for the `dev` environment via `.env.dev` to match the local MailDev container)
: set to `null://null` if you don't want to send any emails, see [Symfony documentation](https://symfony.com/doc/current/mailer.html#disabling-delivery)

`APP_SECRET` (default: `'ThisTokenIsNotSoSecretChangeIt'`)
: randomly generated secret token

`ELASTIC_SEARCH_INDEX_PREFIX` (default: `''`)
: separates more projects that use the same elasticsearch service

`IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK` (default: `'0'`)
: set to `true` if you want to allow administrators to log in with default credentials

`SHOPSYS_CONTENT_DIR_NAME` (default: `'content-test'`)
: web/content-test/ directory is used instead of web/content/ during the tests

`TRUSTED_PROXIES` (default: `'127.0.0.1'`)
: proxies that are trusted to pass traffic, used mainly for production (set as text separated by comma for multiple values)

`CDN_DOMAIN` (default: `''`)
: specifies URL of a Content Delivery Network (CDN) that is used to serve static assets such as images, CSS, and JavaScript files

### Storefront

Environment variable `SHOW_SYMFONY_TOOLBAR` allows for showing/hiding of the Symfony toolbar on Storefront, which can be used to view API requests from the front-end.

### Google Cloud Bundle

These variables are specific for [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)

`GOOGLE_CLOUD_PROJECT_ID` (default: `''`)
: defines Google Cloud Project ID

`GOOGLE_CLOUD_STORAGE_BUCKET_NAME` (default: `''`)
: defines Bucket Name in Google Cloud Storage

### S3 Bridge Bundle

These variables are exclusively usable with the [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge) and are required only when using this package.

`S3_ENDPOINT` (default: `''`)
: URL of the S3 service endpoint

`S3_REGION` (default: `''`)
: AWS region where the S3 bucket is located (usually empty for custom S3 services

`S3_ACCESS_KEY` (default: `''`)
: access key ID for the S3 service

`S3_SECRET` (default: `''`)
: secret access key for the S3 service

`S3_BUCKET_NAME` (default: `''`)
: name of the S3 bucket to access or manipulate

`S3_VERSION` (default: `'2006-03-01'`)
: version of the webservice to utilize
