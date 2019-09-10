<?php
// echo"<pre>";
// print_r($_SERVER);
// exit;

// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
//header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

$routes = [
    ['route'=>"register",'type'=>'POST','params'=>[],'handler'=>doRegister],
    ['route'=>"users",'type'=>'GET','params'=>['id'=>'int']],
    ['route'=>"menus",'type'=>'GET','params'=>['id'=>'int']]
];

// Enter your Host, username, password, database below.
// This password should not end up in github (like I just did).
$con = mysqli_connect("localhost", "4443-ip", "uP34Ll6VvIJ3qww9", "4443-ip");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

// See if a route choice is in either the POST or GET array.
if(array_key_exists('route', $_POST)){
    $route = $_POST['route'];
}else if(array_key_exists('route', $_GET)){
    $route = $_GET['route'];
}else{
    $route = false;
}

// No route picked? Show available routes.
if (!$route) {
    show_routes();
}else

$routes[$route]['handler'](33);

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

    $prefix = str_replace('index.php','',$prefix);

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


function doRegister($params){
    echo build_response([1,2,3],true);
}

/**
 * Builds a response to send back to web page.
 * 
 * Params:
 *     $response [array] : data from caller
 *     $success  [bool] : true or false
 *     $error    [string] : Error message if success = false
 * Returns:
 *     null
 *     prints json result to stdout
 */
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
