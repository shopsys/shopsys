# Blackfire profiling

[TOC]

## Introduction

Blackfire is a powerful tool for profiling and debugging that helps you to understand the performance of your application.
In the Shopsys Platform, Blackfire is preinstalled for local development, allowing developers to easily profile and optimize their code.

## Prerequisites

Before you can start using Blackfire in the Shopsys Platform, you need to have the following:

-   Blackfire account
    -   You can create an account on the [Blackfire website](https://blackfire.io/).
-   [Blackfire browser extension](https://docs.blackfire.io/integrations/browsers/index) installed (optional)

## Configuration

Edit the `docker-compose.yml` file in your Shopsys Platform project to include the Blackfire credentials.
Uncomment the blackfire container definition and fill in variables with your actual Blackfire credentials.

```yaml
version: '3.7'

x-variables:
    blackfire_environments: &blackfire_environments
        BLACKFIRE_SERVER_ID: your_server_id
        BLACKFIRE_SERVER_TOKEN: your_server_token
        BLACKFIRE_CLIENT_ID: your_client_id
        BLACKFIRE_CLIENT_TOKEN: your_client_token

services:
    # Uncomment the following lines and fill values in x-variables section to enable Blackfire
    blackfire:
        image: blackfire/blackfire:2
        ports: ['8307']
        environment:
            <<: *blackfire_environments
# ...
```

Replace `your_client_id`, `your_client_token`, `your_server_id`, and `your_server_token` with your actual Blackfire credentials.

After updating the `docker-compose.yml` file, restart your Docker containers to apply the changes.

```bash
docker compose up -d --force-recreate
```

## Profiling CLI Scripts

You can profile your CLI scripts using the Blackfire CLI tool preinstalled in the `php-fpm` container.
For example, to profile a CLI command, use the following command:

```bash
docker compose exec -it php-fpm blackfire run php bin/console your:command
```

This will run the specified CLI command and profile its execution.
You can view the profiling results in your Blackfire account.

## Profiling HTTP Requests

To profile HTTP requests, you need to install the [Blackfire browser extension](https://docs.blackfire.io/integrations/browsers/index).

You can now click on the Blackfire icon in your browser and select "Profile!"
The profiling results will be sent to your Blackfire account, where you can analyze the performance of your HTTP requests.

To profile the graphql queries on the storefront, you may instead click on the Blackfire icon in your browser and select "Profile all requests".
All requests made, while profiling is active, will be profiled and the results will be sent to your Blackfire account as individual profiles.

## Conclusion

Blackfire integration in the Shopsys Platform allows developers to easily profile and optimize their code.
By configuring the `docker-compose.yml` file with your Blackfire credentials, you can start profiling CLI scripts and HTTP requests to improve the performance of your application.
For more detailed analysis, refer to the profiling results in your Blackfire account and make necessary optimizations.
