<x-mail::message>
# Nachricht zu deinem Event: {{ $event->title }}

{{ $messageBody }}

<x-mail::button :url="route('event.show', $event->slug)">
Zum Event
</x-mail::button>

Viele Grüße,<br>
{{ $event->vendor->name ?? config('app.name') }} via Ticketsout24
</x-mail::message>
