console.log("this is my scrpit ");

jQuery(document).ready(function ($) {
  // get all jobs

  $(".apply-button").click(function () {
    if ($(".job-details").css("display") === "none") {
      $(this).html(
        '<span class="btn-icon"><i class="fa-solid fa-check"></i></span> Apply Now'
      );
      $(".job-details").show();
      $("#job-application-form").hide();
      $('#form-status').hide(); 

    } else {
      $(this).html('<i class="fa-solid fa-arrow-left"></i> Back to details');

      $(".job-details").hide();
      $("#job-application-form").show();
    }
  });

  // get one job by id and change details box

  $(".jobs-list article").click(function () {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "get_job_details_ajax_action",
        id: $(this).data("id"),
      },

      success: function (response) {
        if (response) {
          $("#office-name").text(response.office);
          $("#department-name").text(response.department);
          $("#content-job-title").text(response.post_title);
          $("#job-date").text(response.post_date);
          $("#job-full-content").html(response.post_content);

          $('.apply-button').html('<span class="btn-icon"><i class="fa-solid fa-check"></i></span> Apply Now');
          $('.apply-button').html();
          $(".job-details").show();
          $("#job-application-form").hide();
          $('#form-status').hide(); 

        }
      },
    });
  });

  // set up event delegation for job items
  $("#jop-items").on("click", ".job-item-js", function (e) {
    e.preventDefault();
    var jobId = $(this).data("id");
    getJobDetails(jobId);
  });

  // get one job by id and change details box
  $(".search input[type='search']").on("change", function () {
    var keyword = $(this).val();
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "job_filter_by_keyword_ajax_action",
        keyword: keyword,
      },
      success: function (response) {
        console.log(response);

        if (response.error !== undefined) {
          $(".jobs-count").text(" ");
          $("#jop-items").html("<h4>No results </h4>");
        } else {
          $(".jobs-count").text(response.length);
          var element = $("#jop-items");
          $(element).empty();

          response.forEach((post) => {
            $(build_job_item(post)).appendTo(element);
          });

          firstPost = response[0];
          $("#office-name").text(firstPost.office);
          $("#department-name").text(firstPost.department);
          $("#content-job-title").text(firstPost.post_title);
          $("#job-date").text(firstPost.post_date);
          $("#job-full-content").html(firstPost.post_content);

          $('.apply-button').html('<span class="btn-icon"><i class="fa-solid fa-check"></i></span> Apply Now');
          $('.apply-button').html();
          $(".job-details").show();
          $("#job-application-form").hide();
        }
      },
    });
  });

  $(".department-filter").on("change", function () {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "job_filter_by_department_ajax_action",
        term_id: this.value,
      },
      success: function (response) {
        if (response.error !== undefined) {
          $(".jobs-count").text(" ");
          $("#jop-items").html("<h4>No results  </h4>");
        } else {
          $(".jobs-count").text(response.length);
          var element = $("#jop-items");
          $(element).empty();

          response.forEach((post) => {
            $(build_job_item(post)).appendTo(element);
          });

          firstPost = response[0];
          $("#office-name").text(firstPost.office);
          $("#department-name").text(firstPost.department);
          $("#content-job-title").text(firstPost.post_title);
          $("#job-date").text(firstPost.post_date);
          $("#job-full-content").html(firstPost.post_content);

          $('.apply-button').html('<span class="btn-icon"><i class="fa-solid fa-check"></i></span> Apply Now');
          $(".job-details").show();
          $("#job-application-form").hide();
         }
      },
    });
  });

  jQuery(document).ready(function($) {
    $('#job-application-form').on('submit', function(e) {
        e.preventDefault(); 
        var formData = new FormData(this);
        formData.append('action','handle_form_submission')

        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                $('#form-status').html(response.message); 
                $('#form-status').addClass('success'); 
                $('#job-application-form').trigger("reset");

            } ,
            
            error: function(response) {
                console.log(response);
                $('#form-status').html(response.message); 
                $('#form-status').addClass('error'); 
            } ,
        });

    });
});




  // Helper functions

  function build_job_item(post) {
    return `
    <article data-id=${post.ID} class="job-item-js">
    <header>
        <h3> ${post.post_title} </h3>
        <p class="office-name"> Job Based on <${post.office} </p>
    </header>
    <hr>
    <div class="job-list-description">
        <p>${post.post_excerpt}</p>
    </div>

    <div class="jop-list-meta">
        <p><i class="fa-solid fa-briefcase"></i> ${post.department}
        </p>
        <p><i class="fa-solid fa-calendar-days"></i>
        ${post.post_date}</p>
    </div>
</article> 
`;
  }

  function getJobDetails(jobId) {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "get_job_details_ajax_action",
        id: jobId,
      },
      success: function (response) {
        if (response) {
          $("#office-name").text(response.office);
          $("#department-name").text(response.department);
          $("#content-job-title").text(response.post_title);
          $("#job-date").text(response.post_date);
          $("#job-full-content").html(response.post_content);
          $("input#job_id").val(jobId) ; 
          $("#job-application-form").trigger("reset");
        }
      },
    });
  }
});


