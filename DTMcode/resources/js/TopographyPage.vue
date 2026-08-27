<script setup>
import { onMounted, ref } from 'vue';
import Plotly from 'plotly.js-dist-min';

const user = ref(window.DTMcode?.user ?? null);
const selectedType = ref('CAV');
const selectedFile = ref(null);
const isLoading = ref(false);
const errorMessage = ref('');
const cavSeries = ref([]);
const dtmMatrix = ref([]);
const plotContainer = ref(null);

function logout() {
  document.getElementById('logout-form')?.submit();
}

function onFileChange(event) {
  const file = event.target.files?.[0] ?? null;
  selectedFile.value = file;
  errorMessage.value = '';
}

function buildCavTrace() {
  return {
    x: cavSeries.value.map((item) => Number(item.label)),
    y: cavSeries.value.map((item) => Number(item.value)),
    mode: 'lines+markers',
    type: 'scatter',
    name: 'Cota x Volume',
    line: { color: '#251c51', width: 2 },
    marker: { color: '#7c3494', size: 8 },
  };
}

function renderSurfacePlot() {
  if (!plotContainer.value || !dtmMatrix.value.length) {
    return;
  }

  const data = [{
    type: 'surface',
    z: dtmMatrix.value,
    colorscale: 'Viridis',
    showscale: true,
    contours: {
      z: { show: true, usecolormap: true, highlightcolor: '#42f462', project: { z: true } },
    },
    hovertemplate: 'x=%{x}<br>y=%{y}<br>z=%{z}<extra></extra>',
  }];

  const layout = {
    title: 'Modelo topográfico 3D',
    autosize: true,
    margin: { l: 0, r: 0, b: 0, t: 40 },
    scene: {
      xaxis: { title: 'X' },
      yaxis: { title: 'Y' },
      zaxis: { title: 'Elevação (m)' },
      camera: { eye: { x: 1.5, y: 1.5, z: 1.2 } },
    },
  };

  Plotly.newPlot(plotContainer.value, data, layout, { responsive: true, displaylogo: false });
}

function renderCavChart() {
  if (!plotContainer.value || !cavSeries.value.length) {
    return;
  }

  const trace = buildCavTrace();
  const layout = {
    title: 'Variação de Cota x Volume',
    autosize: true,
    margin: { l: 40, r: 20, b: 40, t: 40 },
    xaxis: { title: 'Cota', type: 'linear' },
    yaxis: { title: 'Volume', type: 'linear' },
    paper_bgcolor: 'rgba(0,0,0,0)',
    plot_bgcolor: 'rgba(0,0,0,0)',
  };

  Plotly.newPlot(plotContainer.value, [trace], layout, { responsive: true, displaylogo: false });
}

function resetPlot() {
  if (!plotContainer.value) {
    return;
  }

  Plotly.purge(plotContainer.value);
}

async function submitFile() {
  if (!selectedFile.value) {
    errorMessage.value = 'Selecione um arquivo antes de enviar.';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';
  resetPlot();

  try {
    const detectedType = selectedFile.value?.name?.match(/\.(csv|xlsx|xls)$/i)
      ? 'CAV'
      : selectedFile.value?.name?.match(/\.(tif|tiff)$/i)
        ? 'DTM'
        : selectedType.value;

    selectedType.value = detectedType;

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('type', detectedType);

    const response = await fetch('/api/topography/process', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
      body: formData,
    });

    const rawText = await response.text();
    let payload = {};

    try {
      payload = rawText ? JSON.parse(rawText) : {};
    } catch (error) {
      console.error('Resposta inválida do backend:', rawText.slice(0, 300));
      throw new Error('O arquivo não pôde ser processado. Verifique se ele é um TIFF/CSV válido.');
    }

    if (!response.ok) {
      throw new Error(payload.message || 'Erro ao processar o arquivo.');
    }

    if (payload.type === 'CAV') {
      cavSeries.value = Array.isArray(payload.series) ? payload.series : [];
      dtmMatrix.value = [];
      renderCavChart();
      return;
    }

    cavSeries.value = [];
    dtmMatrix.value = Array.isArray(payload.matrix) ? payload.matrix : [];
    renderSurfacePlot();
  } catch (error) {
    console.error(error);
    errorMessage.value = error.message || 'Não foi possível processar o arquivo.';
    cavSeries.value = [];
    dtmMatrix.value = [];
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  window.addEventListener('resize', () => {
    if (cavSeries.value.length) {
      renderCavChart();
    }
    if (dtmMatrix.value.length) {
      renderSurfacePlot();
    }
  });
});
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

          <section class="mb-8 grid gap-6 xl:grid-cols-[360px_1fr]">
            <div class="rounded-2xl border border-[#58709f]/30 bg-white p-5">
              <h2 class="text-lg font-bold text-[#251c51]">Processamento do arquivo</h2>
              <div class="mt-5 space-y-4">
                <div>
                  <label class="mb-2 block text-sm font-semibold text-[#251c51]">Tipo de dado</label>
                  <select v-model="selectedType" class="w-full rounded-lg border border-[#58709f]/30 bg-gray-50 px-3 py-3 text-sm outline-none focus:border-[#251c51]">
                    <option value="CAV">CAV</option>
                    <option value="DTM">DTM</option>
                  </select>
                </div>
                <div>
                  <label class="mb-2 block text-sm font-semibold text-[#251c51]">Arquivo</label>
                  <input type="file" accept=".csv,.xlsx,.xls,.tif,.tiff" @change="onFileChange" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-[#251c51] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" />
                </div>
              </div>

              <button type="button" :disabled="isLoading || !selectedFile" @click="submitFile" class="mt-5 w-full rounded-md bg-[#251c51] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#37326b] disabled:cursor-not-allowed disabled:bg-slate-300">
                {{ isLoading ? 'Processando...' : 'Enviar e visualizar' }}
              </button>

              <p v-if="errorMessage" class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                {{ errorMessage }}
              </p>
            </div>

            <div class="rounded-2xl border border-[#58709f]/30 bg-white p-5">
              <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#251c51]">Visualização</h2>
                <span class="rounded-full border border-[#58709f]/30 bg-[#837fc5]/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#37326b]">
                  {{ selectedType }}
                </span>
              </div>

              <div v-if="!cavSeries.length && !dtmMatrix.length" class="flex min-h-[420px] items-center justify-center rounded-xl border border-dashed border-[#58709f]/40 bg-gray-50 text-sm text-slate-500">
                Ainda não há dados para visualizar. Faça o upload de um arquivo para iniciar a análise.
              </div>

              <div v-else ref="plotContainer" class="h-[420px] w-full"></div>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
