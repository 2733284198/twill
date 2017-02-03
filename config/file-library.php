<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CMS Toolkit File Library configuration
    |--------------------------------------------------------------------------
    |
    | This allows you to provide the package with your configuration
    | for the file library disk, endpoint type and others options depending
    | on your endpoint type.
    |
    | Supported endpoint types: 'local' and 's3'.
    | Set cascade_delete to true to delete files on the storage too when
    | deleting from the file library.
    | If using the 'local' endpoint type, define a 'local_path' to store files.
    |
     */
    'disk' => 'libraries',
    'endpoint_type' => env('FILE_LIBRARY_ENDPOINT_TYPE', 's3'),
    'cascade_delete' => env('FILE_LIBRARY_CASCADE_DELETE', false),
    'local_path' => env('FILE_LIBRARY_LOCAL_PATH'),

];
