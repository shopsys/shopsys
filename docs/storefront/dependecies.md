# Dependencies

Sometimes, it's not worth reinventing the wheel, right? For those cases, we use third-party libraries. This place is meant for a general overview of not only what we use (this can be easily found in package.json) but also for some notes. For example why we use that specific library.

- [@hookform/resolvers](https://www.npmjs.com/package/@hookform/resolvers) - used for forms validation
- [cookies-next](https://www.npmjs.com/package/cookies-next) - cookie parser
- [embla-carousel-react](https://www.embla-carousel.com/) - lightweight carousel library
- [zustand](https://github.com/pmndrs/zustand) - store management
- [react-toastify](https://fkhadra.github.io/react-toastify/introduction) - solution for displaying toast notifications
- [react-hook-form](https://github.com/react-hook-form/react-hook-form) - solution for forms
- [nprogress](https://www.npmjs.com/package/nprogress) - show loading state between navigation

## Cooldown for newly released versions

New versions of dependencies are withheld for 7 days after their release before pnpm installs them.
The period is set as `minimumReleaseAge` in `pnpm-workspace.yaml` (pnpm 10.16 or newer) and matches the Composer cooldown policy of the backend, see [Guidelines for Dependencies](../contributing/guidelines-for-dependencies.md).
It limits the exposure to a compromised release, as most malicious versions are detected and removed within hours or days after publication.

- `pnpm install --frozen-lockfile` is not affected, the policy applies only when the lockfile is resolved (`pnpm add`, `pnpm update`)
- an exact version that is still in cooldown is refused, run the command with `--config.minimumReleaseAge=0` to install it anyway (e.g., a security fix you have reviewed)
- Dependabot applies the same 7-day cooldown to its version update pull requests (`cooldown` in `.github/dependabot.yml`)
