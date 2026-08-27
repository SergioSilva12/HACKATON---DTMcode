<script setup>
import { onMounted, ref } from 'vue';
import {
  Chart,
  CategoryScale,
  Legend,
  LineController,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
} from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend);

const history = ref([]);
const forecast = ref([]);
const loading = ref(false);
const chartRef = ref(null);
const chartInstance = ref(null);
const hasData = ref(false);

async function fetchCavData() {
  try {
    const response = await fetch('/api/cav-data');
    if (!response.ok) {
      throw new Error('Erro ao buscar dados do banco');
    }

    const result = await response.json();
    history.value = (result.data ?? []).map((item) => ({
      label: new Date(item.data_registro).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' }),
      value: Number(item.volume ?? 0),
    }));

    hasData.value = history.value.length > 0;
    forecast.value = [];
    renderChart();
  } catch (error) {
    console.error(error);
    history.value = [];
    hasData.value = false;
    forecast.value = [];
    renderChart();
  }
}

async function generateForecast() {
  if (!hasData.value) {
    return;
  }

  loading.value = true;

  try {
    const response = await fetch('/api/cav-data/forecast');
    if (!response.ok) {
      throw new Error('Erro ao gerar previsão');
    }

    const result = await response.json();
    forecast.value = Array.isArray(result.forecast) ? result.forecast : [];
    renderChart();
  } catch (error) {
    console.error(error);
    forecast.value = [];
    renderChart();
  } finally {
    loading.value = false;
  }
}

function renderChart() {
  if (!chartRef.value) {
    return;
  }

  if (!hasData.value) {
    if (chartInstance.value) {
      chartInstance.value.destroy();
    }
    return;
  }

  const labels = history.value.map((item) => item.label);
  const historicalValues = history.value.map((item) => item.value);
  const forecastValues = forecast.value.length
    ? [...Array(history.value.length).fill(null), ...forecast.value.map((item) => item.value)]
    : Array(history.value.length).fill(null);

  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  chartInstance.value = new Chart(chartRef.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Volume histórico',
          data: historicalValues,
          borderColor: '#251c51',
          backgroundColor: 'rgba(15,23,42,0.08)',
          borderWidth: 2.5,
          tension: 0.35,
          pointRadius: 3,
          pointHoverRadius: 5,
        },
        {
          label: 'Previsão',
          data: forecastValues,
          borderColor: '#7c3494',
          borderDash: [8, 6],
          backgroundColor: 'rgba(124,52,148,0.08)',
          borderWidth: 2,
          tension: 0.3,
          pointRadius: 0,
          pointHoverRadius: 0,
          fill: false,
          display: forecast.value.length > 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: true },
      },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: false },
      },
    },
  });
}

onMounted(() => {
  fetchCavData();
});
</script>

<template>
  <div class="rounded-2xl border border-[#58709f]/30 bg-white p-4 sm:p-6">
    <div class="mb-5 flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Curva CAV</p>
        <h3 class="mt-1 text-xl font-bold text-slate-800">Volume histórico</h3>
      </div>

      <button
        type="button"
        class="rounded-md bg-[#251c51] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#37326b] disabled:cursor-not-allowed disabled:bg-slate-300"
        :disabled="loading || !hasData"
        @click="generateForecast"
      >
        {{ loading ? 'Gerando...' : 'Gerar Previsão' }}
      </button>
    </div>

    <div v-if="!hasData" class="flex h-[280px] items-center justify-center rounded-xl border border-dashed border-[#58709f]/40 bg-gray-50 text-sm text-slate-500">
      Nenhum dado registrado ainda.
    </div>

    <div v-else class="h-[340px] w-full">
      <canvas ref="chartRef" aria-label="Gráfico curva CAV"></canvas>
    </div>
  </div>
</template>
