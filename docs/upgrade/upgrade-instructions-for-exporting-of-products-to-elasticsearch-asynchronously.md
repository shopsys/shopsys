# Upgrade Instructions for Exporting of Products to Elasticsearch Asynchronously

In ([#1321](https://github.com/shopsys/shopsys/pull/1321)) was calculation of product visibility changed to be calculated asynchronously.
The change introduced many [BC breaks](/docs/contributing/backward-compatibility-promise.md) (many methods were moved or changed their signatures) so you need to follow the upgrading instructions.

## Infrastructure
- update your `docker/php-fpm/Dockerfile` file like this:
    ```diff
        opcache \
        pgsql \
        pdo_pgsql \
    +   sockets \
        zip
    ```
    ```diff
        COPY ${project_root}/docker/php-fpm/docker-php-entrypoint /usr/local/bin/
        RUN chmod +x /usr/local/bin/docker-php-entrypoint

    +   # copy entry-point for consumers
    +   COPY ${project_root}/docker/php-fpm/docker-php-consumer-entrypoint /usr/local/bin/
    +   RUN chmod +x /usr/local/bin/docker-php-consumer-entrypoint
    ```
- create new [`docker/php-fpm/docker-php-consumer-entrypoint`](https://github.com/shopsys/shopsys/blob/9.0/project-base/docker/php-fpm/docker-php-consumer-entrypoint) file with this content:
    ```diff
    +   #!/bin/bash
    +   set -e
    +
    +   tries_before_fail=100
    +   messages_to_consume_count=${CONSUMER_RUNS_BEFORE_RESTART:-1000}
    +
    +   consumer_name=$1
    +
    +   if [[ -z ${consumer_name} ]]; then
    +       1>&2 echo "First argument had to be name of consumer to start"
    +       exit 1
    +   fi
    +
    +   for ((i = 0; i < $[tries_before_fail]; i++)); do
    +       php bin/console shopsys:rabbitmq:check-availability >/dev/null 2>&1 && break
    +       1>&2 echo "Application is not yet ready - consumer sleeping"
    +       sleep 5
    +   done
    +
    +   if [[ ${i} = ${tries_before_fail} ]]; then
    +       1>&2 echo "Number of maximum retries reached - failing"
    +       exit 1
    +   fi
    +
    +   ENVIRONMENT="$(php bin/console about | grep Environment | sed 's/Environment//;s/ //g')"
    +
    +   # consumer in development and test enviroment processes only one message and then is started again so it loads new codebase every time
    +   if [[ ${ENVIRONMENT} = prod ]]; then
    +       php bin/console rabbitmq:consumer -m ${messages_to_consume_count} ${consumer_name}
    +   else
    +       for ((i = 0; i < $[messages_to_consume_count]; i++)); do
    +           php bin/console rabbitmq:consumer -m 1 ${consumer_name}
    +       done
    +   fi
    ```
- update your `docker-compose` files (`docker-compose.yml`, `docker-compose.yml.dist`, `docker-compose-mac.yml.dist` and `docker-compose-win.yml.dist`) using new versions in [`docker/conf`](https://github.com/shopsys/shopsys/tree/9.0/project-base/docker/conf) folder
    - add RabbitMQ service
    ```diff
    +   rabbitmq:
    +       image: rabbitmq:3.7-management-alpine
    +       container_name: shopsys-framework-rabbitmq
    +       ports:
    +           - "15672:15672"
    ```
    - extract common config into `common_php_configuration` and `common_consumer_configuration` (this may by slightly different for MacOS and Windows)
    ```diff
    +   php-fpm:
    -       build:
    -           context: .
    -           dockerfile: docker/php-fpm/Dockerfile
    -           target: development
    -           args:
    -               www_data_uid: 1000
    -               www_data_gid: 1000
    +       <<: *common_php_configuration
            container_name: shopsys-framework-php-fpm
    -       volumes:
    -           -   .:/var/www/html
    +       ports:
    +           - "35729:35729"
    ```
    ```diff
    +   x-variables:
    +       common_php_configuration: &common_php_configuration
    +           build:
    +               context: .
    +               dockerfile: docker/php-fpm/Dockerfile
    +               target: development
    +               args:
    +                   www_data_uid: 1000
    +                   www_data_gid: 1000
    +           volumes:
    +               -   .:/var/www/html
    +
    +       common_consumer_configuration: &common_consumer_configuration
    +           <<: *common_php_configuration
    +           entrypoint: docker-php-consumer-entrypoint
    +           restart: always
    +           depends_on:
    +               - rabbitmq
    +               - php-fpm
    ```
    - add `product-search-export-consumer`
    ```diff
    +   product-search-export-consumer:
    +       <<: *common_consumer_configuration
    +       container_name: shopsys-framework-product-search-export-consumer
    +       command: product_search_export
    ```
- create new [`infrastructure/supervisor.conf.dist`](https://github.com/shopsys/shopsys/blob/9.0/project-base/infrastructure/supervisor.conf.dist) file with this content:
    ```diff
    +   [unix_http_server]
    +   file = /tmp/supervisor.sock
    +
    +   [rpcinterface:supervisor]
    +   supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface
    +
    +   [supervisorctl]
    +   serverurl = unix:///tmp/supervisor.sock
    +
    +   [inet_http_server]
    +   port=*:9001
    +
    +   [supervisord]
    +   nodaemon=false
    +   logfile=/tmp/supervisor/supervisor.log
    +   pidfile=/tmp/supervisor/supervisord.pid
    +
    +   [program:product_search_export]
    +   command=php ./bin/console rabbitmq:consumer product_search_export --messages=1000
    +   process_name=%(program_name)s%(process_num)02d
    +   numprocs=1
    +   startsecs=2
    +   autorestart=true
    +   stdout_logfile=/tmp/product_search_export_stdout.log
    +   stderr_logfile=/tmp/product_search_export_stderr.log
    ```
- update your `.gitignore` file like this:
    ```diff
        /docker-compose.yml
        /docker-sync.yml
    +   /supervisord.conf
    ```
- update your `.ci/deploy-to-google-cloud.sh` file like this:
    ```diff
    -   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
        yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[0].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
        yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[1].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[2].image ${DOCKER_USERNAME}/php-fpm:${DOCKER_IMAGE_TAG}
    ```
    ```diff
    -   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].env[0].value ${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}
    -   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].env[1].value ${PROJECT_ID}
        yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[1].env[0].value ${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}
        yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.initContainers[1].env[1].value ${PROJECT_ID}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].env[0].value ${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[0].env[1].value ${PROJECT_ID}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[2].env[0].value ${GOOGLE_CLOUD_STORAGE_BUCKET_NAME}
    +   yq write --inplace kubernetes/deployments/webserver-php-fpm.yml spec.template.spec.containers[2].env[1].value ${PROJECT_ID}
    ```
- add a consumer container into [`kubernetes/deployments/webserver-php-fpm.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/kubernetes/deployments/webserver-php-fpm.yml)
    ```diff
        -   image: nginx:1.13.10-alpine
            name: webserver
            ports:
                -   containerPort: 8080
                    name: http
            volumeMounts:
                -   name: nginx-configuration
                    mountPath: /etc/nginx/conf.d
                -   name: source-codes
                    mountPath: /var/www/html
            lifecycle:
                preStop:
                        exec:
                            command:
                                - nginx -s quit
    +       -   image: ~
    +           name: product-search-export-consumer
    +           securityContext:
    +               runAsUser: 33
    +           workingDir: /var/www/html
    +           command:
    +               - docker-php-consumer-entrypoint
    +           args:
    +               - product_search_export
    +           volumeMounts:
    +               -   name: source-codes
    +                   mountPath: /var/www/html
    +               -   name: domains-urls
    +                   mountPath: /var/www/html/app/config/domains_urls.yml
    +                   subPath: domains_urls.yml
    +               -   name: parameters
    +                   mountPath: /var/www/html/app/config/parameters.yml
    +                   subPath: parameters.yml
    +           env:
    +               -   name: GOOGLE_CLOUD_STORAGE_BUCKET_NAME
    +                   value: ~
    +               -   name: GOOGLE_CLOUD_PROJECT_ID
    +                   value: ~
    ```
- create new [`kubernetes/deployments/rabbitmq.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/kubernetes/deployments/rabbitmq.yml) file with this content:
    ```diff
    +   apiVersion: apps/v1
    +   kind: Deployment
    +   metadata:
    +       name: rabbitmq
    +   spec:
    +       replicas: 1
    +       selector:
    +           matchLabels:
    +               app: rabbitmq
    +       template:
    +           metadata:
    +               labels:
    +                   app: rabbitmq
    +           spec:
    +               containers:
    +                   -   name: rabbitmq
    +                       image: rabbitmq:3.7-management-alpine
    +                       ports:
    +                           -   name: management
    +                               containerPort: 15672
    +                               protocol: TCP
    +                           -   name: rabbitmq
    +                               containerPort: 5672
    +                               protocol: TCP
    +
    ```
- update your `kubernetes/kustomize/base/kustomization.yaml` file like this:
    ```diff
        resources:
            - ../../deployments/elasticsearch.yml
            - ../../deployments/postgres.yml
    +       - ../../deployments/rabbitmq.yml
            - ../../deployments/redis.yml
            - ../../deployments/smtp-server.yml
            - ../../deployments/webserver-php-fpm.yml
            - ../../services/elasticsearch.yml
            - ../../services/postgres.yml
    +       - ../../services/rabbitmq.yml
            - ../../services/redis.yml
            - ../../services/smtp-server.yml
            - ../../services/webserver-php-fpm.yml
            - ../../ingress.yml
    ```
- update your `kubernetes/kustomize/overlays/ci/ingress-patch.yaml` file like this:
    ```diff
        -   op: add
            path: /spec/rules/-
            value:
                host: ~
                http:
                    paths:
                        -   path: /
                            backend:
                                serviceName: redis-admin
                                servicePort: 80
    +   -   op: add
    +       path: /spec/rules/-
    +       value:
    +           host: ~
    +           http:
    +               paths:
    +                   -   path: /
    +                       backend:
    +                           serviceName: rabbitmq
    +                           servicePort: 15672
    ```
- create new [`kubernetes/services/rabbitmq.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/kubernetes/services/rabbitmq.yml) file with this content:
    ```diff
    +   kind: Service
    +   apiVersion: v1
    +   metadata:
    +       name: rabbitmq
    +   spec:
    +       selector:
    +           app: rabbitmq
    +       ports:
    +       -   name: management
    +           port: 15672
    +           targetPort: 15672
    +       -   name: rabbitmq
    +           port: 5672
    +           targetPort: 5672
    +
    ```
- add RabbitMQ to your production technology stack, more info can be found in [Installation on Production Server](/docs/installation/installation-using-docker-on-production-server.md#rabbitmq-and-supervisor)
- if you have installed Shopsys Framework natively, install [RabbitMQ](https://www.rabbitmq.com/download.html#installation-guides) and [Supervisor](http://supervisord.org/installing.html), [configure the new parameters](https://github.com/shopsys/shopsys/blob/tl-mg-rabbit-visibility/docs/installation/application-configuration.md) when prompted during `composer install`, and [set up the app to run background processes](https://github.com/shopsys/shopsys/blob/tl-mg-rabbit-visibility/docs/installation/native-installation.md#run-background-processing-with-supervisor)

## Application
- update your code in accordance with the following changes:
    - `ProductSearchExportListener` class has been removed
    - `ProductSearchExportScheduler` class has been removed
- create new [`app/config/packages/old_sound_rabbit_mq.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/app/config/packages/old_sound_rabbit_mq.yml) file with this content:
    ```diff
    +   old_sound_rabbit_mq:
    +       connections:
    +           default:
    +               host: '%rabbitmq_host%'
    +               port: '%rabbitmq_port%'
    +               user: '%rabbitmq_user%'
    +               password: '%rabbitmq_pass%'
    +               vhost: '%rabbitmq_vhost%'
    +               lazy: true
    +
    ```
- create new [`app/config/packages/dev/old_sound_rabbit_mq.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/app/config/packages/dev/old_sound_rabbit_mq.yml) file with this content:
    ```diff
    +   old_sound_rabbit_mq:
    +       consumers:
    +           product_search_export:
    +               idle_timeout: 5
    +               idle_timeout_exit_code: 0
    +
    ```
- create new [`app/config/packages/test/old_sound_rabbit_mq.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/app/config/packages/test/old_sound_rabbit_mq.yml) file with this content:
    ```diff
    +   old_sound_rabbit_mq:
    +       connections:
    +           default: ~
    +
    ```
