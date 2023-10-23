# Running Acceptance Tests

## Running in Docker
There is `selenium-server` container with an installed Selenium hub and Firefox, prepared to run the acceptance tests.

You should run all commands mentioned below while logged into your `php-fpm` container via the command:
```
docker exec -it shopsys-framework-php-fpm bash
```

!!! note
    For `selenium-server` to be able to connect to your `webserver` container and access your application, all domains should have the URL set to `http://webserver:8000`.
    This is done via ENV `OVERWRITE_DOMAIN_URL` defined in `.env.acc` or `.env.acc.local`.
    Everything should be configured for you by default, but it is important to keep the domain URL overwriting in mind when dealing with acceptance tests.

If you are logged into your `php-fpm` container and have the `OVERWRITE_DOMAIN_URL` ENV properly set,
you can run acceptance tests:
```sh
php phing tests-acceptance
```

!!! hint
    In this step, you were using Phing target `tests-acceptance`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

### How to watch what is going on in the Selenium browser
Shopsys Platform uses `seleniarm/standalone-firefox` image for `selenium-server` service, which provides two ways of watching acceptance tests progress:

#### VNC
From your local machine, connect to the remote desktop on `vnc://127.0.0.1:5900`

- on Linux, you can use, for example, the *Remmina* tool that is installed by default in Ubuntu
- on Mac, you can run `open vnc://127.0.0.1:5900` in your terminal
- the default password for the connection is `secret`

Run acceptance tests as described in [the paragraph above](#running-in-docker)

#### noVNC
On your local machine, open in browser [http://127.0.0.1:7900/](http://127.0.0.1:7900/) to run noVNC.
You will be prompted for a password, which is `secret` by default.

Run acceptance tests as described in [the paragraph above](#running-in-docker)

## Native installation
To run acceptance tests, you need to install [Google Chrome browser](https://www.google.com/chrome/browser/desktop/) and download [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/).

You must choose compatible versions of Google Chrome and ChromeDriver.
As the Chrome browser has auto-update enabled by default, this may require you to update ChromeDriver occasionally.

When installing Shopsys Platform natively, it is important to update parameters in `.env.acc.local`:

* `OVERWRITE_DOMAIN_URL=` (disables domain URL overwriting in `ACCEPTANCE` environment)
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

### In native installation, run the following commands (each in a separate terminal):
```sh
# run PHP web server
php phing server-run

# run Selenium server
php phing selenium-run
```

### In both docker and native installation run
```sh
# run acceptance test suite
php phing tests-acceptance
```

!!! note
    `pg_dump` is executed internally to enable reverting the test DB to its previous state.
    You may have to add the path of your PostgreSQL installation to the system `PATH` directory for it to work.

!!! note
    If you interrupt running acceptance tests, you may need to delete the root file named `ACCEPTANCE` that is temporarily created to switch the application to the `ACCEPTANCE` environment.

## Running individual tests
Sometimes, you can debug an individual test without running the whole acceptance test suite (which can take several minutes).

### Run single test
```sh
php phing tests-acceptance-single -D test=OrderCest:testOrderCanBeCompleted
```

### Run all tests in one file
```sh
php phing tests-acceptance-single -D test=OrderCest
```

Do not forget to run both the PHP web server and Selenium server in native installation. See [Running the whole acceptance test suite](#running-the-whole-acceptance-test-suite).
