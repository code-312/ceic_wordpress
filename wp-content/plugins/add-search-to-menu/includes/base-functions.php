<?php
/*
 * Declares Global Functions
 */

/* Checks whether current request is a JSON request, or is expecting a JSON response. */
function ivory_search_is_json_request() {

    if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'application/json' ) ) {
        return true;
    }

    if ( isset( $_SERVER['CONTENT_TYPE'] ) && 'application/json' === $_SERVER['CONTENT_TYPE'] ) {
        return true;
    }

    return false;

}