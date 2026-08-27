<template>
  <div class="p-4 space-y-6">
    <h2 class="text-2xl font-semibold">Dashboard de Exemplo — Volume Histórico e Topografia 3D</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <section class="bg-white shadow rounded p-4">
        <h3 class="text-lg font-medium mb-2">Volume Histórico (Curva CAV)</h3>
        <div id="volume-chart" class="w-full h-72"></div>
      </section>

      <section class="bg-white shadow rounded p-4">
        <h3 class="text-lg font-medium mb-2">Imagem Topográfica — Superfície 3D</h3>
        <div id="surface-chart" class="w-full h-72"></div>
      </section>
    </div>

    <p class="text-sm text-gray-600">Dados mockados associados ao e‑mail: <span class="font-mono">sergiolimasilva12@gmail.com</span></p>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const EMAIL = 'sergiolimasilva12@gmail.com'

// Local fallback mock data (used if API is not available)
function buildLocalMock() {
  const volume_history = []
  const start = new Date(2024, 0, 1)
  for (let i = 0; i < 18; i++) {
    const d = new Date(start)
    d.setMonth(start.getMonth() + i)
    const iso = d.toISOString().slice(0, 10)
    // build smooth seasonal variation
    const baseLevel = 100 + 5 * Math.sin(i * 0.6)
    const area = 5000 + 200 * Math.cos(i * 0.4)
    const volume = baseLevel * area * 0.001 // arbitrary scaling for example
    volume_history.push({ date: iso, level: +(baseLevel.toFixed(2)), area: Math.round(area), volume: Math.round(volume) })
  }

  // Build a 50x50 Z matrix with a gaussian basin and some noise
  const rows = 50, cols = 50
  const z = []
  const cx = (cols - 1) / 2
  const cy = (rows - 1) / 2
  for (let r = 0; r < rows; r++) {
    const row = []
    for (let c = 0; c < cols; c++) {
      const d = Math.hypot(c - cx, r - cy)
      const h = 40 - 20 * Math.exp(-0.02 * d * d) + (Math.random() - 0.5) * 1.2
      row.push(Number(h.toFixed(2)))
    }
    z.push(row)
  }

  return { volume_history, z }
}

async function fetchMockFromApi() {
  try {
    const res = await fetch(`/api/dashboard/mock-data?email=${encodeURIComponent(EMAIL)}`)
    if (!res.ok) throw new Error('API não disponível')
    return await res.json()
  } catch (err) {
    console.warn('Falha ao obter API, usando mock local:', err.message)
    return buildLocalMock()
  }
}

async function renderCharts(data) {
  // dynamic import of plotly to avoid bundler issues if not installed
  let Plotly
  try {
    Plotly = (await import('plotly.js-dist-min')).default || (await import('plotly.js-dist-min'))
  } catch (err) {
    // If module import fails, try using the global Plotly (e.g., CDN script)
    Plotly = window.Plotly
    if (!Plotly) throw err
  }

  // Volume history (line chart)
  const dates = data.volume_history.map(d => d.date)
  const volumes = data.volume_history.map(d => d.volume)
  const trace = {
    x: dates,
    y: volumes,
    mode: 'lines+markers',
    name: 'Volume (m3)',
    line: { color: '#0ea5e9' }
  }
  const layout = { margin: { t: 30, r: 10, l: 40, b: 40 }, xaxis: { title: 'Data' }, yaxis: { title: 'Volume (m3)' } }
  Plotly.newPlot('volume-chart', [trace], layout, { responsive: true })

  // Surface chart (3D)
  const surface = {
    z: data.z,
    type: 'surface',
    colorscale: 'Viridis',
    showscale: true
  }
  const layout3d = { margin: { t: 30 }, scene: { zaxis: { title: 'Cota (m)' } } }
  Plotly.newPlot('surface-chart', [surface], layout3d, { responsive: true })
}

onMounted(async () => {
  const data = await fetchMockFromApi()
  await renderCharts(data)
})
</script>

<style scoped>
/* Ensure Plotly charts fill their containers */
#volume-chart, #surface-chart { height: 100%; }
</style>
