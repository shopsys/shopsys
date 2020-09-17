# Setting environment variables

In this article describes how to set environments variable in different environment.

The most common uses are: Kubernetes, Docker or native installation.

!!! note
    
    Bellow is descibed the easiest way. For better understanding we recomend to find out the documentation of the specific environment.

!!! tip

    You may want to tune your configuration via [`.env` file](https://symfony.com/doc/4.4/configuration.html#configuring-environment-variables-in-env-files).

## Kubernetes

When you are using kubernetes on CI server change your configuration of:

- `kubernetes/kustomize/overlays/ci/kustomization.yaml`

```diff
        path: ./ingress-patch.yaml
+   -   target:
+           group: apps
+           version: v1
+           kind: Deployment
+           name: webserver-php-fpm
+       path: ./webserver-php-fpm-patch.yaml
configMapGenerator:
    -   name: nginx-configuration
```

- create `kubernetes/kustomize/overlays/ci/webserver-php-fpm-patch.yaml` containing

```diff
+-   op: add
+    path: /spec/template/spec/containers/0/env/-
+    value:
+        name: REDIS_PREFIX
+        value: 'my_awesome_app'
```

## Docker

When using docker containers without kubernetes add the environment variable to the `docker-compose.yml` file to `php-fpm` definition like in example below

```diff
    php-fpm:
        build:
            context: .
            dockerfile: docker/php-fpm/Dockerfile
            target: development
            args:
                www_data_uid: 1000
                www_data_gid: 1000
        container_name: shopsys-framework-php-fpm
        volumes:
            - shopsys-framework-sync:/var/www/html
            - shopsys-framework-vendor-sync:/var/www/html/vendor
            - shopsys-framework-web-sync:/var/www/html/web
        ports:
            - "35729:35729"
+       environment:
+           - REDIS_PREFIX='my_awesome_app'
```


## Native instalation

Without containers you must set environment variable on the host machine, typically in unix like OS by executing

```
export REDIS_PREFIX='my_awesome_app'
```
