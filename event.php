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
        '<a href="%s" class="event border"><img alt="Poster of the event" src="%s"><div><p class="title">%s</p><p class="date">%s</p><p class="venue">%s</p></div></a>',
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
        for ($i = 0; $i < 5; $i++) {
            $dom = (new HttpBrowser(HttpClient::create()))
                ->request('GET', $url)
                ->filter('div#e-1');

            if ($dom->count() > 0) {
                break;
            }

            sleep(3);
        }

        return $dom->count() === 0 ? null : new self($dom);
    }

    public function date(): string
    {
        $node = $this->dom->filter('time');

        if ($node->count() === 0) {
            return 'Date to be confirmed';
        }

        return $node->text();
    }

    public function image(): string
    {
        $node = $this->dom->filter('img#image-e-1');

        if ($node->count() === 0) {
            return '/img/banner.png';
        }

        return $node->attr('src');
    }

    public function title(): string
    {
        return $this->dom->filter('span.ds-font-title-3')->first()->text();
    }

    public function url(): string
    {
        return $this->dom->filter('a#event-card-e-1')->link()->getUri();
    }

    public function venue(): string
    {
        $node = $this->dom->filter('span.text-gray6')->first();

        return $node->count() === 0
            ? 'Venue to be confirmed'
            : $node->text();
    }
}
