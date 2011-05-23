<?php
/**
 * Template created by: Gregory Chris
 */

/**
 * Custom "die" function
 * Currently implemented as regular DIE
 *
 * @param <type> $str
 */
function _die($str) {
    echo '
        <h1 style="color: #C11; font-size: 40px; letter-spacing: -2px;">STM CMS Error</h1><hr>
        <h2 style="color: #456; font-size: 20px; letter-spacing: -1px;">'.$str.'</h2>
        <p>
            <pre>';
            debug_print_backtrace();
    echo '  </pre>
        </p>';
    die();
}

/**
 *  Load view file
 *
 * @param string $view_name
 * @param array $params
 */
function load_view($view_name, array $params = array()) {
    if (!empty($params)) {
        extract($params);
    }

    $view_filename = _DIR_ROOT.'/application/view/'.$view_name.'.phtml';
    if (file_exists($view_filename)) {
        require_once $view_filename;
    }
}


/**
 * The function loads a view within the basic layout (view with header, footer)
 * @param <type> $view_name
 * @param array $params
 */
function load_view_with_layout($view_name, array $params = array()) {
    
    $layout_params = get_layout_params();
    $params = array_merge($params, $layout_params);

    if (!empty($params)) {
        extract($params);
    }
    
    $header = _DIR_ROOT.'/application/view/header.phtml';
    if (file_exists($header)) {
        require_once $header;
    }

    $view_filename = _DIR_ROOT.'/application/view/'.$view_name.'.phtml';
    if (file_exists($view_filename)) {
        require_once $view_filename;
    }

    $footer = _DIR_ROOT.'/application/view/footer.phtml';
    if (file_exists($footer)) {
        require_once $footer;
    }
}

/**
 * Load model file. The model file contains functions.
 * @param string $model_name
 */
function load_model($model_name) {
    $model_filename = _DIR_ROOT.'/application/model/'.$model_name.'.php';
    if (file_exists($model_filename)) {
        require_once $model_filename;
    }
}



/**
 * Escape text for HTML output
 * @param string $str
 * @return string
 */
function escape($str) {
    $str = str_replace('\\', '&#92;', $str);
    $str = htmlentities($str, ENT_QUOTES, 'utf-8');
    return $str;
}


/**
 * Function cuts a text to the nearest " " (space) character
 * @param <type> $str
 * @param <type> $number
 * @return <type>
 */
function limit_string($str, $number) {
    if (strlen($str) < $number) {
        return $str;
    }

    // return the cutted text
    return substr($str, 0, strrpos($str, " ", -1 * (strlen($str) - $number + 1))) . ' ...';
}



/**
 * Get parameters for the layout, like
 * the top menu services and products load.
 */
function get_layout_params() {
    $params = array();

    // define basic params for all the pages
    // the params may be override by view params

    return $params;
}





