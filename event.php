<?php

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

// Load Composer's autoload
require_once __DIR__ . '/vendor/autoload.php';

// Get the next event
$event = Event::fromUrl('https://www.meetup.com/php-sussex/events/');

// Convert it to HTML
if (is_null($event)) {
    $section = '<p>Our next event hasn\'t been announced yet – <a href="https://www.meetup.com/php-sussex/">join our Meetup group</a> so you don\'t miss it!</p>';
} else {
    $section = sprintf(
        '<a href="%s" class="event"><img alt="Poster of the event" src="%s"><div><p class="title">%s</p><p class="date">%s</p><p class="venue">%s</p></div></a>',
        $event->url(),
        $event->image(),
        $event->title(),
        $event->date(),
        $event->venue(),
    );
}

// Inject the event's content into the page
$page = preg_replace(
    '#<!-- event -->.*<!-- /event -->#s',
    sprintf('<!-- event -->%s<!-- /event -->', $section),
    file_get_contents('index.html')
);

// Overwrite the page
file_put_contents('index.html', $page);

final class Event
{
    private function __construct(private Crawler $dom)
    {
    }

    public static function fromUrl(string $url): ?self
    {
        $dom = (new HttpBrowser(HttpClient::create()))
            ->request('GET', $url)
            ->filter('ul.eventList-list > li')
            ->first();

        return $dom->count() === 0 ? null : new self($dom);
    }

    public function date(): string
    {
        $node = $this->dom->filter('time');

        if ($node->count() === 0) {
            return 'Date to be confirmed';
        }

        return (new DateTime())
            ->setTimezone(new DateTimeZone('Europe/London'))
            ->setTimestamp((int) $node->attr('datetime') / 1000)
            ->format('l j F Y, g:i a T');
    }

    public function image(): string
    {
        $node = $this->dom->filter('span.eventCardHead--photo');

        if ($node->count() === 0) {
            return '/img/banner.png';
        }

        return preg_match('#url\((.+)\)#', $node->attr('style'), $matches) === 1
            ? $matches[1]
            : '/img/banner.png';
    }

    public function title(): string
    {
        return $this->dom->filter('a.eventCardHead--title')->text();
    }

    public function url(): string
    {
        return $this->dom->filter('a.eventCardHead--title')->link()->getUri();
    }

    public function venue(): string
    {
        $node = $this->dom->filter('address > p');

        return $node->count() === 0
            ? 'Venue to be confirmed'
            : $node->text();
    }
}
