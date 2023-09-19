(function ($) {
    var min_quantity = parseInt(bundle_manager_vars.falke_job_posts_min_quantity);
    var max_quantity = parseInt(bundle_manager_vars.falke_job_posts_max_quantity);
    var quantity_discounts = bundle_manager_vars.falke_quantity_discounts;
    var feature_product_price = parseInt(bundle_manager_vars.feature_product_price);
    var $bundle_image_elem = $('.price_ill-img');
    var $price_holder = $('.js-price').find('span');
    var $quantity_holder = $('.product-quantity');
    var switch_off_count = 0;
    var current_quantity = 1;

    function set_discount_price() {
        var extra_multiplier = 0;

        if (switch_off_count === 0) {
            extra_multiplier = feature_product_price * 2;
        } else if (switch_off_count === 1) {
            extra_multiplier = feature_product_price;
        }

        var extras = extra_multiplier * parseInt(current_quantity);
        var final_price = parseInt(quantity_discounts[current_quantity]) + extras;
        $price_holder.html(final_price);
    }
    init_quantity();
    trigger_switches();
    set_discount_price();

    function init_quantity() {
        current_quantity = parseInt($quantity_holder.val());
    }

    $(document).on('click', '.quantity-minus, .quantity-plus', function (e) {
        var operation = e.currentTarget.className.search('quantity-plus') > 0 ? 1 : -1;
        var set_quantity = (parseInt($quantity_holder.val()) + operation);
        var $product_url = $('.product-url');
        var parser = document.createElement('a');
        parser.href = $product_url.attr('href');

        if (set_quantity >= min_quantity && set_quantity <= max_quantity) {
            current_quantity = set_quantity;
            set_discount_price();

            $quantity_holder.val(set_quantity);
            $product_url.attr('href', parser.search.replace(/=[0-9]{1,3}$/, "=" + set_quantity));
            $('.quantity-count').html(set_quantity);
        }
    });

    $(document).on('change', '.product-quantity', function (e) {
        var $this = $(this);
        var set_quantity = parseInt($this.val());
        var $product_url = $('.product-url');
        var parser = document.createElement('a');
        parser.href = $product_url.attr('href');

        if (set_quantity <= min_quantity) {
            set_quantity = min_quantity;
        } else if (set_quantity >= max_quantity) {
            set_quantity = max_quantity;
        }

        current_quantity = set_quantity;
        set_discount_price();

        $product_url.attr('href', parser.search.replace(/=[0-9]{1,3}$/, "=" + set_quantity));
        $this.val(set_quantity);
        $('.quantity-count').html(set_quantity);
    });

    $(document).on('change', '.onoffswitch-checkbox', function (e) {
        trigger_switches();
        set_discount_price();
    });

    function trigger_switches() {
        var $switches = $('.onoffswitch-checkbox');
        switch_off_count = 0;

        $.each($switches, function (key, value) {
            var $elem = $(value);

            if (!$elem.prop('checked')) {
                if ($elem.attr('id') === 'feature-bundle-pre-sort') {
                    $bundle_image_elem.attr('src', $bundle_image_elem.data('half-standard-pack-img'));
                } else if ($elem.attr('id') === 'feature-bundle-highlight') {
                    $('#feature-bundle-homepage').prop('disabled', true);
                    $bundle_image_elem.attr('src', $bundle_image_elem.data('half-pack-img'));
                }

                switch_off_count++;
            } else if ($elem.attr('id') === 'feature-bundle-highlight') {
                $('#feature-bundle-homepage').prop('disabled', false);
            }
        });

        if (switch_off_count >= 2) {
            $bundle_image_elem.attr('src', $bundle_image_elem.data('standard-pack-img'));
        } else if (switch_off_count <= 0) {
            $bundle_image_elem.attr('src', $bundle_image_elem.data('full-pack-img'));
        }
    }
})(jQuery);
