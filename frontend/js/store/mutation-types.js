/* form */
export const UPDATE_FORM_TITLE = 'updateFormTitle'
export const UPDATE_FORM_PERMALINK = 'updateFormPermalink'
export const UPDATE_FORM_FIELD = 'updateFormField'
export const REFRESH_FORM_FIELD = 'refreshFormFieldUI'
export const REMOVE_FORM_FIELD = 'removeFormField'
export const ADD_FORM_BLOCK = 'addFormBlock'
export const DELETE_FORM_BLOCK = 'deleteFormBlock'
export const DUPLICATE_FORM_BLOCK = 'duplicateFormBlock'
export const REORDER_FORM_BLOCKS = 'reorderFormBlocks'
export const UPDATE_FORM_LOADING = 'updateFormLoading'
export const SET_FORM_ERRORS = 'setFormErrors'
export const CLEAR_FORM_ERRORS = 'clearFormErrors'
export const UPDATE_FORM_SAVE_TYPE = 'updateFormSaveType'

/* language */
export const SWITCH_LANG = 'switchLanguage'
export const UPDATE_LANG = 'updateLanguage'
export const PUBLISH_LANG = 'updatePublishedLanguage'

/* media-library */
export const UPDATE_MEDIA_TYPE_TOTAL = 'updateMediaTypeTotal'
export const INCREMENT_MEDIA_TYPE_TOTAL = 'incrementMediaTypeTotal'
export const DECREMENT_MEDIA_TYPE_TOTAL = 'decrementMediaTypeTotal'
export const SAVE_MEDIAS = 'saveSelectedMedias'
export const DESTROY_MEDIAS = 'destroySelectedMedias'
export const REORDER_MEDIAS = 'reorderSelectedMedias'
export const PROGRESS_UPLOAD_MEDIA = 'progressUploadMedia'
export const DONE_UPLOAD_MEDIA = 'doneUploadMedia'
export const ERROR_UPLOAD_MEDIA = 'errorUploadMedia'
export const DESTROY_SPECIFIC_MEDIA = 'destroyMediasInSelected'
export const UPDATE_MEDIA_MAX = 'updateMediaMax'
export const UPDATE_MEDIA_TYPE = 'updateMediaType'
export const SET_MEDIA_CROP = 'setMediaCrop'
export const SET_MEDIA_METADATAS = 'setMediaMetadatas'
export const UPDATE_MEDIA_CONNECTOR = 'updateMediaConnector'
export const UPDATE_MEDIA_MODE = 'updateMediaMode'
export const DESTROY_MEDIA_CONNECTOR = 'destroyMediaConnector'

/* browser */
export const SAVE_ITEMS = 'saveSelectedItems'
export const DESTROY_ITEMS = 'destroyAllItems'
export const DESTROY_ITEM = 'destroySelectedItem'
export const REORDER_ITEMS = 'reorderSelectedItems'
export const UPDATE_BROWSER_MAX = 'updateBrowserMax'
export const UPDATE_BROWSER_TITLE = 'updateBrowserTitle'
export const UPDATE_BROWSER_CONNECTOR = 'updateBrowserConnector'
export const DESTROY_BROWSER_CONNECTOR = 'destroyBrowserConnector'
export const UPDATE_BROWSER_ENDPOINT = 'updateBrowserEndpoint'
export const DESTROY_BROWSER_ENDPOINT = 'destroyBrowserEndpoint'

/* revision */
export const LOADING_REV = 'loadingRevision'
export const UPDATE_REV = 'updateRevision'
export const UPDATE_REV_CONTENT = 'updateRevisionContent'
export const UPDATE_REV_CURRENT_CONTENT = 'updatePreviewContent'

/* content */
export const ADD_BLOCK = 'addBlock'
export const MOVE_BLOCK = 'moveBlock'
export const DELETE_BLOCK = 'deleteBlock'
export const DUPLICATE_BLOCK = 'duplicateBlock'
export const REORDER_BLOCKS = 'reorderBlocks'
export const ACTIVATE_BLOCK = 'activateBlock'
export const ADD_BLOCK_PREVIEW = 'addBlockPreview'

/* publications */
export const UPDATE_PUBLISH_START_DATE = 'updatePublishStartDate'
export const UPDATE_PUBLISH_END_DATE = 'updatePublishEndDate'
export const UPDATE_PUBLISH_STATE = 'updatePublishState'
export const UPDATE_PUBLISH_VISIBILITY = 'updatePublishVisibility'
export const UPDATE_REVIEW_PROCESS = 'updateReviewProcess'

/* Datatable */
export const UPDATE_DATATABLE_DATA = 'updateDatableData'
export const UPDATE_DATATABLE_BULK = 'updateDatableBulk'
export const REPLACE_DATATABLE_BULK = 'replaceDatableBulk'
export const ADD_DATATABLE_COLUMN = 'addDatableColumn'
export const REMOVE_DATATABLE_COLUMN = 'removeDatableColumn'
export const UPDATE_DATATABLE_OFFSET = 'updateDatableOffset'
export const UPDATE_DATATABLE_PAGE = 'updateDatablePage'
export const UPDATE_DATATABLE_MAXPAGE = 'updateDatableMaxPage'
export const UPDATE_DATATABLE_NAV = 'updateDatableNavigation'
export const UPDATE_DATATABLE_VISIBLITY = 'updateDatableVisibility'
export const UPDATE_DATATABLE_SORT = 'updateDatableSort'
export const PUBLISH_DATATABLE = 'publishDatatable'
export const FEATURE_DATATABLE = 'featureDatatable'
export const UPDATE_DATATABLE_FILTER = 'updateDatableFilter'
export const UPDATE_DATATABLE_FILTER_STATUS = 'updateDatableFilterStatus'
export const CLEAR_DATATABLE_FILTER = 'clearDatableFilter'
export const UPDATE_DATATABLE_MESSAGE = 'updateDatableMessage'
export const UPDATE_DATATABLE_LOADING = 'updateDatableLoading'
export const UPDATE_DATATABLE_NESTED = 'updateDatatableNestedDatas'

/* Buckets */
export const ADD_TO_BUCKET = 'addToBucket'
export const DELETE_FROM_BUCKET = 'deleteFromBucket'
export const TOGGLE_FEATURED_IN_BUCKET = 'toggleFeaturedInBucket'
export const REORDER_BUCKET_LIST = 'reorderBucketList'
export const UPDATE_BUCKETS_DATASOURCE = 'updateBucketsDataSource'
export const UPDATE_BUCKETS_DATA = 'updateBucketsData'
export const UPDATE_BUCKETS_FILTER = 'updateBucketsFilter'
export const UPDATE_BUCKETS_DATA_OFFSET = 'updateBucketsDataOffset'
export const UPDATE_BUCKETS_DATA_PAGE = 'updateBucketsDataPage'
export const UPDATE_BUCKETS_MAX_PAGE = 'updateBucketsMaxPage'

/* Parents */
export const UPDATE_PARENT = 'updateParent'

/* Notifications */
export const SET_NOTIF = 'setNotification'
export const CLEAR_NOTIF = 'clearNotification'

/* Mutations taht must trigger a change in the preview in the block editor need to be listed here */
export const REFRESH_BLOCK_PREVIEW = [
  UPDATE_FORM_FIELD,
  REFRESH_FORM_FIELD,
  REMOVE_FORM_FIELD,
  ADD_FORM_BLOCK,
  DELETE_FORM_BLOCK,
  DUPLICATE_FORM_BLOCK,
  REORDER_FORM_BLOCKS,
  SWITCH_LANG,
  SET_MEDIA_CROP,
  SET_MEDIA_METADATAS,
  SAVE_MEDIAS,
  DESTROY_MEDIAS,
  DESTROY_SPECIFIC_MEDIA,
  REORDER_MEDIAS,
  SAVE_ITEMS,
  DESTROY_ITEMS,
  DESTROY_ITEM,
  REORDER_ITEMS
]
