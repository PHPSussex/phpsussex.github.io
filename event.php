<?php

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

// Get the next event's HTML node
$node = (new HttpBrowser(HttpClient::create()))
    ->request('GET', 'https://www.meetup.com/php-sussex/events/')
    ->filter('ul.eventList-list > li')
    ->first();

// Generate the content for the event
if ($node->count() === 0) {
    $event = '<p>Our next event hasn\'t been announced yet – <a href="https://www.meetup.com/php-sussex/">join our Meetup group</a> so you don\'t miss it!</p>';
} else {
    $title = $node->filter('a.eventCardHead--title')->text();
    $date = (new DateTime())
        ->setTimezone(new DateTimeZone('Europe/London'))
        ->setTimestamp((int) $node->filter('time')->attr('datetime') / 1000)
        ->format('l j F Y, g:i a T');
    $venue = $node->filter('address > p')->text();
    $url = $node->filter('a.eventCardHead--title')->link()->getUri();
    preg_match('#url\((.+)\)#', $node->filter('span.eventCardHead--photo')->attr('style'), $matches);
    $image = $matches[1];
    $event = sprintf(
        '<a href="%s" class="event"><img alt="Poster of the event" src="%s"><div><p class="title">%s</p><p class="date">%s</p><p class="venue">%s</p></div></a>',
        $url,
        $image,
        $title,
        $date,
        $venue,
    );
}

// Inject the event's content into the page
$index = preg_replace(
    '#<!-- event -->.*<!-- /event -->#s',
    sprintf('<!-- event -->%s<!-- /event -->', $event),
    file_get_contents('index.html')
);

// Overwrite the page's file
file_put_contents('index.html', $index);
