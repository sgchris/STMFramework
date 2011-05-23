<?php

// load a model(s)
load_model('index');

// define the "params" array (params which will be passed to the view)
$params = array();

// define some basic parameter which will be used in the view file.
// the value of the parameter will be given from the model
$params['data'] = get_data();

// load a view (within a layout), and pass the "params" parameters array
// the "load_view_with_layout" function calls the header and the footer views (defined in the "view" directory)
// the view file will see the parameters as variables.
load_view_with_layout('pages/index', $params);