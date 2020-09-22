# Application Configuration

The application is configurable by [symfony configuration files](https://symfony.com/doc/4.4/configuration.html#configuration-parameters) or via environment variables which allows you to overwrite them.

- [Configuration by parameters](#configuration-by-parameters)
- [Configuration by environment variables](#configuration-by-environment-variables)

## Configuration by parameters

For operating Shopsys Framework it is needed to have correctly set connections to external services via `parameters.yaml` config.
From the clean project, during composer installation process it will prompt you to set the values of parameters (`config/parameters.yaml`):

| Name                                     | Description                                                                                                  |
| ---------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `database_host`                          | access data of your PostgreSQL database                                                                      |
| `database_port`                          | ...                                                                                                          |
| `database_name`                          | ...                                                                                                          |
| `database_user`                          | ...                                                                                                          |
| `database_password`                      | ...                                                                                                          |
| `elasticsearch_host`                     | host of your Elasticsearch                                                                                   |
| `redis_host`                             | host of your Redis storage (credentials are not supported right now)                                         |
| `mailer_transport`                       | access data of your mail server                                                                              |
| `mailer_host`                            | ...                                                                                                          |
| `mailer_user`                            | ...                                                                                                          |
| `mailer_password`                        | ...                                                                                                          |
| `mailer_disable_delivery`                | set to `true` if you don't want to send any emails                                                          |
| `mailer_master_email_address`            | set if you want to send all emails to one address (useful for development)                                  |
| `mailer_delivery_whitelist`              | set as array with regex text items if you want to have master email but allow sending to specific addresses |
| `secret`                                 | randomly generated secret token                                                                              |
| `trusted_proxies`                        | proxies that are trusted to pass traffic, used mainly for production                                         |

Composer will then prompt you to set parameters for testing environment (`config/parameters_test.yaml`):

| Name                               | Description                                                                   |
| ---------------------------------- | ----------------------------------------------------------------------------- |
| `test_database_host`               | access data of your PostgreSQL database for tests                             |
| `test_database_port`               | ...                                                                           |
| `test_database_name`               | ...                                                                           |
| `test_database_user`               | ...                                                                           |
| `test_database_password`           | ...                                                                           |
| `overwrite_domain_url`             | overwrites URL of all domains for acceptance testing (set to `~` to disable)  |
| `selenium_server_host`             | with native installation the selenium server is on `localhost`                |
| `test_mailer_transport`            | access data of your mail server for tests                                     |
| `test_mailer_host`                 | ...                                                                           |
| `test_mailer_user`                 | ...                                                                           |
| `test_mailer_password`             | ...                                                                           |
| `shopsys.content_dir_name`         | web/content-test/ directory is used instead of web/content/ during the tests  |


!!! note
    All default values use default ports for all external services like PostgreSQL database, elasticsearch, redis, ...

!!! tip
    Host values can be modified or can be aliased for your Operating System via `/etc/hosts` or `C:\Windows\System32\drivers\etc\hosts`

## Configuration by environment variables

Environment variables are really handy to configure the right setting in the desired application environment.
You may want to set some settings in a different way (such as production, test, or CI servers).
[Setting environment variables](/introduction/setting-environment-variables) depends on environment of your application.

### Application

| Name                                   | Default | Description                                                                          |
| -------------------------------------- | ------- | ------------------------------------------------------------------------------------ |
| `REDIS_PREFIX`                         | `''`    | separates more projects that use the same redis service                              |
| `ELASTIC_SEARCH_INDEX_PREFIX`          | `''`    | separates more projects that use the same elasticsearch service                      |
| `IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK`  | `'0'`   | set to `true` if you want to allow administrators to log in with default credentials |

### Google Cloud Bundle

These variables are specific for [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)

| Name                                    | Default | Description                                              |
| --------------------------------------- | ------- | -------------------------------------------------------- |
| `GOOGLE_CLOUD_PROJECT_ID`               | `''`    | defines Google Cloud Project ID                          |
| `GOOGLE_CLOUD_STORAGE_BUCKET_NAME`      | `''`    | defines Bucket Name in Google CLoud Storage              |
