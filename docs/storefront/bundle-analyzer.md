# Bundle analyzer

If we need to go deep into what is included in our builded bundle, we can use Bundle Analyzer.

To run Bundle Analyzer, go to `/project-base/storefront` and run

```bash
pnpm run analyze
```

After this command is finished, you can see three `html` files (`nodejs`, `edge` and `client`) in the `/project-base/storefront/.next/analyze/` folder. You are probably interested mainly in the `client.html` file, which contains expected results for the client bundle. Just open it with a browser and inspect the bundle.
