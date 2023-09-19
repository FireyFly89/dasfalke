(function ($) {
    $site_content = $('.site-content');
    $register_company_wrapper = $('#registerCompany').closest('.form-row.text');
    $styled_radios = $('.job-page__advopt-option .styled-radio');

    $(document).on('click', '.job-page__advopt-option .styled-radio', function() {
        $elem = $(this);
        $styled_radios.removeClass('active');
        $styled_radios.find('input').attr('disabled', true);

        $elem.addClass('active');
        $elem.find('input').attr('disabled', false);
    });

    function refresh_register_page_status() {
        if ($('#radioSeeker').prop('checked') === true) {
            $register_company_wrapper.fadeOut(300);
        } else {
            $register_company_wrapper.fadeIn(300);
        }
    }

    refresh_register_page_status();

    $(document).on('change', '#radioEmployer, #radioSeeker', function () {
        refresh_register_page_status();
    });

    function falke_notice_manager() {
        $error_notice = $('.woocommerce-success, .woocommerce-error, .woocommerce-notice');

        if ($error_notice.length > 0) {
            scrollToElement($error_notice.first());

            setTimeout(function () {
                $error_notice.slideUp(500);
            }, 6000);
        }
    }

    falke_notice_manager();

    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.search);
        return (results !== null) ? results[1] || 0 : false;
    };

    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();

        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    var delete_account_button = $('button[name="delete_account"]');

    $(document).on('change', '#account_delete_confirm', function(e) {
        delete_account_button.prop('disabled', !$(this).prop('checked'));
    });

    var fb_login_btn = $('.login__facebook');
    var login_separator = $('.auth__sep');

    $(document).on('change', '#radioSeeker, #radioEmployer', function (e) {
        var active = $(this).val();

        if (active === 'jobseeker') {
            $('.register-companyname').hide();
            $('.register-fullname').show();
            fb_login_btn.show();
            login_separator.show();
        } else if (active === 'employer') {
            $('.register-fullname').hide();
            $('.register-companyname').show();
            fb_login_btn.hide();
            login_separator.hide();
        }
    });

    $(document).on('submit', '#userRequest', function (e) {
        e.preventDefault();
        handle_user_request('falke_user_request', $(this).serializeObject());
    });

    $(document).on('click', '#logoutUser', function (e) {
        e.preventDefault();
        handle_user_request('falke_user_request_logged', {request: 'logout'});
    });

    if (window.location.search === "?buy_extras") {
        scrollToElement($('.job-page__advopts'));
    }

    $(document).on('submit', '.filter__options-form', function(e){
        var $dropdowns = $(this).find('select');
        var prevent_submit = true;

        $.each($dropdowns, function(key, value) {
            if ($(this).val() !== null && $(this).val().length > 0) {
                prevent_submit = false;
            }
        });

        if (prevent_submit === true) {
            e.preventDefault();
        }
    });

    $(document).on('submit', '.search-field__form', function(e) {
        var $dropdowns = $(this).find('input');
        var prevent_submit = true;

        $.each($dropdowns, function(key, value) {
            if ($(this).val() !== null && $(this).val().length > 0) {
                prevent_submit = false;
            }
        });

        if (prevent_submit === true) {
            e.preventDefault();
        }
    });

    $(document).on('click', '#create_job_alert', function (e) {
        e.preventDefault();

        if (window.location.search.length > 0) {
            $.ajax({
                type: "post",
                url: localized_vars.ajax_url,
                data: {
                    action: 'falke_jobalert_create',
                    nonce: localized_vars.ajax_nonce,
                    data: {
                        query_string: encodeURIComponent(window.location.search),
                    }
                },
            }).done(function (response) {
                if (response.error) {
                    display_ajax_error(response.error);
                } else if (response.success) {
                    display_ajax_error(response.success);
                }

                falke_notice_manager();
            });
        }
    });

    $(document).on('click', '.start-job-apply', function (e) {
        e.preventDefault();
        $job_form = $('.job-application-form');
        $job_form.fadeIn(300, function () {
            scrollToElement($job_form);
        });
    });

    function handle_user_request(action, formData) {
        $.ajax({
            type: "post",
            url: localized_vars.ajax_url,
            data: {
                action: action,
                nonce: localized_vars.ajax_nonce,
                data: formData
            },
        }).done(function (response) {
            if (response.error) {
                display_ajax_error(response.error);
            } else if (response.success) {
                display_ajax_error(response.success);
            }

            falke_notice_manager();

            if (response.redirect) {
                window.location.replace(response.redirect);
            }
        });
    }

    var $spinner = $('.falke-spinner').clone();

    function falke_dropdown_listener(taxonomy, deep_select_labels) {
        $(document).on('change', 'select[name="selected_' + taxonomy + '"]', function () {
            var $elem = $(this);
            var $options = $elem.find('option');
            var create_new_dropdown = true;

            $.each($options, function (key, value) {
                if (key === 0 && $(value).val() === $elem.val()) {
                    create_new_dropdown = false;
                }
            });

            $elem.closest('.form-row.select').nextAll('.form-row.select').find('select[name="selected_' + taxonomy + '"]').closest('.form-row.select').remove();

            if (create_new_dropdown === false) {
                return false;
            }

            var $selects = $('select[name="selected_' + taxonomy + '"]');

            if (parseInt($elem.val()) === -1 || $('select[name="selected_' + taxonomy + '"]').length >= deep_select_labels.length) {
                return false;
            }

            $elem.nextAll().remove();
            $spinner.insertAfter($elem);
            $spinner.fadeIn(300);

            make_dropdown({
                term_id: $elem.val(),
                taxonomy: taxonomy,
                label: deep_select_labels[$selects.length],
                extra_classes: $elem.attr('class'),
            });
        });
    }

    falke_dropdown_listener('locations', ['Location', 'City', 'Address']);
    falke_dropdown_listener('professions', ['Main category', 'Category', 'Sub category']);

    function make_dropdown(formData) {
        $.ajax({
            type: "post",
            url: localized_vars.ajax_url,
            data: {
                action: localized_vars.dropdown_action,
                data: formData
            },
        }).done(function (response) {
            if (response && response.length > 0) {
                $spinner.fadeOut(100, function () {
                    var $selects = $('select[name="selected_' + formData.taxonomy + '"]');
                    $(response).insertAfter($selects.last().closest('.form-row.select'));
                });
            }
        });
    }

    function display_ajax_error($error) {
        $error_wrapper = $site_content.find('.woocommerce-error, .woocommerce-warning, .woocommerce-success');

        if ($error_wrapper.length > 0) {
            $error_wrapper.fadeOut(300).remove();
        }

        $site_content.prepend($error);
    }

    function scrollToElement(element, vertical_modifier) {
        vertical_modifier = typeof vertical_modifier !== 'undefined' ? vertical_modifier : 0;

        var deferred = jQuery.Deferred(),
            elemPos = element.offset().top,
            finalOffset = elemPos + vertical_modifier,
            scrollTop = jQuery(window).scrollTop(),
            animTiming = 400;

        if (scrollTop < finalOffset) {
            animTiming = Math.floor((animTiming + ((finalOffset - scrollTop) / 10)));
        } else {
            animTiming = Math.floor((animTiming + ((scrollTop - finalOffset) / 10)));
        }

        if (elemPos > scrollTop || (elemPos + element.outerHeight()) < scrollTop) {
            jQuery("html, body").stop().animate({scrollTop: (finalOffset < 0 ? 0 : finalOffset)}, animTiming, function () {
                deferred.resolve();
            });
        } else {
            deferred.resolve();
        }

        return deferred;
    }
})(jQuery);
