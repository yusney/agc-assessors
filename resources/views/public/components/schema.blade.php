@if(!empty($schemas))
    @foreach($schemas as $schema)
        <script type="application/ld+json">
{!! json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endforeach
@endif
