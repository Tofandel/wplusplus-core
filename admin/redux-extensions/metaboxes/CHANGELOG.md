## 1.4.3
* Added global arg `metaboxes_save_defaults` which overrides the 
default functionality to NOT save defaults to the database. Set 
to true and defaults will be stored for metaboxes.

## 1.4.2
* url_to_postid failing on custom post types.
* Metabox fields displaying in option panel.
* Less database clutter/calls

## 1.4.1
* Classes arg attached to misnames variable, causes an error.
* CSS 'th' tag selector no longer set to 'auto'.

## 1.4.0
* Default value parameter added to redux_post_meta to avoid errors when no value is found.
* redux_post_meta now works within AJAX calls.
* Compensates for unique WooCommerce post types when used on products post type.
* Metaboxes no longer loads when using Visual Composer front end.
* post_format index error.
* Added isset check to postID in get_values to prevent errors.

## 1.3.8
* Proper enqueuing and required even if field type doesn't exist in primary panel.

## 1.3.7
* Metaboxes will now load even if an instance of Redux isn't "running"