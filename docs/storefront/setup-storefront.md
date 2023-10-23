# Setup Storefront

There are two ways to use Shopsys Platform Storefront on your machine.
The first and easiest way is when you have installed your project using Docker.
With Docker, you have everything running already.
If Docker way is too robust for you or you do not need the whole application running, you can run Shopsys Platform Storefront natively.

## Docker way

With Docker, you have Shopsys Platform Storefront already running.
Storefront is running on <http://127.0.0.1:3000>

### Restart Next

When you change the `next.config.js` file, and you want the new settings to be applied, you need to restart the Next application.
You can also restart it when something is not working correctly.
In such cases, you do not need to stop all running containers and start them again just to recreate the container of the Storefront.
To do that, run this command outside the container:

```bash
docker-compose up -d --force-recreate storefront
```

## Native way

### Install all dependencies

```bash
pnpm install
```

### Start app

```bash
pnpm run dev
```

After running this command, open <http://127.0.0.1:3000/> in your browser.

## Additional commands available for both ways

In Docker way they need to be run inside the Storefront container.

### Build the app for production

```bash
pnpm run build
```

### Run the built app in production mode

```bash
pnpm start
```

### Run eslint for code

```bash
pnpm run lint
```

### Run eslint and fix code

```bash
pnpm run lint--fix
```

### Run prettier format code

```bash
pnpm run format
```

### Run TypeScript compiler (TSC typecheck), prettier, and eslint to check the code

```bash
pnpm run check
```

### Run TypeScript compiler (TSC typecheck), prettier, and eslint to check and fix the code

```bash
pnpm run check--fix
```
