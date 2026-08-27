<template>
  <div class="relative h-[260px] overflow-hidden rounded-lg border border-[#58709f]/30 bg-gray-50">
    <div id="surface-plot" class="absolute inset-0"></div>
    <div v-if="error" class="absolute inset-0 grid place-items-center text-sm text-slate-500">{{ error }}</div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

const error = ref(null)

async function fetchMock(email) {
  try {
    const url = `/api/dashboard/mock-data?email=${encodeURIComponent(email)}`
    const res = await fetch(url)
    if (!res.ok) throw new Error('No mock data')
    return await res.json()
  } catch (err) {
    console.warn('fetchMock failed:', err.message)
    throw err
  }
}

async function renderSurface(zMatrix) {
  let Plotly
  try {
    Plotly = (await import('plotly.js-dist-min')).default || (await import('plotly.js-dist-min'))
  } catch (err) {
    Plotly = window.Plotly
    if (!Plotly) throw new Error('Plotly não disponível')
  }

  const data = [
    {
      z: zMatrix,
      type: 'surface',
      colorscale: 'Viridis',
      showscale: true,
      contours: { z: { show: true, usecolormap: true, highlightcolor: '#42a5f5', project: { z: true } } }
    }
  ]

  const layout = {
    margin: { t: 20, l: 40, r: 10, b: 30 },
    scene: { zaxis: { title: 'Cota (m)' }, xaxis: { visible: false }, yaxis: { visible: false } }
  }

  Plotly.newPlot('surface-plot', data, layout, { responsive: true })
}

onMounted(async () => {
  const userEmail = window.DTMcode?.user?.email || 'sergiolimasilva12@gmail.com'
  try {
    const res = await fetchMock(userEmail)
    if (!res || !res.z_matrix) {
      error.value = 'Nenhuma malha topográfica disponível.'
      return
    }
    await renderSurface(res.z_matrix)
  } catch (err) {
    error.value = 'Não foi possível carregar a superfície (usando mock local).'
    // As fallback, generate a small local matrix
    const rows = 30, cols = 30
    const cx = (cols - 1) / 2, cy = (rows - 1) / 2
    const z = []
    for (let r = 0; r < rows; r++) {
      const row = []
      for (let c = 0; c < cols; c++) {
        const d = Math.hypot(c - cx, r - cy)
        row.push(Number((40 - 15 * Math.exp(-0.03 * d * d)).toFixed(2)))
      }
      z.push(row)
    }
    await renderSurface(z)
  }
})
</script>

<style scoped>
#surface-plot { width: 100%; height: 100%; }
</style>