import * as types from '../mutation-types'

const state = {
  available: window.STORE.form.content || {},
  blocks: window.STORE.form.blocks || [],
  active: {}
}

// getters
const getters = { }

function setBlockID () {
  return Date.now()
}

const mutations = {
  [types.ADD_BLOCK] (state, blockInfos) {
    let block = blockInfos.block
    block.id = setBlockID()

    console.log('ADD_BLOCK')
    console.log(blockInfos.index)

    if (blockInfos.index > -1) {
      state.blocks.splice(blockInfos.index, 0, block) // add after a certain position
    } else {
      state.blocks.push(block) // or add a new block at the end of the list
    }
  },
  [types.MOVE_BLOCK] (state, fromTo) {
    if (fromTo.newIndex >= state.blocks.length) {
      var k = fromTo.newIndex - state.blocks.length
      while ((k--) + 1) {
        state.blocks.push(undefined)
      }
    }
    state.blocks.splice(fromTo.newIndex, 0, state.blocks.splice(fromTo.oldIndex, 1)[0])
  },
  [types.DELETE_BLOCK] (state, index) {
    state.blocks.splice(index, 1)
  },
  [types.DUPLICATE_BLOCK] (state, index) {
    let clone = Object.assign({}, state.blocks[index])
    clone.id = setBlockID()

    state.blocks.splice(index + 1, 0, clone)
  },
  [types.REORDER_BLOCKS] (state, newBlocks) {
    state.blocks = newBlocks
  },
  [types.ACTIVATE_BLOCK] (state, index) {
    if (state.blocks[index]) state.active = state.blocks[index]
    else state.active = {}
  }
}

export default {
  state,
  getters,
  mutations
}
