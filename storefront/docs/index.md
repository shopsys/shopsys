This is documentation for Shopsys Framework Storefront.

## Ways to use Shopsys Storefront
There are two ways to use Shopsys Storefront on your machine.
First and easiest way is when you have installed your project using Docker.
With Docker, you have everything running already.
If Docker way is too robust for you or you do not need whole application running, you can run Shopsys Storefront natively.

### Docker way
With Docker, you have already Shopsys Storefront and its styleguide already running.
Storefront is running on http://127.0.0.1:3000 and styleguide on http://127.0.0.1:6060.

#### Restart NPM
When you change `next.config.js` file, and you want new settings to be applied, you need to restart NPM.
You might also want to restart NPM when something is not working correctly.
In such cases, you do not need to stop all running containers and start them again, just to recreate container of the storefront.
To do that run this command outside the container:

```plain
docker-compose up -d --force-recreate storefront
```

### Native way

#### Install all dependencies
```plain
npm ci --legacy-peer-deps
```

#### Start app
```plain
npm run dev
```
After this command open http://127.0.0.1:3000/ in your browser.

### Additional commands available for both ways (in Docker way they need to be run inside the storefront container)

#### Build the app for production.
```plain
npm run build
```

#### Run the built app in production mode
```plain
npm start
```

#### Run eslint for code
```plain
npm run lint
```

#### Run eslint and fix code
```plain
npm run lint--fix
```

#### Run prettier format code
```plain
npm run format 
```

