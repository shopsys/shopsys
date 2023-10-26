# Typescript

-   this codebase is written in [TypeScript](https://www.typescriptlang.org/)
-   the checks are set to strict mode in the `tsconfig.json` file.
-   strict flag enables tighter type checking, which on one hand brings stronger guarantees of code correctness, but on the other hand it brings more overhead and requires familiarity with TypeScript development.
-   if you are not comfortable with TypeScript, you can set this option to false
-   Next.js automatically creates a `next-env.d.ts` file in the root directory, which cannot be moved, edited, or deleted as it can break the application
-   you can check the official docs to find out how to use native [React](https://reactjs.org/docs/static-type-checking.html#typescript) or [Next.js](https://nextjs.org/docs/basic-features/typescript) features, such as hooks, SSR, SSG, etc. together with TypeScript
-   for other important packages which are used across this codebase, check the docs below
    an
