![CrowdSec Logo](images/logo_crowdsec.png)
# CrowdSec CAPI PHP client

## Developer guide


<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [Local development](#local-development)
  - [DDEV setup](#ddev-setup)
    - [DDEV installation](#ddev-installation)
    - [Prepare DDEV PHP environment](#prepare-ddev-php-environment)
  - [DDEV Usage](#ddev-usage)
    - [Use composer to update or install the lib](#use-composer-to-update-or-install-the-lib)
    - [Unit test](#unit-test)
    - [Integration test](#integration-test)
    - [Coding standards](#coding-standards)
      - [PHPCS Fixer](#phpcs-fixer)
      - [PHPSTAN](#phpstan)
      - [PHP Mess Detector](#php-mess-detector)
      - [PHPCS and PHPCBF](#phpcs-and-phpcbf)
      - [PSALM](#psalm)
      - [PHP Unit Code coverage](#php-unit-code-coverage)
- [Commit message](#commit-message)
  - [Allowed message `type` values](#allowed-message-type-values)
- [Release process](#release-process)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->



## Local development

There are many ways to install this library on a local PHP environment.

We are using [DDEV](https://ddev.readthedocs.io/en/stable/) because it is quite simple to use and customize.

Of course, you may use your own local stack, but we provide here some useful tools that depends on DDEV.


### DDEV setup

For a quick start, follow the below steps.


#### DDEV installation

This project is fully compatible with DDEV 1.21.2, and it is recommended to use this specific version.
For the DDEV installation, please follow the [official instructions](https://ddev.readthedocs.io/en/stable/#installation).
On a Linux distribution, you can run:
```
sudo apt-get -qq update
sudo apt-get -qq -y install libnss3-tools
curl -LO https://raw.githubusercontent.com/drud/ddev/master/scripts/install_ddev.sh
bash install_ddev.sh v1.21.2
rm install_ddev.sh
```


#### Prepare DDEV PHP environment

The final structure of the project will look like below.

```
crowdsec-capi-client-dev-project (choose the name you want for this folder)
│       
│
└───.ddev (do not change this folder name)
│   │   
│   │ (Cloned sources of a PHP specific ddev repo)
│   
└───my-own-modules (do not change this folder name)
    │   
    │
    └───capi-client (do not change this folder name)
       │   
       │ (Clone of this repo)
         
```

- Create an empty folder that will contain all necessary sources:
```bash
mkdir crowdsec-capi-client-dev-project
```

- Create a `capi-client` folder with sources of this repo:

```bash
cd crowdsec-capi-client-dev-project
mkdir -p my-own-modules/capi-client
cd my-own-modules/capi-client && git clone git@github.com:crowdsecurity/php-capi-client.git ./
```

- Create an empty `.ddev` folder for DDEV and clone our pre-configured DDEV repo:

```bash
cd crowdsec-capi-client-dev-project
mkdir .ddev && cd .ddev && git clone git@github.com:julienloizelet/ddev-php.git ./
```

By default, ddev will launch a PHP 7.2 container. If you want to work with another PHP version, copy the
corresponding config file. For example:

```bash
cd crowdsec-capi-client-dev-project
cp .ddev/config_overrides/config.php74.yaml .ddev/config.php74.yaml
```
- Launch DDEV

```bash
cd .ddev && ddev start
```
This should take some times on the first launch as this will download all necessary docker images.


### DDEV Usage


#### Use composer to update or install the lib

Run:

```bash
ddev composer update --working-dir ./my-own-modules/capi-client
```

#### Unit test

```bash
ddev php ./my-own-modules/capi-client/vendor/bin/phpunit  ./my-own-modules/capi-client/tests/Unit 
```

#### Integration test

In order to launch integration tests, we have to set some environment variables:

```bash
ddev php ./my-own-modules/capi-client/vendor/bin/phpunit  ./my-own-modules/capi-client/tests/Integration --testdox     
```


#### Coding standards

We set up some coding standards tools that you will find in the `tools/coding-standards` folder.
In order to use these, you will need to work with a PHP version >= 7.4 and run first:

```
ddev composer update --working-dir=./my-own-modules/capi-client/tools/coding-standards
```

##### PHPCS Fixer

We are using the [PHP Coding Standards Fixer](https://cs.symfony.com/)

With ddev, you can do the following:


```bash
ddev phpcsfixer my-own-modules/capi-client/tools/coding-standards/php-cs-fixer ../

```

##### PHPSTAN

To use the [PHPSTAN](https://github.com/phpstan/phpstan) tool, you can run:


```bash
ddev phpstan /var/www/html/my-own-modules/capi-client/tools/coding-standards phpstan/phpstan.neon /var/www/html/my-own-modules/capi-client/src

```


##### PHP Mess Detector

To use the [PHPMD](https://github.com/phpmd/phpmd) tool, you can run:

```bash
ddev phpmd ./my-own-modules/capi-client/tools/coding-standards phpmd/rulesets.xml ../../src

```

##### PHPCS and PHPCBF

To use [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) tools, you can run:

```bash
ddev phpcs ./my-own-modules/capi-client/tools/coding-standards my-own-modules/capi-client/src PSR12
```

and:

```bash
ddev phpcbf  ./my-own-modules/capi-client/tools/coding-standards my-own-modules/capi-client/src PSR12
```


##### PSALM

To use [PSALM](https://github.com/vimeo/psalm) tools, you can run:

```bash
ddev psalm ./my-own-modules/capi-client/tools/coding-standards ./my-own-modules/capi-client/tools/coding-standards/psalm
```

##### PHP Unit Code coverage

In order to generate a code coverage report, you have to:


- Enable `xdebug`:
```bash
ddev xdebug
```

To generate a html report, you can run:
```bash
ddev php -dxdebug.mode=coverage ./my-own-modules/capi-client/tools/coding-standards/vendor/bin/phpunit --configuration ./my-own-modules/capi-client/tools/coding-standards/phpunit/phpunit.xml
```

You should find the main report file `dashboard.html` in `tools/coding-standards/phpunit/code-coverage` folder.


If you want to generate a text report in the same folder:

```bash
ddev php -dxdebug.mode=coverage ./my-own-modules/capi-client/tools/coding-standards/vendor/bin/phpunit --configuration ./my-own-modules/capi-client/tools/coding-standards/phpunit/phpunit.xml --coverage-text=./my-own-modules/capi-client/tools/coding-standards/phpunit/code-coverage/report.txt
```

## Commit message

In order to have an explicit commit history, we are using some commits message convention with the following format:

    <type>(<scope>): <subject>

Allowed `type` are defined below.
`scope` value intends to clarify which part of the code has been modified. It can be empty or `*` if the change is a
global or difficult to assign to a specific part.
`subject` describes what has been done using the imperative, present tense.

Example:

    feat(watcher): Add a new endpoint for watcher


You can use the `commit-msg` git hook that you will find in the `.githooks` folder : 

```
cp .githooks/commit-msg .git/hooks/commit-msg
chmod +x .git/hooks/commit-msg
```

### Allowed message `type` values

- chore (automatic tasks; no production code change)
- ci (updating continuous integration process; no production code change)
- comment (commenting;no production code change)
- docs (changes to the documentation)
- feat (new feature for the user)
- fix (bug fix for the user)
- refactor (refactoring production code)
- style (formatting; no production code change)
- test (adding missing tests, refactoring tests; no production code change)

## Release process

We are using [semantic versioning](https://semver.org/) to determine a version number. To verify the current tag, 
you should run: 
```
git describe --tags `git rev-list --tags --max-count=1`
```

Before publishing a new release, there are some manual steps to take:

- Change the version number in the `Constants.php` file
- Update the `CHANGELOG.md` file

Then, you have to [run the action manually from the GitHub repository](https://github.com/crowdsecurity/php-capi-client/actions/workflows/release.yml)


Alternatively, you could use the [GitHub CLI](https://github.com/cli/cli): 
- create a draft release: 
```
gh workflow run release.yml -f tag_name=vx.y.z -f draft=true
```
- publish a prerelease:  
```
gh workflow run release.yml -f tag_name=vx.y.z -f prerelease=true
```
- publish a release: 
```
gh workflow run release.yml -f tag_name=vx.y.z
```

Note that the GitHub action will fail if the tag `tag_name` already exits.


 
