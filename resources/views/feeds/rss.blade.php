{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><![CDATA[{!! config('app.name', 'BlogWriter') !!}]]></title>
        <link>{{ route('home') }}</link>
        <description><![CDATA[{!! setting('profile_bio', '') !!}]]></description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <atom:link href="{{ route('feed.rss') }}" rel="self" type="application/rss+xml" />
@if($lastUpdated)
        <lastBuildDate>{{ $lastUpdated->toRfc2822String() }}</lastBuildDate>
@endif
        <generator>BlogWriter</generator>
@foreach($items as $item)
        <item>
            <title><![CDATA[{!! $item->title !!}]]></title>
            <link>{{ $item->url }}</link>
            <guid isPermaLink="true">{{ $item->id }}</guid>
@if($item->summary)
            <description><![CDATA[{!! $item->summary !!}]]></description>
@endif
            <content:encoded><![CDATA[{!! $item->contentHtml !!}]]></content:encoded>
            <pubDate>{{ $item->publishedAt->toRfc2822String() }}</pubDate>
            <author>{{ $item->authorName }}</author>
@if($item->categoryName)
            <category><![CDATA[{!! $item->categoryName !!}]]></category>
@endif
        </item>
@endforeach
    </channel>
</rss>
