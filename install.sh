#!/bin/bash

check_composer() {
	if [ ! -x "$(command -v composer)" ]; then
		echo "Composer is not installed or not executable. Install it first."
		exit 1
	fi
}

check_git() {
	if [ ! -x "$(command -v git)" ]; then
		echo "Git is not installed or not executable. Install it first."
		exit 1
	fi
}

check_novaxis_dir() {
	if [ -d "novaxis" ]; then
		echo "novaxis directory found. Remove it first."
		exit 1
	fi
}

install() {
	check_git
	check_composer
	check_novaxis_dir

	git clone https://github.com/naxeion/novaxis && cd novaxis

	export COMPOSER_ALLOW_SUPERUSER=1

	composer install --ignore-platform-reqs

	chmod +x bin/novaxis
	make reinstall
}

install
