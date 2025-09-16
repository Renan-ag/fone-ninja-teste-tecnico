import "./bootstrap";
import { createApp } from "vue";
import App from "../js/components/App.vue";
import router from "../js/router";
import "@mdi/font/css/materialdesignicons.css";
import "../css/app.css";
import VueTheMask from "vue-the-mask";

import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

const vuetify = createVuetify({
    components,
    directives,
});

createApp(App).use(VueTheMask).use(vuetify).use(router).mount("#app");
