# PHP Sussex Website

## Usage

Clone the repository:

```
$ git clone git@github.com:PHPSussex/phpsussex.github.io.git phpsussex.uk
$ cd phpsussex.uk
```

Start the local server:

```
$ ./bin/serve.sh
```

Navigate to [localhost:8000](http://localhost:8000/).

## How it works

The [`event.php`](/event.php) script fetches the latest event from the [Meetup website](https://www.meetup.com/php-sussex/) and inserts it in [`index.html`](/index.html):

```
$ php event.php
```

It uses Symfony's [BrowserKit](https://symfony.com/doc/current/components/browser_kit.html), [HTTP Client](https://symfony.com/doc/current/http_client.html) and [CssSelector](https://symfony.com/doc/current/components/css_selector.html) components to scrape the event from the group's page.

The [`event.yml`](/.github/workflows/event.yml) GitHub workflow automatically runs every day at midnight to update and commit [`index.html`](/index.html) if a newer event was published.

If the workflow suddenly starts failing, it's probably because the Meetup website layout has changed.