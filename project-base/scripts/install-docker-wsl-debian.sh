#!/bin/bash

GREEN="\e[32m"
NC="\e[0m"

echo This is installation script that will install Docker in your Debian on Windows. You will be prompted to enter your password in order to install all necessary applications.

printf "${GREEN}Updating and installing necessary applications${NC}\n"

sudo apt update
sudo apt install -y --no-install-recommends apt-transport-https ca-certificates curl gnupg2 gnupg-agent  software-properties-common wget lsb-release apt-transport-https

printf "${GREEN}Installing Docker for Debian${NC}\n"

curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -
sudo add-apt-repository \
  "deb [arch=amd64] https://download.docker.com/linux/debian \
  $(lsb_release -cs) \
  stable"
sudo apt-get update -y && sudo apt-get install -y docker-ce docker-ce-cli containerd.io
sudo usermod -aG docker $USER

printf "${GREEN}Installing docker compose 1.28.5${NC}\n"

sudo curl -L "https://github.com/docker/compose/releases/download/1.28.5/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

printf "${GREEN}Installing PHP 8.1${NC}\n"

wget https://packages.sury.org/php/apt.gpg
sudo apt-key add apt.gpg
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php7.list
sudo apt update
sudo apt install -y php8.1

printf "${GREEN}Installing Composer${NC}\n"

wget -O composer-setup.php https://getcomposer.org/installer
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

printf "${GREEN}Installation successful${NC}\n"
