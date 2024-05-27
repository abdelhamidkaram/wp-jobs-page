<?php
add_shortcode(
    'job-page',
    function () {

?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <div class="jop-page">
        <main class="wrap">
            <div class="filter-wrap">
                <div class="department">
                    <i class="fa-solid fa-briefcase"></i>

                    <?php

                    $terms = get_terms(array(
                        'taxonomy' => 'department',
                        'hide_empty' => true,
                    ));

                    if (!empty($terms) && !is_wp_error($terms)) {
                        echo '<select class="department-filter">';
                        echo '<option value="0">All Departments</option>';

                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }

                        echo '</select>';
                    }

                    ?>




                </div>
                <div class="search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" placeholder="Keywords..." />
                </div>
            </div><!--end filter-wrap !-->


            <div class="job-content-wrap">
                <div class="jobs-list">
                    <?php
                    $args = array(
                        'post_type' => 'job',
                        'posts_per_page' => -1,
                        'order' => 'ASC',
                    );
                    $the_query = new WP_Query($args); ?>

                    <h2>Available Opportunites</h2>
                    <span class="jobs-count"><?php echo $the_query->post_count ?> </span><span class="sub-title">job listed</span>

                    <div id="jop-items">
                        <?php if ($the_query->have_posts()) : ?>
                            <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                                <article data-id=<?php echo get_the_ID() ?> class="job-item-js">
                                    <header>
                                        <h3> <?php the_title(); ?> </h3>
                                        <p class="office-name"> Job Based on <?php echo get_post_meta(get_the_ID(), 'office', true); ?></p>
                                    </header>
                                    <hr>
                                    <div class="job-list-description">
                                        <p>
                                            <?php the_excerpt(); ?>
                                        </p>
                                    </div>

                                    <div class="jop-list-meta">
                                        <p><i class="fa-solid fa-briefcase"></i> <?php $terms = wp_get_post_terms($the_query->post->ID, array('department')); ?>
                                            <?php echo $terms ? $terms[0]->name : '---'; ?>
                                        </p>
                                        <p><i class="fa-solid fa-calendar-days"></i>
                                            <?php the_date() ?></p>
                                    </div>
                                </article>
                            <?php endwhile; ?>


                    </div>

                    <?php wp_reset_postdata(); ?>

                <?php endif; ?>

                </div> <!-- jobs-list !-->
                <div class="job-content">
                    <div class="job-content-header">
                        <div class="header-content-top">
                            <div>
                                <h3 id='content-job-title'><?php echo $the_query->posts[0]->post_title ?> </h3>
                                <p class="office-name">Job Based on <span id="office-name"><?php echo get_post_meta($the_query->posts[0]->ID, 'office', true); ?></span></p>
                            </div>
                            <div>
                                <button class="apply-button"><span class="btn-icon"><i class="fa-solid fa-check"></i></span> Apply Now</button>
                            </div>
                        </div>
                        <div class="jop-list-meta">
                            <p><i class="fa-solid fa-briefcase"></i> <span id="department-name">
                                    <?php
                                    $department = wp_get_post_terms($the_query->posts[0]->ID, array('department'));
                                    $department = $department ? $department[0]->name : '';
                                    echo $department;
                                    ?>
                                </span> </p>
                            <p><i class="fa-solid fa-calendar-days"></i>
                                <span id="job-date">

                                    <?php echo $the_query->posts[0]->post_date ?>
                                </span>
                            </p>
                        </div>
                    </div class="job-content-header">
                    <div class="divider"></div>
                    <div class="job-details" id="job-full-content">
                        <?php echo $the_query->posts[0]->post_content ?>
                    </div>
                    <!-- Form  !-->
                    <div class="apply-form">
                        
                        <form id="job-application-form" >
                        <h2>Apply Now</h2>
                        <div id="form-status"></div>

                            <p>

                                <label for="name">Name <span>*</span></label>
                                <input type="text" id="name" name="name" required>
                            </p>
                            <p>
                                <label for="email">Email <span>*</span></label>
                                <input type="email" id="email" name="email" required>
                            </p>
                            <p>
                                <label for="phone">Phone <span>*</span></label>
                                <input type="text" id="phone" name="phone" required>
                            </p>
                                                    
                            <p>
                                <label for="cv">Upload CV </label>
                                <input type="file" id="cv" name="cv">
                            </p>
                            <p>
                                <label for="cover_letter">Cover Letter <span>*</span></label>
                                <textarea id="cover_letter" name="cover_letter" required></textarea>
                            </p>

                            <p>
                                <input type="submit" name="submit_form" value="Send">
                            </p>

                            <input type="hidden" id="job_id" name="job_id" value=<?php echo $the_query->posts[0]->ID ?>>
                        </form>

                    </div>
                </div><!-- End job-content !-->
            </div>

        </main><!-- end wrap !-->
    </div>
<?php


    }

);
