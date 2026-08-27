<script setup>
const navItems = [
  { label: 'Sobre', href: '#about' },
  { label: 'Contato', href: '#contact' },
];
</script>

<template>
  <div class="landing-page min-h-screen">
    <header class="landing-header sticky top-0 z-20 border-b">
      <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
          <div class="landing-brand-mark flex h-10 w-10 items-center justify-center text-lg font-black">
            D
          </div>
          <div>
            <div class="text-lg font-black tracking-tight" style="color: var(--dtm-dark-blue);">DTMcode</div>
          </div>
        </div>

        <div class="hidden items-center gap-8 md:flex">
          <a v-for="item in navItems" :key="item.label" :href="item.href" class="text-sm font-medium transition" style="color: var(--dtm-steel-blue);">{{ item.label }}</a>
        </div>

        <div class="flex items-center gap-3">
          <a href="/login" class="landing-outline rounded-md border bg-white px-4 py-2 text-sm font-semibold transition">Login</a>
          <a href="/register" class="landing-action rounded-md px-4 py-2 text-sm font-semibold transition">Registro</a>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
      <section id="about" class="landing-intro relative overflow-hidden rounded-lg border border-transparent bg-white p-8 sm:p-12">
        <div class="landing-water-drop absolute right-12 top-10 hidden sm:block" aria-hidden="true"></div>
        <div class="relative max-w-3xl">
        <p class="landing-kicker text-xs font-semibold uppercase tracking-[0.28em]">Sobre o DTMcode</p>
        <h1 class="landing-title mt-4 max-w-3xl text-3xl font-black tracking-[-0.06em] sm:text-5xl">
          Monitoramento inteligente de recursos hídricos com dados reais e decisão mais clara.
        </h1>
        <p class="landing-copy mt-6 max-w-3xl text-base leading-8 sm:text-lg">
          O DTMcode reúne cota, área e volume em uma visão simples e confiável para acompanhamento de reservatórios, análise de histórico e projeção de cenários com base em registros reais do banco.
        </p>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
          <div class="landing-feature rounded-md border p-5">
            <div class="landing-feature__number text-sm font-semibold uppercase tracking-[0.18em]">01 / CAV</div>
            <div class="mt-3 text-2xl font-black" style="color: var(--dtm-dark-blue);">Histórico</div>
            <p class="landing-copy mt-2 text-sm leading-6">Registros organizados por data para análise de evolução do reservatório.</p>
          </div>

          <div class="landing-feature rounded-md border p-5">
            <div class="landing-feature__number text-sm font-semibold uppercase tracking-[0.18em]">02 / CENÁRIO</div>
            <div class="mt-3 text-2xl font-black" style="color: var(--dtm-dark-blue);">Previsão ativa</div>
            <p class="landing-copy mt-2 text-sm leading-6">A projeção é calculada após a ação do usuário, usando dados reais da base.</p>
          </div>

          <div class="landing-feature rounded-md border p-5">
            <div class="landing-feature__number text-sm font-semibold uppercase tracking-[0.18em]">03 / OPERAÇÃO</div>
            <div class="mt-3 text-2xl font-black" style="color: var(--dtm-dark-blue);">Clara</div>
            <p class="landing-copy mt-2 text-sm leading-6">Interface pensada para leitura direta, sem excesso de ruído visual.</p>
          </div>
        </div>

        <div class="landing-signal mt-10 flex max-w-sm items-end gap-3 rounded-md border border-[#dedbea] bg-[#f5f3fa] p-5">
          <div class="mr-auto">
            <div class="landing-kicker text-xs font-semibold uppercase tracking-[0.18em]">Leitura do sistema</div>
            <div class="mt-2 text-sm font-semibold" style="color: var(--dtm-dark-blue);">Dados organizados para decidir melhor</div>
          </div>
          <div class="landing-signal__bar h-8 w-2"></div>
          <div class="landing-signal__bar h-14 w-2"></div>
          <div class="landing-signal__bar h-10 w-2"></div>
        </div>
      </section>

      <section id="contact" class="landing-contact mt-10 rounded-lg border p-8 sm:p-10">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#837fc5]">Contato</p>
        <h2 class="mt-4 text-2xl font-black tracking-[-0.05em] sm:text-4xl">Fale com a equipe DTMcode.</h2>

        <form class="mt-8 grid gap-5 md:grid-cols-2">
          <label class="block text-sm font-medium text-white/80">
            Nome
            <input type="text" class="landing-input mt-2 w-full rounded-md border px-4 py-3 transition" placeholder="Seu nome" />
          </label>

          <label class="block text-sm font-medium text-white/80">
            E-mail
            <input type="email" class="landing-input mt-2 w-full rounded-md border px-4 py-3 transition" placeholder="seu@email.com" />
          </label>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-white/80">
              Mensagem
              <textarea rows="5" class="landing-input mt-2 w-full rounded-md border px-4 py-3 transition" placeholder="Descreva sua necessidade"></textarea>
            </label>
          </div>

          <div class="md:col-span-2">
            <button type="button" class="landing-action rounded-md px-6 py-3 text-sm font-semibold transition">Enviar mensagem</button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>
