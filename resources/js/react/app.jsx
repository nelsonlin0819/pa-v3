import { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

function Clock() {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    const date = now.toLocaleDateString('zh-Hant', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    return (
        <div className="text-center">
            <p className="mb-2 text-sm tracking-widest text-slate-500 uppercase">{date}</p>
            <time className="font-mono text-7xl font-bold tabular-nums text-cyan-300 drop-shadow-[0_0_25px_rgba(34,211,238,0.35)]">
                {now.toLocaleTimeString('zh-Hant')}
            </time>
        </div>
    );
}

const el = document.getElementById('react-root');
if (el) {
    createRoot(el).render(<Clock />);
}
