# Bundle analyzer

In case we need to go deep into what is included in our builded bundle we can use Bundle Analyzer.

To run bundle analyzer go to `/project-base/storefront` and run

```bash
pnpm run analyze
```

After this command is finished you can see in the `/project-base/storefront/.next/analyze/` folder three `html` files `nodejs`, `edge` and `client`. You are probably interested mainly in the `client.html` file which contains expected results for the client bundle. Just open it with a browser and inspect the bundle.
