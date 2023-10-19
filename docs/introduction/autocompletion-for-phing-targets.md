# Autocompletion for Phing Targets

Autocompletion is a functionality through which helps you type commands faster and easier.
It accomplishes that by presenting possible options when you press the tab key while typing a command.
This article describes ways how to use autocompletion for Phing targets.

Phing can be run from several locations and on different machines, we summarize here the few most common use-cases.

## Inside the container

When you connect to the container either with `docker-compose exec ... bash`, `docker exec -it ... bash`, or `kubectl exec -it ... bash`,
you can take advantage of already working autocompletion.

To use, you just type `php phing <tab><tab>` to see all possible completion words.
Alternatively, you can use `./phing <tab><tab>` to achieve the same behavior.

It's also possible to let autocompletion expand the partially typed target name, so `php phing fri<tab>` became `php phing friendly-urls-generate`.

## Running from the host with Bash

As you can always connect to the container, sometimes it's more convenient to run Phing targets from the host machine.
It's possible to get autocompletion working, but you have to set it on your machine.
In this section, you can find the setup guide for Bash.

Create the alias for easier running Phing in the container from your host machine.
Add following into your profile (usually `~/.bash_profile`, `~/.bashrc`, or `~/.profile` file) to make alias available in newly opened terminal.

```bash
alias dphing='docker-compose exec php-fpm ./phing'
```

From now on you can invoke a phing from the container just by running `dphing` from the root of your project folder.

### Linux prerequisites

You need to install `bash-completion` package.
Exact installation depends on your Linux distribution, but usually, it can be done by running

```bash
sudo apt install bash-completion
```

or

```bash
sudo yum install bash-completion
```

You may need to add loading of the bash-completion into your profile.
For more information, please refer to the guide of your Linux distribution.

### MacOS prerequisites

You need to install the necessary package with `brew` command (you need to have [Homebrew](https://brew.sh) installed)

```bash
brew install bash-completion
```

Add following into `~/.bash_profile`

```bash
[ -f /usr/local/etc/bash_completion ] && . /usr/local/etc/bash_completion
```

### Autocomplete script

The last thing is to add the autocomplete script to handle the completion of your previously created `dphing` alias.

Store this file in `/etc/bash_completion.d/dphing` on Linux, or in `/usr/local/etc/bash_completion.d/dphing` on MacOS.

```bash
_dphing () {
    local cur prev
 
    COMPREPLY=()
    buildfile=build.xml
    _get_comp_words_by_ref cur prev
 
    [ ! -f $buildfile ] && return 0

    COMPREPLY=( $( compgen -W "$( dphing -l | tr -s '\-' | sed s/^-/\|/ | tr -d '\|' \
        | sed s/\ \ .*\// \
        | sed s/Buildfile.*// | sed s/Default\ target:// | sed s/Subtargets:// \
        | sed s/Main\ targets:// \
        | tr -s ' ' \
        | sed 's/[^[:print:]]//g' | sed s/\\[.*// | tr '\n' ' ' | tr -s '\n' 2>/dev/null )" \
        -- "$cur" ) )
}
 
complete -F _dphing dphing
```

Restart the terminal for changes to take effect.

From now on, you can just type `dphing <tab><tab>` to see all possible completion words.

It's also possible to let autocompletion expand the partially typed target name, so `dphing fri<tab>` became `dphing friendly-urls-generate`.

!!! note

    Docker containers have to be running in order to `dphing` and autocompletion work properly.
