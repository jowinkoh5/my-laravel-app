<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>API documentation · {{ config('app.name') }}</title>

        {{--
            Swagger UI is loaded from a CDN rather than committed into the
            repository. The specification itself is served locally from
            public/openapi.yaml, so the contract is still readable (and
            diffable) without network access.
        --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
        <style>
            body { margin: 0; }
            .docs-bar {
                display: flex; align-items: center; justify-content: space-between; gap: 1rem;
                padding: 0.75rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: #fff;
                font: 500 14px/1.4 system-ui, sans-serif;
            }
            .docs-bar a { color: #4f46e5; text-decoration: none; }
            .docs-bar a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="docs-bar">
            <a href="{{ route('catalog.index') }}">&larr; {{ config('app.name') }}</a>
            <a href="{{ url('openapi.yaml') }}">Download openapi.yaml</a>
        </div>

        <div id="swagger-ui"></div>

        <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
        <script>
            window.addEventListener('load', () => {
                if (typeof SwaggerUIBundle === 'undefined') {
                    document.getElementById('swagger-ui').innerHTML =
                        '<p style="padding:1.25rem;font:14px system-ui,sans-serif">' +
                        'Swagger UI could not be loaded. The specification is available at ' +
                        '<a href="{{ url('openapi.yaml') }}">openapi.yaml</a>.</p>';

                    return;
                }

                SwaggerUIBundle({
                    url: @js(url('openapi.yaml')),
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    persistAuthorization: true,
                });
            });
        </script>
    </body>
</html>
