<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 
    $parent_style = 'parent-style'; 
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

register_nav_menus( array(
	'primary' => __( 'Primary Menu', 'air-light' ),
    'secondary' => __( 'Secondary Menu', 'air-light' )
) );

// Begin work on search bar
function wpb_hook_javascript_footer() {
    ?>
        <script>
            let searchButton = document.querySelector(".is-search-submit");
            let searchForm = document.querySelector(".is-search-form");
            let srText = document.querySelector(".is-screen-reader-text");
            let textInput = document.querySelector(".is-search-input");

            srText.classList.add("closed");
            textInput.classList.add("closed");

            // Updates aria label for screen reader
            searchButton.children[0].innerHTML = "Open Search Bar";

            // If focus is no longer on the search input or button, closes the search bar.
            function checkFocus() {
                setTimeout(function(){ 
                    let stillInSearch = (document.activeElement === searchButton) || (document.activeElement === textInput);

                    if(!stillInSearch) {
                        searchForm.classList.remove("open");
                        searchButton.classList.remove("opened");
                        srText.classList.add("closed");
                        textInput.classList.add("closed");

                        searchButton.children[0].innerHTML = "Open Search Bar";
                    }
                 }, 100);
            };

            function openSearchForm(e) {

                if (!searchButton.classList.contains("opened")) {
                    e.preventDefault();

                    searchForm.classList.add("open");
                    searchButton.classList.add("opened");
                    srText.classList.remove("closed");
                    textInput.classList.remove("closed");

                    searchButton.children[0].innerHTML = "Search Button";

                    // Changes user focus to input
                    textInput.focus();

                    // Checks if focus has left the search input or button
                    [searchButton, textInput].forEach(item => {
                        item.addEventListener("blur", checkFocus)
                    });
                } 
            }

            searchButton.addEventListener("click", openSearchForm); 
        </script>
    <?php
}
add_action('wp_footer', 'wpb_hook_javascript_footer');