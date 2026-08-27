<script setup>
import { computed, ref } from 'vue';

const authState = window.DTMAuth ?? {};
const mode = ref(authState.mode === 'register' ? 'register' : 'login');
const errors = ref(authState.errors ?? []);
const isRegister = computed(() => mode.value === 'register');

function switchMode(nextMode) {
  mode.value = nextMode;
  errors.value = [];
  window.history.pushState({}, '', nextMode === 'register' ? '/register' : '/login');
}

function submitForm(event) {
  event.target.querySelector('button[type="submit"]')?.setAttribute('aria-busy', 'true');
}
</script>

<template>
  <main class="auth-page min-h-screen px-4 py-6 antialiased sm:px-6 sm:py-8">
    <nav class="mx-auto mb-6 flex w-full max-w-5xl items-center justify-between">
      <a href="/" class="flex items-center gap-3 transition hover:opacity-80">
        <span class="auth-nav-mark flex h-10 w-10 items-center justify-center rounded-md text-lg font-black">D</span>
        <span class="text-lg font-black tracking-tight" style="color: var(--dtm-dark-blue);">DTMcode</span>
      </a>
      <a href="/" class="auth-link text-sm font-semibold">Voltar para início</a>
    </nav>

    <section class="auth-shell relative mx-auto w-full max-w-5xl overflow-hidden rounded-lg border bg-white">
      <div class="auth-forms grid min-h-[620px] lg:grid-cols-2">
        <form v-if="mode === 'login'" method="POST" action="/login" class="flex flex-col justify-center p-8 sm:p-12 lg:col-start-1 lg:p-14" @submit="submitForm">
          <input type="hidden" name="_token" :value="authState.csrf" />
          <div class="mb-8">
            <div class="text-sm font-semibold uppercase tracking-[0.28em]" style="color: var(--dtm-steel-blue);">Entrar</div>
            <h1 class="mt-3 text-4xl font-black tracking-[-0.05em]" style="color: var(--dtm-dark-blue);">Acesse o DTMcode</h1>
            <p class="mt-3 text-sm leading-6" style="color: var(--dtm-steel-blue);">Continue acompanhando seus dados hídricos.</p>
          </div>

          <div v-if="errors.length && mode === 'login'" class="mb-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ errors[0] }}</div>

          <label class="block text-sm font-medium" style="color: var(--dtm-dark-blue);">
            E-mail
            <input type="email" name="email" required autocomplete="email" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="seu@email.com" />
          </label>

          <label class="mt-5 block text-sm font-medium" style="color: var(--dtm-dark-blue);">
            Senha
            <input type="password" name="password" required autocomplete="current-password" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="Sua senha" />
          </label>

          <label class="mt-5 inline-flex items-center gap-2 text-sm" style="color: var(--dtm-steel-blue);">
            <input type="checkbox" name="remember" class="h-4 w-4 border-slate-300" style="accent-color: var(--dtm-purple);" />
            Lembrar-me
          </label>

          <button type="submit" class="auth-primary mt-7 w-full rounded-md px-6 py-3 text-sm font-semibold transition">Entrar</button>
        </form>

        <form v-else method="POST" action="/register" class="flex flex-col justify-center p-8 sm:p-12 lg:col-start-2 lg:p-14" @submit="submitForm">
          <input type="hidden" name="_token" :value="authState.csrf" />
          <div class="mb-7">
            <div class="text-sm font-semibold uppercase tracking-[0.28em]" style="color: var(--dtm-steel-blue);">Criar conta</div>
            <h2 class="mt-3 text-4xl font-black tracking-[-0.05em]" style="color: var(--dtm-dark-blue);">Comece no DTMcode</h2>
            <p class="mt-3 text-sm leading-6" style="color: var(--dtm-steel-blue);">Crie seu acesso ao painel de monitoramento.</p>
          </div>

          <div v-if="errors.length && mode === 'register'" class="mb-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ errors[0] }}</div>

          <label class="block text-sm font-medium" style="color: var(--dtm-dark-blue);">
            Nome
            <input type="text" name="name" required autocomplete="name" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="Seu nome" />
          </label>

          <label class="mt-4 block text-sm font-medium" style="color: var(--dtm-dark-blue);">
            E-mail
            <input type="email" name="email" required autocomplete="email" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="seu@email.com" />
          </label>

          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium" style="color: var(--dtm-dark-blue);">
              Senha
              <input type="password" name="password" required autocomplete="new-password" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="8 caracteres" />
            </label>
            <label class="block text-sm font-medium" style="color: var(--dtm-dark-blue);">
              Confirmar
              <input type="password" name="password_confirmation" required autocomplete="new-password" class="auth-input mt-2 w-full rounded-md border px-4 py-3 outline-none transition" placeholder="Repita a senha" />
            </label>
          </div>

          <button type="submit" class="auth-primary mt-7 w-full rounded-md px-6 py-3 text-sm font-semibold transition">Cadastrar</button>
        </form>
      </div>

      <div class="auth-slider pointer-events-none absolute inset-y-0 left-0 hidden w-1/2 p-10 text-white lg:flex lg:flex-col lg:justify-between" :class="{ 'auth-slider--register': isRegister }">
        <div>
          <div class="flex items-center gap-3">
            <div class="auth-panel__mark flex h-11 w-11 items-center justify-center rounded-md text-xl font-black">D</div>
            <div class="text-3xl font-black tracking-tight">DTMcode</div>
          </div>
          <p class="mt-6 max-w-sm text-sm leading-7 text-white/75">Monitoramento hídrico simples, humano e baseado nos seus próprios dados.</p>
        </div>

        <div class="auth-panel__badge rounded-lg border p-5">
          <div class="text-xs uppercase tracking-[0.24em] text-white/60">{{ isRegister ? 'Novo acesso' : 'Seu painel' }}</div>
          <div class="mt-3 text-2xl font-black">{{ isRegister ? 'Faça parte' : 'Bem-vindo de volta' }}</div>
          <p class="mt-2 text-sm leading-6 text-white/70">{{ isRegister ? 'Acompanhe cota, área e volume em um só lugar.' : 'Seus registros estão a um acesso de distância.' }}</p>
        </div>

        <div class="pointer-events-auto flex gap-3">
          <button v-if="isRegister" type="button" class="auth-slider__link border border-white/30 px-4 py-2 text-sm font-semibold transition" @click="switchMode('login')">Entrar</button>
          <button v-else type="button" class="auth-slider__link border border-white/30 px-4 py-2 text-sm font-semibold transition" @click="switchMode('register')">Criar conta</button>
        </div>
      </div>

      <div class="flex gap-3 border-t p-6 lg:hidden" style="border-color: var(--dtm-line);">
        <button v-if="isRegister" type="button" class="auth-mobile-tab--active w-full border px-4 py-2 text-sm font-semibold transition" @click="switchMode('login')">Entrar</button>
        <button v-else type="button" class="auth-mobile-tab--active w-full border px-4 py-2 text-sm font-semibold transition" @click="switchMode('register')">Criar conta</button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.auth-shell { border-color: var(--dtm-line); }
.auth-nav-mark { background-color: var(--dtm-dark-blue); color: var(--dtm-light-purple); }
.auth-slider { background-color: var(--dtm-dark-blue); transform: translateX(100%); transition: transform 500ms ease; }
.auth-slider--register { transform: translateX(0); }
.auth-slider__link:hover { background-color: var(--dtm-light-purple); border-color: var(--dtm-light-purple); color: var(--dtm-dark-blue); }
.auth-mobile-tab { border-color: var(--dtm-line); color: var(--dtm-steel-blue); }
.auth-mobile-tab--active { border-color: var(--dtm-purple); background-color: var(--dtm-purple); color: white; }
@media (prefers-reduced-motion: reduce) { .auth-slider { transition: none; } }
</style>
