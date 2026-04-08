<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thanks - {{ $form->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family={{ str_replace(' ', '+', $theme['font_family']) }}:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --color-primary: {{ $theme['primary_color'] }};
            --color-bg: {{ $theme['background_color'] }};
            --font-family: '{{ $theme['font_family'] }}', system-ui, sans-serif;
        }
        html, body { background: var(--color-bg); font-family: var(--font-family); color: #fff; }
        .accent { color: var(--color-primary); }
    </style>
</head>
<body class="h-screen flex items-center justify-center text-center px-6">
    <div>
        <div class="text-6xl accent mb-4">&#10003;</div>
        <h1 class="text-4xl font-bold mb-3">Thank you!</h1>
        <p class="text-white/70 text-lg">Your response to <strong>{{ $form->title }}</strong> has been recorded.</p>
    </div>
</body>
</html>
