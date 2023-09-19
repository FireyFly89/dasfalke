(function ($) {
    $employment_city_and_label = $("#employment_city, label[for=employment_city]");
    $employment_address_and_label = $("#employment_address, label[for=employment_address]");
    $employment_city = $("#employment_city");
    $employment_address = $("#employment_address");
    $employment_payment_extent = $("input[name=employment_payment_extent]");
    $apply_type_site_field = $('input[name=apply_type_site_field]');
    $apply_employment = $('input[name=employment_email]');
    $job_info_elems = $('.job-info');
    $apply_type = $('#apply_type');
    $apply_type_site = $('#apply_type_site');

    if ($employment_payment_extent.length > 0 && $employment_payment_extent.val().length > 0) {
        $employment_payment_extent.closest('.form-row').fadeIn(100);
    }

    if ($apply_type_site_field.length > 0 && $apply_type_site_field.val().length > 0) {
        $apply_type_site_field.closest('.form-row').fadeIn(100);
    }

    if ($apply_type.length > 0 && $apply_type.prop('checked')) {
        $('#employment_email').closest('.form-row').fadeIn(100);
        $apply_type_site_field.closest('.form-row').fadeOut(100);
    } else if ($apply_type_site.length > 0 && $apply_type_site.prop('checked')) {
        $('#employment_email').closest('.form-row').fadeOut(100);
        $apply_type_site_field.closest('.form-row').fadeIn(100);
    }

    if ($employment_city.length > 0 && $employment_city.val()) {
        $employment_city_and_label.closest('.form-row').fadeIn(100);
    }

    if ($employment_address.length > 0 && $employment_address.val()) {
        $employment_address_and_label.closest('.form-row').fadeIn(100);
    }

    $(document).on('change', '#employment_province', function (e) {
        if ($(this).val().length > 0) {
            $employment_city_and_label.closest('.form-row').fadeIn(300);
        }
    });

    $(document).on('change', '#employment_city', function (e) {
        if ($(this).val().length > 0) {
            $employment_address_and_label.closest('.form-row').fadeIn(300);
        }
    });

    $(document).on('change', '#employment_payment', function (e) {
        $employment_payment_extent.closest('.form-row').fadeIn(300);
    });

    $(document).on('change', 'input[name=apply_type]', function (e) {
        if ($(this).attr('id') === 'apply_type_site') {
            $apply_employment.closest('.form-row').fadeOut(100, function () {
                $apply_type_site_field.closest('.form-row').fadeIn(300);
            });
        } else {
            $apply_type_site_field.closest('.form-row').fadeOut(100, function () {
                $apply_employment.closest('.form-row').fadeIn(300);
            });
        }
    });
})(jQuery);
