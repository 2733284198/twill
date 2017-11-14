import Vue from 'vue'
import store from '@/store'

// General behaviors
import main from '@/main'

// Plugins
import A17Config from '@/plugins/A17Config'

// configuration
Vue.use(A17Config)

// Dashboard
import a17ShortcutCreator from '@/components/dashboard/shortcutCreator.vue'
import A17ActivityFeed from '@/components/dashboard/activityFeed.vue'
import A17StatFeed from '@/components/dashboard/statFeed.vue'
import A17PopularFeed from '@/components/dashboard/popularFeed.vue'

/* eslint-disable no-new */
/* eslint no-unused-vars: "off" */
Window.vm = new Vue({
  store, // inject store to all children
  el: '#app',
  components: {
    'a17-shortcut-creator': a17ShortcutCreator,
    'a17-activity-feed': A17ActivityFeed,
    'a17-stat-feed': A17StatFeed,
    'a17-popular-feed': A17PopularFeed
  }
})

// DOM Ready general actions
document.addEventListener('DOMContentLoaded', main)
