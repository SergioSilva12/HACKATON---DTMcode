import { createApp } from 'vue';
import '../css/app.css';
import LandingPage from './LandingPage.vue';
import DashboardLayout from './DashboardLayout.vue';
import AuthPage from './AuthPage.vue';
import TopographyPage from './TopographyPage.vue';

const appRoot = document.body.dataset.page;
const components = {
	auth: AuthPage,
	dashboard: DashboardLayout,
	landing: LandingPage,
	topography: TopographyPage,
};

const component = components[appRoot];
const mountPoint = document.getElementById('app');

if (component && mountPoint) {
	createApp(component).mount(mountPoint);
}
