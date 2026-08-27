<div class="auth-panel auth-panel--{{ $mode }} flex h-56 flex-col justify-between p-6 sm:p-10 lg:h-auto lg:p-10">
    <div>
        <div class="flex items-center gap-3">
            <div class="auth-panel__mark flex h-11 w-11 items-center justify-center rounded-2xl text-xl font-black">D</div>
            <div class="text-3xl font-black tracking-tight">DTMcode</div>
        </div>
        <p class="mt-6 max-w-sm text-sm leading-7 text-white/75">
            {{ $mode === 'login' ? 'Acesse seus dados de monitoramento e acompanhe o cenário hídrico com clareza.' : 'Crie seu acesso para acompanhar dados operacionais e previsões do reservatório.' }}
        </p>
    </div>

    <div class="auth-panel__badge rounded-2xl border p-5">
        <div class="text-xs uppercase tracking-[0.24em] text-white/60">DTMcode</div>
        <div class="mt-3 text-2xl font-black">{{ $mode === 'login' ? 'Bem-vindo de volta' : 'Comece agora' }}</div>
        <p class="mt-2 text-sm leading-6 text-white/70">{{ $mode === 'login' ? 'Seu painel está a um acesso de distância.' : 'Seu painel de monitoramento começa aqui.' }}</p>
    </div>
</div>