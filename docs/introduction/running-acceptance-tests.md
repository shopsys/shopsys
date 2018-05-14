# Running Acceptance Tests

## Running in Docker
There is `selenium-server` container with installed Selenium hub and Google Chrome, prepared to run the acceptance tests.

You should run all command mentioned below while logged into your `php-fpm` container via command:
```
docker exec -it shopsys-framework-php-fpm sh
```

*Note: For `selenium-server` to be able to connect to you `webserver` container and access your application, all domains should have URL set to `http://webserver:8000`.*
*This is done via parameter `%overwrite_domain_url%` defined in `parameters_test.yml`.*
*Everything should be configured for you by default but it is important to keep the domain URL overwriting in mind when dealing with acceptance tests.*

If you are logged into your `php-fpm` container and have the `%overwrite_domain_url%` parameter properly set, 
you can run acceptance tests:
```
php phing tests-acceptance

```   

## Native installation
For running acceptance tests you need to install [Google Chrome browser](https://www.google.com/chrome/browser/desktop/) and download [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/).

You must choose compatible versions of Google Chrome and ChromeDriver.
As Chrome browser has auto-update enabled by default this may require you to update ChromeDriver from time to time.

When installing Shopsys Framework natively, it is important to update parameters in `parameters_test.yml`:
* `overwrite_domain_url: ~` (disables domain URL overwriting in `TEST` environment) 
* `selenium_server_host: 127.0.0.1`

### Installing Google Chrome browser
Download and install Google Chrome browser from https://www.google.com/chrome/browser/desktop/

### Setting-up ChromeDriver (Selenium WebDriver)
ChromeDriver can be downloaded from: https://sites.google.com/a/chromium.org/chromedriver/downloads

Extract the executable in your system `PATH` directory.
Alternatively, you can extract it anywhere and just point to the executable from your `build/build.local.properties` file.
Example:
```
path.chromedriver.executable=C:\tools\chrome-driver\chromedriver.exe
```

## Running the whole acceptance test suite
After finishing the steps above, running acceptance tests is easy.
Just run the following commands (each in a separate terminal):
```
# run PHP web server
php phing server-run

# run Selenium server
php phing selenium-run

# run acceptance test suite
php phing tests-acceptance
```

*Note: `pg_dump` is executed internally to enable reverting the test DB to its previous state. You may have to add path of your PostgreSQL installation to the system `PATH` directory for it to work.*

*Note: If you interrupt running acceptance tests you may need to delete root file named `TEST` that is temporarily created to switch application to `TEST` environment.*

## Running individual tests
Sometimes you may want to debug individual test without running the whole acceptance test suite (which can take several minutes).

### Prepare database dump and switch to TEST environment
```
# create test database and fill it with demo data
php phing test-db-demo

# create test database dump with current data which will be restored before each test
php phing test-db-dump

# switch application to TEST environment
# on Unix systems (Linux, Mac OSX)
touch TEST
# in Windows CMD
echo.>TEST
```

### Run individual tests
```
vendor/bin/codecept run -c build/codeception.yml acceptance tests/ShopBundle/Acceptance/acceptance/OrderCest.php:testOrderCanBeCompleted
```

Do not forget to run both PHP web server and Selenium server. See [Running the whole acceptance test suite](#running-the-whole-acceptance-test-suite).

*Note: In Windows CMD you have to use backslashes in the path of the executable: `vendor\bin\codecept run ...`*

### Do not forget to restore your original environment afterward
```
# on Unix systems (Linux, Mac OSX)
rm TEST
# in Windows CMD
del TEST
```
