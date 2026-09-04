<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Usage · {{ config('app.name', 'nelsonlys.com') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col gap-12 px-6 py-16">
        <header>
            <p class="mb-3 text-sm font-medium uppercase tracking-widest text-cyan-400">chatbot-proxy</p>
            <h1 class="bg-gradient-to-r from-cyan-400 via-sky-400 to-violet-400 bg-clip-text text-5xl font-extrabold text-transparent">
                API Usage
            </h1>
        </header>

        <section class="flex flex-col gap-4">
            <h2 class="text-lg font-semibold text-slate-100">Endpoints</h2>
            @foreach ($endpoints as $path => $info)
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="flex items-center gap-3">
                        <span class="rounded-md bg-cyan-500/15 px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-cyan-400">{{ $info['method'] }}</span>
                        <code class="font-mono text-slate-100">{{ $path }}</code>
                    </div>
                    <ul class="mt-3 flex flex-col gap-1">
                        @foreach ($info['headers'] as $header)
                            <li class="font-mono text-sm text-slate-400">{{ $header }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </section>

        <section class="flex flex-col gap-4">
            <h2 class="text-lg font-semibold text-slate-100">Actions</h2>
            @foreach ($actions as $action => $params)
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <code class="font-mono font-semibold text-sky-400">{{ $action }}</code>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($params as $param)
                            <li class="rounded-md bg-slate-800 px-2 py-1 font-mono text-xs text-slate-300">{{ $param }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </section>

        <a href="/" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-400 transition hover:text-cyan-300">
            <span aria-hidden="true">←</span>
            回首頁
        </a>
    </main>
</body>
</html>
