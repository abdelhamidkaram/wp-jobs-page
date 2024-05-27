<?php
/*
 Plugin Name: Trend Job 
*/

require_once plugin_dir_path(__FILE__) . '/custom-post-type.php';
require_once plugin_dir_path(__FILE__) . '/jobs-page.php';

function enqueue_plugin_styles()
{
    wp_enqueue_style('plugin-styles', plugin_dir_url(__FILE__) . '/assets/style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('plugin-script', plugin_dir_url(__FILE__) . '/assets/script.js', ['jquery']);
}
add_action('wp_enqueue_scripts', 'enqueue_plugin_styles');





// Hook for table creation
register_activation_hook( __FILE__, 'create_custom_table' );

function create_custom_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'job_applications';

    // Check if the table already exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) NOT NULL,
            job_id varchar(20) NOT NULL,
            cover_letter text NOT NULL,
            cv_url varchar(255) DEFAULT '' NOT NULL,
            date_applied datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}



function add_ajax_url_to_frontend()
{
    wp_localize_script('plugin-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'add_ajax_url_to_frontend');


// get all jobs 
function your_ajax_callback()
{
    return get_all_posts();
}

add_action('wp_ajax_your_ajax_action', 'your_ajax_callback');
add_action('wp_ajax_nopriv_your_ajax_action', 'your_ajax_callback'); // For non-logged in users


// get one job by id 
function get_job_details_ajax_callback()
{
    $post = $_POST;

    // Query the posts
    $post_obj = get_post($post['id']);
    wp_reset_postdata();

    if (!empty($post_obj)) {
        // Send back the posts as JSON
        $post_obj->office = get_post_meta($post_obj->ID, 'office');
        $department = wp_get_post_terms($post_obj->ID, array('department'));
        $post_obj->department = $department ? $department[0]->name : '';

        wp_send_json($post_obj);
    } else {
        // No posts found
        wp_send_json(array('error' => 'No posts found'));
    }
}

add_action('wp_ajax_get_job_details_ajax_action', 'get_job_details_ajax_callback');
add_action('wp_ajax_nopriv_get_job_details_ajax_action', 'get_job_details_ajax_callback'); // For non-logged in users


// filter jobs by term_id 
function job_filter_by_department_ajax_callback()
{

    // Query the posts
    if ( $_POST['term_id'] == 0) {

        return get_all_posts();
    };

    $args = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'department',
                'field' => 'term_id',
                'terms' => $_POST['term_id']
            )
        )
    );

    // Query the posts
    $query = new WP_Query($args);

    wp_reset_postdata();

    // Check if there are posts
    if ($query->have_posts()) {
        $posts = array();

        // Loop through each post
        while ($query->have_posts()) {
            $query->the_post();

            // Get post data
            global $post;
            $post_obj = $post ;
            $post_obj->office = get_post_meta($post_obj->ID, 'office' , true);
            $department = wp_get_post_terms($post_obj->ID, array('department'));
            $post_obj->department = $department ? $department[0]->name : '';
    
            // Add post data to posts array
            $posts[] = $post_obj;
        }

        // Reset post data
        wp_reset_postdata();

        // Send back the posts as JSON
        wp_send_json($posts);
    } else {
        // No posts found
        wp_send_json(array('error' => 'No posts found'));
    }
}

add_action('wp_ajax_job_filter_by_department_ajax_action', 'job_filter_by_department_ajax_callback');
add_action('wp_ajax_nopriv_job_filter_by_department_ajax_action', 'job_filter_by_department_ajax_callback');




// filter jobs by  keyword 
function job_filter_by_keyword_ajax_callback()
{

    // Query the posts
    if ( empty($_POST['keyword'])) {
        
        return get_all_posts();
    };

    $args = array(
        'post_status'=>'publish',
        'post_type' => 'job',
        'posts_per_page' => -1,
        's' => $_POST['keyword'] 
    );

    // Query the posts
    $query = new WP_Query($args);

    wp_reset_postdata();

    // Check if there are posts
    if ($query->have_posts()) {
        $posts = array();
        while ($query->have_posts()) {
            $query->the_post();
            global $post;
            $post_obj = $post ;
            $post_obj->office = get_post_meta($post_obj->ID, 'office' , true);
            $posts[] = $post_obj;
        }

        // Reset post data
        wp_reset_postdata();

        // Send back the posts as JSON
        wp_send_json($posts);
    } else {
        // No posts found
        wp_send_json(array('error' => 'No posts found'));
    }
}

add_action('wp_ajax_job_filter_by_keyword_ajax_action', 'job_filter_by_keyword_ajax_callback');
add_action('wp_ajax_nopriv_job_filter_by_keyword_ajax_action', 'job_filter_by_keyword_ajax_callback');



// Handle form submission
function handle_form_submission() {
    global $wpdb;

    // Sanitize the input fields
    $name         = sanitize_text_field( $_POST['name'] );
    $email        = sanitize_email( $_POST['email'] );
    $phone        = sanitize_text_field( $_POST['phone'] );
    $job_id       = sanitize_text_field( $_POST['job_id'] );
    $cover_letter = sanitize_textarea_field( $_POST['cover_letter'] );

    // Validate email
    if ( ! is_email( $email ) ) {
        echo 'Invalid email address.';
        return;
    }

    // Handle file upload
    $cv_url = '';
    if ( ! empty( $_FILES['cv']['name'] ) ) {
        $uploaded_file = $_FILES['cv'];
        $upload_overrides = array( 'test_form' => false );

        // Handle the upload using WordPress functions
        $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

        if ( $movefile && ! isset( $movefile['error'] ) ) {
            $cv_url = $movefile['url']; // URL of the uploaded file
        } else {
            echo 'Failed to upload file: ' . $movefile['error'];
            return;
        }
    }

    // Insert data into the database
    $table_name = $wpdb->prefix . 'job_applications';

    $wpdb->insert(
        $table_name,
        array(
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'job_id'       => $job_id,
            'cover_letter' => $cover_letter,
            'cv_url'       => $cv_url,
            'date_applied' => current_time('mysql')
        )
    );

    // Prepare the email content
    $message = "Name: $name\n";
    $message .= "Email: $email\n";
    $message .= "Phone: $phone\n";
    $message .= "Job ID: $job_id\n";
    $message .= "Cover Letter:\n$cover_letter\n";
    if ( $cv_url ) {
        $message .= "CV: $cv_url\n";
    }

    // Get the HR email from the job post meta
    $to = get_post_meta($job_id, 'hr_email', true);
    if (!$to) {
        $to = get_option('admin_email'); // Fallback to admin email if HR email is not set
    }

    $subject = 'New Job Application';
    $headers = 'From: ' . $name . ' <' . $email . '>' . "\r\n";

    $sent = wp_mail( $to, $subject, $message, $headers );

    if ( $sent ) {
        return  wp_send_json([
            'status' => true,
            'message'=>'Thank you for your application.'
        ]);
    } else {
        return wp_send_json([
            'status' => false,
            'message'=>'Failed to send your application.'
        ]);
    }
}
add_action('wp_ajax_handle_form_submission', 'handle_form_submission');
add_action('wp_ajax_nopriv_handle_form_submission', 'handle_form_submission');




// ------- display applications -----------------------------//

// Add submenu page to 'job' post type
function register_job_applications_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=job', // Parent slug
        'Job Applications',       // Page title
        'Applications',           // Menu title
        'manage_options',         // Capability
        'job_applications',       // Menu slug
        'display_job_applications'// Callback function
    );
}
add_action( 'admin_menu', 'register_job_applications_submenu_page' );

function display_job_applications() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'job_applications';

    // Retrieve all job applications
    $applications = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_applied DESC" );

    ?>
    <div class="wrap">
        <h1>Job Applications</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="manage-column column-columnname" scope="col">Name</th>
                    <th class="manage-column column-columnname" scope="col">Email</th>
                    <th class="manage-column column-columnname" scope="col">Phone</th>
                    <th class="manage-column column-columnname" scope="col">Job ID</th>
                    <th class="manage-column column-columnname" scope="col">Cover Letter</th>
                    <th class="manage-column column-columnname" scope="col">CV URL</th>
                    <th class="manage-column column-columnname" scope="col">Date Applied</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $applications ) : ?>
                    <?php foreach ( $applications as $application ) : ?>
                        <tr>
                            <td><?php echo esc_html( $application->name ); ?></td>
                            <td><?php echo esc_html( $application->email ); ?></td>
                            <td><?php echo esc_html( $application->phone ); ?></td>
                            <td><?php echo esc_html( $application->job_id ); ?></td>
                            <td><?php echo esc_html( $application->cover_letter ); ?></td>
                            <td><a href="<?php echo esc_url( $application->cv_url ); ?>" target="_blank">View CV</a></td>
                            <td><?php echo esc_html( $application->date_applied ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}




// ------ helper function 
function get_all_posts()
{
    $args = array(
        'post_status' => 'publish' ,
        'post_type' => 'job',
        'posts_per_page' => -1,

    );

    // Query the posts
    $query = new WP_Query($args);

    // Check if there are posts
    if ($query->have_posts()) {
        $posts = array();

        // Loop through each post
        while ($query->have_posts()) {
            $query->the_post();

            global $post;
            $post_obj = $post ;
            $post_obj->office = get_post_meta($post_obj->ID, 'office' , true);
            $department = wp_get_post_terms($post_obj->ID, array('department'));
            $post_obj->department = $department ? $department[0]->name : '';
    
            // Add post data to posts array
            $posts[] = $post_obj;
        }

        // Reset post data
        wp_reset_postdata();

        // Send back the posts as JSON
        wp_send_json($posts);
    } else {
        // No posts found
        wp_send_json(array('error' => 'No posts found'));
    }
}

