import * as types from '../mutation-types'

const state = {
  all: window.STORE.languages.all || [],
  active: window.STORE.languages.all[0] || {}
}

// getters
const getters = {
  publishedLanguages: state => {
    return state.all.filter(language => language.published)
  }
}

const mutations = {
  [types.SWITCH_LANG] (state, { oldValue }) {
    function isMatchingLocale (language) {
      return language.value === oldValue.value
    }

    const index = state.all.findIndex(isMatchingLocale)
    const newIndex = index < (state.all.length - 1) ? (index + 1) : 0

    state.active = state.all[newIndex]
  },

  [types.UPDATE_LANG] (state, newValue) {
    function isMatchingLocale (language) {
      return language.value === newValue
    }

    const index = state.all.findIndex(isMatchingLocale)
    state.active = state.all[index]
  },

  [types.PUBLISH_LANG] (state, publishedValues) {
    state.all.forEach(function (language) {
      if (publishedValues.includes(language.value)) language.published = true
      else language.published = false
    })
  }
}

export default {
  state,
  getters,
  mutations
}
