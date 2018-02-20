const getDepth = ({children}) => 1 + (children && children.length > 0 ? Math.max(...children.map(getDepth)) : 0)

export default {
  props: {
    /**
     * Enable or not a nested listing
     * @type {Boolean}
     */
    nested: {
      type: Boolean,
      default: false
    },
    /**
     * Define the max depth level for a nested listing.
     * User can't add element in a listing if this value is reached.
     * @type {number}
     */
    maxDepth: {
      type: Number,
      default: 1
    },
    /**
     * The current nested depth
     * @type {number}
     */
    depth: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      currentElDepth: undefined
    }
  },
  computed: {
    updateTracker () {
      return this.$store.state.datatable.updateTracker
    },
    draggableGetComponentData: function () {
      return {
        props: {
          depth: this.depth
        }
      }
    }
  },
  methods: {
    onStart: function (event) {
      event.item.classList.add('datatable--selected')
      document.querySelector('.datatable').classList.add('datatable--dragging')
    },
    onMove: function (event) {
      if (!this.nested) return true

      if (typeof this.currentElDepth === 'undefined') {
        const children = JSON.parse(JSON.stringify(event.draggedContext.element))
        this.currentElDepth = getDepth(children)
      }

      const targetDepth = event.relatedContext.component.componentData.props.depth
      return targetDepth + this.currentElDepth <= this.maxDepth + 1
    },
    onEnd: function (event) {
      this.currentElDepth = undefined
      event.item.classList.remove('datatable--selected')
      document.querySelector('.datatable').classList.remove('datatable--dragging')
    },
    saveNewTree: function (isChangingParents) {
      let self = this
      const isNestedAction = isChangingParents ? true : self.nested
      const action = isNestedAction ? 'setDatatableNestedDatas' : 'setDatatableDatas'

      function save () {
        self.$store.commit('updateDatableTracker', 0)
        self.$store.dispatch(action)
      }

      // Proof of concepts
      if (isChangingParents) {
        // 2 moves need to happen so we can save the new tree (1 move to remove from list and a second to add to a new list)
        self.$store.commit('updateDatableTracker', 1)
        if (self.updateTracker >= 2) save()
      } else {
        // reorder rows
        save()
      }
    }
  }
}
