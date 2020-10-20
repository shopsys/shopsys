# Running Acceptance Tests

## Running in Docker
There is `selenium-server` container with installed Selenium hub and Google Chrome, prepared to run the acceptance tests.

You should run all command mentioned below while logged into your `php-fpm` container via command:
```
docker exec -it shopsys-framework-php-fpm bash
```

!!! note
    For `selenium-server` to be able to connect to you `webserver` container and access your application, all domains should have URL set to `http://webserver:8000`.
    This is done via ENV `OVERWRITE_DOMAIN_URL` defined in `.env.test` or `.env.test.local`.
    Everything should be configured for you by default but it is important to keep the domain URL overwriting in mind when dealing with acceptance tests.

If you are logged into your `php-fpm` container and have the `OVERWRITE_DOMAIN_URL` ENV properly set,
you can run acceptance tests:
```sh
php phing tests-acceptance
```

!!! hint
    In this step you were using Phing target `tests-acceptance`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

### How to watch what is going on in the selenium browser
By default, Shopsys Framework uses `selenium/standalone-chrome` image for `selenium-server` service which means you are not able to watch what is going on in the selenium browser.
However, there is a quick solution which allows you to watch the progress of your acceptance tests:

In your `docker-compose.yml`, use `standalone-chrome-debug` image for `selenium-server` service and new settings of ports:

```diff
  selenium-server:
-    image: selenium/standalone-chrome:3.141.5
+    image: selenium/standalone-chrome-debug:3.141.5
     container_name: shopsys-framework-acceptance-tests
         ports:
             - "4400:4444"
+            - "5900:5900"
```

Run `docker-compose up -d`

From your local machine, connect to the remote desktop on `vnc://127.0.0.1:5900`

- for the connection, you can use e.g. *Remmina* tool that is installed by default in Ubuntu
- on Mac, you can run `open vnc://127.0.0.1:5900` in your terminal
- the default password for the connection is `secret`

Run acceptance tests as described in [the paragraph above](#running-in-docker)

## Native installation
For running acceptance tests you need to install [Google Chrome browser](https://www.google.com/chrome/browser/desktop/) and download [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/).

You must choose compatible versions of Google Chrome and ChromeDriver.
As Chrome browser has auto-update enabled by default this may require you to update ChromeDriver from time to time.

When installing Shopsys Framework natively, it is important to update parameters in `.env.test.local`:

* `OVERWRITE_DOMAIN_URL=` (disables domain URL overwriting in `TEST` environment)
* `SELENIUM_SERVER_HOST=127.0.0.1`

### Installing Google Chrome browser
Download and install Google Chrome browser from <https://www.google.com/chrome/browser/desktop/>

### Setting-up ChromeDriver (Selenium WebDriver)
ChromeDriver can be downloaded from: <https://sites.google.com/a/chromium.org/chromedriver/downloads>

Extract the executable in your system `PATH` directory.
Alternatively, you can extract it anywhere and just point to the executable from your `build/build.local.properties` file.
Example:
```sh
path.chromedriver.executable=C:\tools\chrome-driver\chromedriver.exe
```

## Running the whole acceptance test suite
After finishing the steps above, running acceptance tests is easy.
Just run the following commands (each in a separate terminal):
```sh
# run PHP web server
php phing server-run

# run Selenium server
php phing selenium-run

# run acceptance test suite
php phing tests-acceptance
```

!!! note
    `pg_dump` is executed internally to enable reverting the test DB to its previous state.
    You may have to add path of your PostgreSQL installation to the system `PATH` directory for it to work.

!!! note
    If you interrupt running acceptance tests you may need to delete root file named `TEST` that is temporarily created to switch application to `TEST` environment.

## Running individual tests
Sometimes you may want to debug individual test without running the whole acceptance test suite (which can take several minutes).

### Prepare database dump and switch to TEST environment
```sh
# create test database and fill it with demo data and export products to elasticsearch test index
php phing test-db-demo
php phing test-elasticsearch-export

# create test database dump with current data which will be restored before each test
php phing test-db-dump

# switch application to TEST environment
# on Unix systems (Linux, Mac OSX)
touch TEST
# in Windows CMD
echo.>TEST
```

### Run individual tests
```sh
vendor/bin/codecept run -c build/codeception.yml acceptance tests/App/Acceptance/acceptance/OrderCest.php:testOrderCanBeCompleted
```

Do not forget to run both PHP web server and Selenium server. See [Running the whole acceptance test suite](#running-the-whole-acceptance-test-suite).

!!! note
    In Windows CMD you have to use backslashes in the path of the executable: `vendor\bin\codecept run ...`

### Do not forget to restore your original environment afterward
```sh
# on Unix systems (Linux, Mac OSX)
rm TEST
# in Windows CMD
del TEST
```
