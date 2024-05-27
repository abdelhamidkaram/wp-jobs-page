<?php
// Add meta boxes for the job fields
function job_custom_meta_box() {
    add_meta_box(
        'job_meta_box',
        'Job Fields',
        'job_meta_box_callback',
        'job',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'job_custom_meta_box');

// Callback function to create custom fields in the meta box
function job_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field(basename(__FILE__), 'job_nonce');
    
    // Retrieve current values of meta fields if they exist
    $office = get_post_meta($post->ID, 'office', true);
    $hr_email = get_post_meta($post->ID, 'hr_email', true);
    
    // Output fields
    ?>
    <p>
        <label for="office">Office:</label>
        <select name="office" id="office" required>
            <option value="Riyadh office" <?php selected($office, 'Riyadh office'); ?>>Riyadh office</option>
            <option value="Cairo office" <?php selected($office, 'Cairo office'); ?>>Cairo office</option>
        </select>
    </p>
    <p>
        <label for="hr_email">HR Email:</label>
        <input type="email" name="hr_email" id="hr_email" value="<?php echo esc_attr($hr_email); ?>" required>
    </p>
    <?php
}

// Save custom field data when the post is saved
function save_job_meta_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['job_nonce'])) {
        return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['job_nonce'], basename(__FILE__))) {
        return;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save meta data
    if (isset($_POST['office'])) {
        update_post_meta($post_id, 'office', sanitize_text_field($_POST['office']));
    }
    if (isset($_POST['hr_email'])) {
        update_post_meta($post_id, 'hr_email', sanitize_email($_POST['hr_email']));
    }
}
add_action('save_post', 'save_job_meta_data');


add_action( 'init', function() {
	register_taxonomy( 'department', array(
	0 => '',
), array(
	'labels' => array(
		'name' => 'Departments',
		'singular_name' => 'Department',
		'menu_name' => 'Department',
		'all_items' => 'All Department',
		'edit_item' => 'Edit Department',
		'view_item' => 'View Department',
		'update_item' => 'Update Department',
		'add_new_item' => 'Add New Department',
		'new_item_name' => 'New Department Name',
		'search_items' => 'Search Department',
		'popular_items' => 'Popular Department',
		'separate_items_with_commas' => 'Separate department with commas',
		'add_or_remove_items' => 'Add or remove department',
		'choose_from_most_used' => 'Choose from the most used department',
		'not_found' => 'No department found',
		'no_terms' => 'No department',
		'items_list_navigation' => 'Department list navigation',
		'items_list' => 'Department list',
		'back_to_items' => '← Go to department',
		'item_link' => 'Department Link',
		'item_link_description' => 'A link to a department',
	),
	'public' => true,
	'show_in_menu' => true,
	'show_in_rest' => true,
) );
} );

add_action( 'init', function() {
	register_post_type( 'job', array(
	'labels' => array(
		'name' => 'Jobs',
		'singular_name' => 'Job',
		'menu_name' => 'Jobs',
		'all_items' => 'All Jobs',
		'edit_item' => 'Edit Job',
		'view_item' => 'View Job',
		'view_items' => 'View Jobs',
		'add_new_item' => 'Add New Job',
		'add_new' => 'Add New Job',
		'new_item' => 'New Job',
		'parent_item_colon' => 'Parent Job:',
		'search_items' => 'Search Jobs',
		'not_found' => 'No jobs found',
		'not_found_in_trash' => 'No jobs found in Trash',
		'archives' => 'Job Archives',
		'attributes' => 'Job Attributes',
		'insert_into_item' => 'Insert into job',
		'uploaded_to_this_item' => 'Uploaded to this job',
		'filter_items_list' => 'Filter jobs list',
		'filter_by_date' => 'Filter jobs by date',
		'items_list_navigation' => 'Jobs list navigation',
		'items_list' => 'Jobs list',
		'item_published' => 'Job published.',
		'item_published_privately' => 'Job published privately.',
		'item_reverted_to_draft' => 'Job reverted to draft.',
		'item_scheduled' => 'Job scheduled.',
		'item_updated' => 'Job updated.',
		'item_link' => 'Job Link',
		'item_link_description' => 'A link to a job.',
	),
	'public' => true,
	'show_in_rest' => true,
	'menu_icon' => 'dashicons-businessman',
	'supports' => array(
		0 => 'title',
		1 => 'editor',
		2 => 'thumbnail',
		3 => 'excerpt',

	),
	'taxonomies' => array(
		0 => 'department',
	),
	'delete_with_user' => false,
) );
} );

