<?php
// echo"<pre>";
// print_r($_SERVER);
// exit;

// required headers
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: access");
//header("Access-Control-Allow-Methods: GET");
//header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// php array to show what routes exist
$routes = [
    ['route'=>"/register",'type'=>'POST','params'=>[]],
    ['route'=>"/users",'type'=>'GET','params'=>['id'=>'int']],
    ['route'=>"/menus",'type'=>'GET','params'=>['id'=>'int']]
];

// Enter your Host, username, password, database below.
// This password should not end up in github (like I just did).
$con = mysqli_connect("localhost", "4443-ip", "uP34Ll6VvIJ3qww90", "4443-ip");
if (mysqli_connect_errno()) {
    //echo "Failed to connect to MySQL: " . ;
    echo build_response($response,false,mysqli_connect_error());
}

// Check to see if command exists
if (!array_key_exists('route', $_POST)) {
    // if not dump available routes
    show_routes();
}

// print_r($_POST);
// print_r($_GET);

/**
 * Dumps the routes out to the browser simply to help programmer see what is available.
 * Params:
 *     None
 * Returns:
 *     prints response (json)
 */
function show_routes()
{
    global $routes;

    $scheme = $_SERVER['REQUEST_SCHEME'];   // gets http or https
    $host = $_SERVER['HTTP_HOST'];          // gets domain name (or ip address)
    $script = $_SERVER['PHP_SELF'];         // gets name of 'this' file

    $prefix = "{$scheme}://{$host}{$script}"; //http://terrywgriffin.com/api.php

    $response = [];
    $i = 0;
    foreach ($routes as $route) {
        $temp = [];
        foreach ($route as $k => $v) {
            if ($k == 'route') {
                $v = $prefix.$v;
            }
            $temp[$k] = $v;
        }
        $response[] = $temp;
    }
    echo build_response($response);
}

function handle_request($array)
{
}

function build_response($response,$success=true,$error="")
{
    $response_data = [];

    if ($success) {
        $count = sizeof($response);
        $response_data['count'] = $count;
    }
    if ($error){
        $response_data['error'] = $error;
    }
    
    $response_data['success'] = $success;
    $response_data['data'] = $response;
    echo json_encode($response_data);
    exit;
}
