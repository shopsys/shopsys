# Monorepo Tools

Tools for building and splitting monolithic repository from existing packages.
You can read about pros and cons of monorepo approach on the [Shopsys Framework Blog](https://blog.shopsys.com/how-to-maintain-multiple-git-repositories-with-ease-61a5e17152e0).

We created these scripts because we couldn't find a tool that would keep the git history of subpackages unchanged.

You may need to update your `git` (tested on `2.16.1`).

Commands will not run on MacOS as it uses slightly different implementation of `sed` and `echo`.

## Quick start

### 1. Download

First download this repository so you can use the tools (eg. into `~/monorepo-tools`).

```
git clone https://github.com/shopsys/monorepo-tools ~/monorepo-tools
```

### 2. Preparing an empty repository with added remotes

You have to create a new git repository for your monorepo and add all your existing packages as remotes.
You can add as many remotes as you want.

In this example we will prepare 3 packages from github for merging into monorepo.

```
git init
git remote add main-repository http://github.com/vendor/main-repository.git
git remote add package-alpha http://github.com/vendor/alpha.git
git remote add package-beta http://github.com/vendor/beta.git
git fetch --all
```

### 3. Building the monorepo

Then you can build your monorepo using `monorepo_build.sh`.
Just list the names of all your previously added remotes as arguments.
Optionally you can specify a directory where the repository will be located by providing `<remote-name>:<subdirectory>`, otherwise remote name will be used.

The command will rewrite history of all mentioned repositories as if they were developed in separate subdirectories.

Only branches `master` will be merged together, other branches will be kept only from first package to avoid possible branch name conflicts.

```
~/monorepo-tools/monorepo_build.sh \
    main-repository package-alpha:packages/alpha package-beta:packages/beta
```

This may take a while, depending on the size of your repositories.

Now your `master` branch should contain all packages in separate directories. For our example it would mean:
* **main-repository/** - contains repository *vendor/main-repository*
* **packages/**
  * **alpha/** - contains repository *vendor/alpha*
  * **beta/** - contains repository *vendor/beta*

### 4. Splitting into original repositories

You should develop all your packages in this repository from now on.

When you made your changes and would like to update the original repositories use `monorepo_split.sh` with the same arguments as before.

```
~/monorepo-tools/monorepo_split.sh \
    main-repository package-alpha:packages/alpha package-beta:packages/beta
```

This will push all relevant changes into all of your remotes.
Only `master` branches will be pushed.

It may again take a while, depending on the size of your monorepo.

## Reference

This is just a short description and usage of all the tools in the package.
For detailed information go to the scripts themselves and read the comments.

### [monorepo_build.sh](./monorepo_build.sh)

Build monorepo from specified remotes. The remotes must be already added to your repository and fetched.

Usage: `monorepo_build.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...`

### [monorepo_split.sh](./monorepo_split.sh)

Split monorepo built by `monorepo_build.sh` and push all master branches into specified remotes.

Usage: `monorepo_split.sh <remote-name>[:<subdirectory>] <remote-name>[:<subdirectory>] ...`

### [rewrite_history_into.sh](./rewrite_history_into.sh)

Rewrite git history so that all filepaths are in a specific subdirectory.

Usage: `rewrite_history_into.sh <subdirectory> [<rev-list-args>]`

### [rewrite_history_from.sh](./rewrite_history_from.sh)

Rewrite git history so that only commits that made changes in a subdirectory are kept and rewrite all filepaths as if it was root.

Usage: `rewrite_history_from.sh <subdirectory> [<rev-list-args>]`

### [original_refs_restore.sh](./original_refs_restore.sh)

Restore original git history after rewrite.

Usage: `original_refs_restore.sh`

### [original_refs_wipe.sh](./original_refs_wipe.sh)

Wipe original git history after rewrite.

Usage: `original_refs_wipe.sh`

### [load_branches_from_remote.sh](./load_branches_from_remote.sh)

Delete all local branches and create all non-remote-tracking branches of a specified remote.

Usage: `load_branches_from_remote.sh <remote-name>`

## Need help
Contact us on our Slack [http://slack.shopsys-framework.com/](http://slack.shopsys-framework.com/).
