# CDN

## Introduction

CDN (Content Delivery Network) is a system of distributed servers that are used to deliver web content to end-users based on their geographic location.
The main purpose of using a CDN is to improve website performance by reducing latency and improving website load times.

## How to configure CDN in Shopsys Framework

Shopsys Framework supports CDN by simply configuring the environment variable `CDN_DOMAIN`.
The value of this variable is used as a prefix for all static assets such as images, CSS, and JavaScript files.

The variable can be configured in the `.env` file or better in the webserver settings.

!!! note
    You have to ensure the data are properly propagated to the CDN servers.

## Content in the WYSIWYG editor

If the CDN is configured, URL of all images inserted into the WYSIWYG editor will be automatically replaced by the CDN URL on save (thanks to the `WysiwygCdnDataTransformer`).

If you enable the CDN on the already running project, you should ensure, that your existing content is updated accordingly.

## Testing CDN locally

Nginx webserver running in the Docker container is already configured to be able to provide static files on different port.

If you need to test CDN locally, you just need to add new port mapping into your `docker-compose.yaml` file:

```diff
    webserver:
        image: nginx:1.13-alpine
        container_name: shopsys-framework-webserver
        depends_on:
            - php-fpm
        volumes:
            - ./project-base/web:/var/www/html/project-base/web:cached
            - ./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf:delegated
        ports:
            - "8000:8080"
+           - "8001:8081"
```

and set the `CDN_DOMAIN` variable to `http://127.0.0.1:8001` in the `.env[.local]` file:

```dotenv
CDN_DOMAIN=http://127.0.0.1:8001
```

All assets will now be served from the different URL: `http://127.0.0.1:8001`.
