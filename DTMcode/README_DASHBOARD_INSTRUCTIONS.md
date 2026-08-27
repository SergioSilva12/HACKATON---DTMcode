Dashboard de Exemplo — Instruções

Arquivos criados:
- vue_dashboard_example.vue  — componente Vue + Tailwind com dois gráficos (Volume histórico e Surface 3D)
- LaravelExampleController.php — controller Laravel de exemplo que retorna JSON mockado para o e-mail sergiolimasilva12@gmail.com

Como usar (frontend):
1. O componente exemplo tenta buscar dados em: /api/dashboard/mock-data?email=sergiolimasilva12@gmail.com
   - Se a API não estiver disponível, usa dados locais mockados embutidos no componente.
2. O componente usa Plotly.js. Duas opções para garantir que funcione:
   - Instalar via npm: npm install plotly.js-dist-min
   - Ou incluir CDN no seu index.html: <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
3. Coloque o componente em uma view Vue (por exemplo, em src/components/) e importe/registre conforme o seu setup (Vue 3 + Vite/CLI).

Como usar (backend — Laravel):
1. Copie LaravelExampleController.php para app/Http/Controllers/DashboardMockController.php
2. Registre a rota no routes/api.php:

   use App\Http\Controllers\DashboardMockController;

   Route::get('/dashboard/mock-data', [DashboardMockController::class, 'mockData']);

3. Acesse: GET /api/dashboard/mock-data?email=sergiolimasilva12@gmail.com

Notas de segurança e produção:
- Estes dados são apenas mock estático para prototipagem/visualização. Em produção, implemente autenticação e autorizações adequadas e persista dados reais em banco de dados.

Se quiser, posso:
- Copiar/colar o Controller diretamente em app/Http/Controllers e criar a rota automaticamente (preciso de permissão para editar esses arquivos),
- Ajustar o componente para usar um state manager (Vuex/Pinia) ou integrar com seu layout existente,
- Gerar e anexar um GIF/PNG dos frames 3D gerados anteriormente.
