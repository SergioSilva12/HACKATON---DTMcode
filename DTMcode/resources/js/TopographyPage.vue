<script setup>
import { ref } from 'vue';

const user = ref(window.DTMcode?.user ?? null);

function logout() {
  document.getElementById('logout-form')?.submit();
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-4 text-slate-800 sm:p-6 lg:p-8">
    <div class="mx-auto max-w-7xl rounded-2xl border border-[#58709f]/30 bg-white">
      <div class="flex flex-col lg:flex-row">
        <aside class="w-full border-b border-[#58709f]/30 bg-gray-50 p-5 lg:w-72 lg:border-b-0 lg:border-r">
          <div class="mb-8 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-md bg-[#251c51] text-lg font-black text-white">D</div>
            <div>
              <div class="text-2xl font-black text-[#251c51]">DTM</div>
              <div class="text-xs uppercase tracking-[0.26em] text-[#58709f]">code</div>
            </div>
          </div>
          <nav class="space-y-2">
            <a href="/" class="block rounded-md px-4 py-3 text-sm font-medium text-[#251c51] transition hover:bg-[#837fc5]/20">Início</a>
            <a href="/topografia" class="block rounded-md border-l-4 border-[#7c3494] bg-[#837fc5]/15 px-4 py-3 text-sm font-semibold text-[#251c51]">Topografia</a>
          </nav>
          <div class="mt-8 rounded-lg border border-[#58709f]/30 bg-white p-4">
            <div class="text-xs uppercase tracking-[0.2em] text-[#58709f]">Status</div>
            <div class="mt-3 text-2xl font-black text-[#251c51]">Operacional</div>
            <div class="mt-2 text-sm text-[#58709f]">{{ user?.name ?? 'Usuário' }} conectado ao sistema.</div>
          </div>
        </aside>

        <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
          <header class="mb-8 flex flex-col gap-4 border-b border-[#58709f]/30 pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-medium text-[#58709f]">Análise espacial</p>
              <h1 class="mt-1 text-3xl font-black tracking-tight text-[#251c51]">Topografia do Reservatório</h1>
            </div>
            <button type="button" @click="logout" class="bg-[#251c51] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#37326b]">Sair</button>
          </header>

          <section class="rounded-xl border border-dashed border-[#58709f]/50 bg-white">
            <div class="flex min-h-[520px] items-center justify-center p-6 text-center">
              <div>
                <div class="water-drop-mark mx-auto mb-5 h-16 w-16" aria-hidden="true"></div>
                <h2 class="text-xl font-bold text-[#251c51]">Área de visualização</h2>
                <p class="mt-2 max-w-md text-sm leading-6 text-[#58709f]">Espaço reservado para o mapa topográfico ou a máscara gerada pela U-Net.</p>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
