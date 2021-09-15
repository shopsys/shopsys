
This is documentation for Shopsys Framework StoreFront. Let's start with first two steps.

## Install 
1 - Install all dependencies.
```plain
npm ci
```

## Start app

2 - Start the development server.
```plain
npm run dev
```
After this command open http://localhost:3000/ in your browser.

### Optional
Build the app for production.
```plain
npm run build
```

Run the built app in production mode.
```plain
npm start
```

Run eslint for code
```plain
npm run lint
```

Run eslint and fix code
```plain
npm run lint--fix
```

Run prettier format code
```plain
npm run format 
```

Run translation files generator. You can find generated files in /public/locales/ folder.
```plain
npm run translate
```

Run styleguide generator, which will watch your files and compiles changes and displays it with hot-reload. 
After start and first compile you can usually find your online styleguide on http://localhost:6060/

You may restart stuleguide-server when adding new component or new md file.
```plain
npm run styleguide-server
```

One-time compile all stand alone styleguide files as static page. You can find generated files in /docs/styleguide/ folder.
```plain
npm run styleguide-build
```

For full documentation please visit /docs/ folder or after styleguide-build you can visit /docs/styleguide/index.html
