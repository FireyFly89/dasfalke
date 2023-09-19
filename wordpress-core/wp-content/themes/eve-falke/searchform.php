<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
  <div class="search-form__wrp">
    <input type="search" class="search-form__input br"
      placeholder="<?php echo esc_attr_x( 'Search…', 'eve' ) ?>"
      value="<?php echo get_search_query() ?>" name="s"
      title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
    <button class="search-form__submit" type="submit"><svg class="i i-search" width="18" height="18" title="<?php get_bloginfo('name') ?>"><use xlink="http://www.w3.org/1999/xlink" xlink:href="#i-search" href="#i-search"></use></svg></button>
  </div>
</form>