#!/bin/sh

set -e

config_path=~/.docker
mkdir $config_path
config_filename=$config_path/config.json

# Could assume config.json isn't there or overwrite regardless and not use jq (or sed etc.)
echo '{ "credsStore": "pass" }' > $config_filename

# init key for pass
gpg --batch --gen-key <<-EOF
%no-protection
%echo Generating a standard key
Key-Type: DSA
Key-Length: 1024
Subkey-Type: ELG-E
Subkey-Length: 1024
Name-Real: Shopsys
Name-Email: no-reply@shopsys.com
Expire-Date: 0
# Do a commit here, so that we can later print "done" :-)
%commit
%echo done
EOF

# extract key from the list
# from following output of gpg --no-auto-check-trustdb --list-secret-keys
#  ------------------------
#  sec   dsa1024 2021-01-06 [SCA]
#        36DD093xxxxxxxxxxxxxF1F5F8C5751B
#  uid           [ultimate] Shopsys <no-reply@shopsys.com>
#  ssb   elg1024 2021-01-06 [E]
#
# only gpg-id "36DD093xxxxxxxxxxxxxF1F5F8C5751B" is extracted to be used later for initialize of password storage
key=$(gpg --no-auto-check-trustdb --list-secret-keys | grep ^sec -A1 | tail -1 | sed 's/ //g')

# initialize new password storage and use previously generated gpg-id for encryption
pass init $key

# install helper to use pass as credentials store for docker
curl -fsSL https://github.com/docker/docker-credential-helpers/releases/download/v0.6.3/docker-credential-pass-v0.6.3-amd64.tar.gz | tar x -z
sudo cp docker-credential-pass /usr/local/bin/docker-credential-pass
sudo chmod +x /usr/local/bin/docker-credential-pass

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
