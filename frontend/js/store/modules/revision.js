import revisionAPI from '../api/revision'
import { REVISION } from '../mutations'
import { getFormData } from '@/utils/getFormData.js'

const state = {
  loading: false,
  active: {},
  activeContent: '',
  currentContent: '',
  all: window.STORE.revisions || []
}

// getters
const getters = { }

const mutations = {
  [REVISION.LOADING_REV] (state) {
    state.loading = true
  },
  [REVISION.UPDATE_REV] (state, newValue) {
    function isMatchingRev (revision) {
      return revision.id === newValue
    }

    const index = state.all.findIndex(isMatchingRev)

    if (index !== -1) state.active = state.all[index]
    else state.active = {}
  },
  [REVISION.UPDATE_REV_CONTENT] (state, fullHTML) {
    state.loading = false
    state.activeContent = fullHTML
  },
  [REVISION.UPDATE_REV_CURRENT_CONTENT] (state, fullHTML) {
    state.loading = false
    state.currentContent = fullHTML
  }
}

const actions = {
  getCurrentContent ({ commit, rootState }) {
    return new Promise((resolve, reject) => {
      commit(REVISION.LOADING_REV)

      let formData = getFormData(rootState)

      if (rootState.language.all.length > 1) {
        formData.activeLanguage = rootState.language.active.value
      }

      revisionAPI.getRevisionContent(
        rootState.form.previewUrl,
        formData,
        data => {
          commit(REVISION.UPDATE_REV_CURRENT_CONTENT, data)
          resolve()
        },
        errorResponse => {
          reject(errorResponse)
        }
      )
    })
  },
  getRevisionContent ({ commit, state, rootState }) {
    return new Promise((resolve, reject) => {
      commit(REVISION.LOADING_REV)

      let id = 0

      if (Object.keys(state.active).length === 0) id = state.all[0].id
      else id = state.active.id

      let revisionData = { revisionId: id }

      if (rootState.language.all.length > 1) {
        revisionData.activeLanguage = rootState.language.active.value
      }

      revisionAPI.getRevisionContent(
        rootState.form.previewUrl,
        revisionData,
        data => {
          commit(REVISION.UPDATE_REV_CONTENT, data)
          resolve()
        },
        errorResponse => {
          reject(errorResponse)
        }
      )
    })
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
