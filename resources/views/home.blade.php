@extends('layouts.app')

@section('title', 'hello Nelson')

@section('content')
<main class="flex min-h-screen flex-col items-center justify-center gap-12 px-6">
    <section class="text-center">
        <p class="mb-3 text-sm font-medium uppercase tracking-widest text-cyan-400">nelsonlys.com</p>
        <h1 class="bg-gradient-to-r from-cyan-400 via-sky-400 to-violet-400 bg-clip-text text-6xl font-extrabold text-transparent">
            hello {{ $name }}
        </h1>
        <a href="https://pa-v3.15064719d.workers.dev/"
           class="mt-8 inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-6 py-3 font-semibold text-slate-950 transition hover:bg-cyan-400">
            前往 pa-v3 worker
            <span aria-hidden="true">→</span>
        </a>
    </section>

    <section id="react-root"></section>
</main>
@endsection
