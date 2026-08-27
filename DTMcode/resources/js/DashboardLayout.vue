<script setup>
import { onMounted, ref } from 'vue';
import WaterForecastChart from './WaterForecastChart.vue';

const user = ref(window.DTMcode?.user ?? null);
const summaryCards = ref([
  { label: 'Volume atual estimado', value: '—', trend: 'Sem dados', tone: 'bg-[#58709f]' },
  { label: 'Cota', value: '—', trend: 'Sem dados', tone: 'bg-[#37326b]' },
  { label: 'Última atualização', value: '—', trend: 'Sem dados', tone: 'bg-[#7c3494]' },
  { label: 'Variação de volume', value: '—', trend: 'Sem dados', tone: 'bg-[#837fc5]' },
]);

const navItems = [
  { label: 'Início', href: '/' },
  { label: 'Topografia', href: '/topografia' },
];

function logout() {
  const form = document.getElementById('logout-form');
  if (form) {
    form.submit();
  }
}

async function loadSummary() {
  try {
    const response = await fetch('/api/cav-data');
    if (!response.ok) {
      throw new Error('Erro ao buscar dados');
    }

    const result = await response.json();
    const items = result.data ?? [];

    if (!items.length) {
      return;
    }

    const last = items.at(-1);
    const previous = items.at(-2) ?? last;
    const variance = Number(last.volume ?? 0) - Number(previous.volume ?? 0);

    summaryCards.value = [
      { label: 'Volume atual estimado', value: `${Number(last.volume ?? 0).toFixed(2)} m³`, trend: `${variance >= 0 ? '+' : ''}${variance.toFixed(2)} m³`, tone: 'bg-[#58709f]' },
      { label: 'Cota', value: `${Number(last.cota ?? 0).toFixed(2)} m`, trend: 'Atual', tone: 'bg-[#37326b]' },
      { label: 'Última atualização', value: new Date(last.data_registro).toLocaleDateString('pt-BR'), trend: 'Registro', tone: 'bg-[#7c3494]' },
      { label: 'Variação de volume', value: `${variance >= 0 ? '+' : ''}${variance.toFixed(2)} m³`, trend: 'vs. anterior', tone: 'bg-[#837fc5]' },
    ];
  } catch (error) {
    console.error(error);
  }
}

onMounted(() => {
  loadSummary();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-4 text-slate-800 sm:p-6 lg:p-8">
    <div class="mx-auto max-w-7xl overflow-hidden rounded-2xl border border-[#58709f]/30 bg-white">
      <aside class="flex flex-col lg:flex-row">
        <div class="w-full border-b border-[#58709f]/30 bg-gray-50 p-5 lg:w-72 lg:border-b-0 lg:border-r">
          <div class="mb-8 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-md bg-[#251c51] text-lg font-black text-white">D</div>
            <div>
              <div class="text-2xl font-black tracking-tight">DTM</div>
              <div class="text-xs uppercase tracking-[0.26em] text-slate-500">code</div>
            </div>
          </div>

          <nav class="space-y-2">
            <a v-for="(item, index) in navItems" :key="item.label" :href="item.href" :class="['flex w-full items-center rounded-md px-4 py-3 text-left text-sm font-medium transition hover:bg-[#837fc5]/20', index === 0 ? 'bg-[#837fc5]/15 text-[#251c51]' : 'text-[#251c51]']">
              <span>{{ item.label }}</span>
            </a>
          </nav>

          <div class="mt-8 rounded-lg border border-[#58709f]/30 bg-white p-4">
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Status</div>
            <div class="mt-3 text-2xl font-black text-slate-900">Operacional</div>
            <div class="mt-2 text-sm text-slate-600">{{ user?.name ?? 'Usuário' }} conectado ao sistema.</div>
          </div>
        </div>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
          <header class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Painel principal</p>
              <h1 class="mt-1 text-3xl font-black tracking-[-0.05em] text-slate-900">Dashboard de monitoramento</h1>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
              <div class="rounded-md border border-[#58709f]/30 bg-white px-4 py-2 text-sm text-slate-600">{{ user?.email ?? 'sem e-mail' }}</div>
              <button type="button" @click="logout" class="rounded-md bg-[#251c51] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#37326b]">Sair</button>
            </div>
          </header>

          <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div v-for="card in summaryCards" :key="card.label" class="rounded-2xl border border-[#58709f]/30 bg-white p-4 transition duration-200 hover:-translate-y-1">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-sm text-slate-500">{{ card.label }}</p>
                  <div class="mt-3 text-3xl font-black tracking-[-0.04em] text-slate-900">{{ card.value }}</div>
                </div>
                <div :class="['rounded-md px-3 py-2 text-xs font-bold text-white', card.tone]">{{ card.trend }}</div>
              </div>
            </div>
          </section>

          <section class="mt-8">
            <WaterForecastChart />
          </section>

          <section class="mt-8 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-2xl border border-[#58709f]/30 bg-white p-5">
              <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Topografia</h2>
                <a href="/topografia" class="rounded-md border border-[#58709f]/40 bg-gray-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#37326b]">Abrir</a>
              </div>

              <div class="relative h-[260px] overflow-hidden rounded-lg border border-[#58709f]/30 bg-gray-50">
                <div class="absolute inset-0 opacity-60" style="background-color: rgba(131,127,197,0.08);"></div>
                <div class="absolute left-10 top-10 h-24 w-24 border border-[#837fc5]/50 bg-white"></div>
                <div class="absolute right-12 top-12 h-28 w-28 border border-[#58709f]/40 bg-white"></div>
                <div class="absolute bottom-10 left-14 h-32 w-32 border border-[#7c3494]/30 bg-gray-100"></div>
                <div class="absolute inset-0 grid place-items-center">
                  <div class="bg-[#37326b] px-4 py-2 text-sm font-semibold text-white">Mapa em preparação</div>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-[#58709f]/30 bg-white p-5">
              <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Resumo operacional</h2>
                <span class="rounded-md border border-[#58709f]/40 px-2.5 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#37326b]">OK</span>
              </div>

              <div v-if="summaryCards[0].value !== '—'" class="space-y-4">
                <div class="rounded-lg border border-[#58709f]/20 bg-gray-50 p-4">
                  <div class="text-sm text-slate-500">Volume atual</div>
                  <div class="mt-2 text-2xl font-black text-slate-900">{{ summaryCards[0].value }}</div>
                </div>
                <div class="rounded-lg border border-[#58709f]/20 bg-gray-50 p-4">
                  <div class="text-sm text-slate-500">Cota atual</div>
                  <div class="mt-2 text-2xl font-black text-slate-900">{{ summaryCards[1].value }}</div>
                </div>
              </div>

              <div v-else class="rounded-xl border border-dashed border-[#58709f]/40 bg-gray-50 p-6 text-sm text-slate-500">
                Nenhum dado registrado ainda.
              </div>
            </div>
          </section>
        </main>
      </aside>
    </div>
  </div>
</template>
