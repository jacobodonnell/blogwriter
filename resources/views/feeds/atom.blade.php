{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<feed xmlns="http://www.w3.org/2005/Atom">
    <title><![CDATA[{!! config('app.name', 'BlogWriter') !!}]]></title>
    <link href="{{ route('home') }}" rel="alternate" type="text/html" />
    <link href="{{ route('feed.atom') }}" rel="self" type="application/atom+xml" />
    <id>{{ route('home') }}/</id>
@if($lastUpdated)
    <updated>{{ $lastUpdated->toRfc3339String() }}</updated>
@endif
    <subtitle><![CDATA[{!! setting('profile_bio', '') !!}]]></subtitle>
    <generator>BlogWriter</generator>
@foreach($items as $item)
    <entry>
        <title><![CDATA[{!! $item->title !!}]]></title>
        <link href="{{ $item->url }}" rel="alternate" type="text/html" />
        <id>{{ $item->id }}</id>
        <published>{{ $item->publishedAt->toRfc3339String() }}</published>
        <updated>{{ ($item->updatedAt ?? $item->publishedAt)->toRfc3339String() }}</updated>
        <author>
            <name><![CDATA[{!! $item->authorName !!}]]></name>
        </author>
@if($item->summary)
        <summary type="html"><![CDATA[{!! $item->summary !!}]]></summary>
@endif
        <content type="html"><![CDATA[{!! $item->contentHtml !!}]]></content>
@if($item->categoryName)
        <category term="{{ $item->categoryName }}" />
@endif
    </entry>
@endforeach
</feed>
