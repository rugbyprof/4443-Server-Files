<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
//header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

require('authenticate.php');

// Associative array of routes with general info about each route.
// Really used to help document our API
$routes = [
    ['route'=>"register",'type'=>'POST','params'=>[]],
    ['login'=>"register",'type'=>'GET','params'=>['email','password']],
    ['route'=>"users",'type'=>'GET','params'=>['id'=>'int']],
    ['route'=>"menus",'type'=>'GET','params'=>['id'=>'int']]
];

// Enter your Host, username, password, database below.
// This password should not end up in github (like I just did).
$conn = mysqli_connect($auth['host'], $auth['user'], $auth['password'], $auth['db']);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

// Initialize route to nothing.
$route = false;

// See if a route choice is in either the POST or GET array.
if(array_key_exists('route', $_POST)){
    $route = $_POST['route'];
    unset($_POST['route']);
}else if(array_key_exists('route', $_GET)){
    $route = $_GET['route'];
}else{
    $route = false;
}

// No route picked? Show available routes.
if (!$route) {
    show_routes();
    exit;
}

// Choose which route to run
switch($route){
    case 'register' : doRegister($_POST);
        break;
    case 'login' : doLogin($_POST);
        break;
    case 'menus' : getMenus($id);
        break;
    case 'users' : getUsers($id);
        break;
    default: show_routes($route);
}

/**
 * Dumps the routes out to the browser simply to help programmer see what is available.
 * Params:
 *     $route [string] : a route name passed in if there wasnt a route to match.
 * Returns:
 *     prints response (json)
 */
function show_routes($inroute=null)
{
    global $routes;

    $scheme = $_SERVER['REQUEST_SCHEME'];   // gets http or https
    $host = $_SERVER['HTTP_HOST'];          // gets domain name (or ip address)
    $script = $_SERVER['PHP_SELF'];         // gets name of 'this' file

    $prefix = "{$scheme}://{$host}{$script}"; //http://terrywgriffin.com/api.php

    $prefix = str_replace('index.php','',$prefix);

    $response = [];

    $response['route'] = $inroute;
    $i = 0;
    foreach ($routes as $r) {
        $temp = [];
        foreach ($r as $k => $v) {
            if ($k == 'route') {
                $v = $prefix.$v;
            }
            $temp[$k] = $v;
        }
        $response[] = $temp;
    }
    if($inroute){
        echo build_response($response,false,"Error: Route:{$inroute} does not exist!");
    }else{
        echo build_response($response);
    }
    exit;
}

function doLogin($data){
    global $conn;

    $email = $data['email'];
    $password = $data['password'];

    // Create SQL statement
    $sql = "SELECT * FROM `users` where email like '{$email}';";

    $result = $conn->query($sql);

    // User exists because email was correct
    if ($result->num_rows > 0) {
        // output data of each row
        $row = $result->fetch_assoc(); 
        // check password

        if(md5($password) == $row['password']){
            build_response([],true);
        }

    }
    build_response([],false,"Login incorrect!");
}

/**
 * Registers a person by adding them to a users table in the database
 * Params:
 *     $data [array] : associative array with user info
 * Returns:
 *     response with success or fail.
 */
function doRegister($data){

    // Lets us access the global connection at the top
    global $conn;

    $required = ['first-name','last-name','email','city','age','state','pwd1','pwd2'];

    foreach($required as $field){
        if(!array_key_exists($field,$data)){
            build_response($data,false,"Registration field: {$field} is required! ");
        }
    }
    

    // Pull names out of array (only for readability)
    $fname = $data['first-name'];
    $lname = $data['last-name'];
    $email = $data['email'];
    $city = $data['city'];
    $age = $data['age'];
    $state = $data['state'];
    $pwd1 = $data['pwd1'];
    $pwd2 = $data['pwd2'];

    if($pwd1 != $pwd2){
        build_response([],false,"Passwords do not match!!");
    }

    $hashed = password_hash($pwd1, PASSWORD_DEFAULT);

    // Create SQL statement
    $sql = "INSERT INTO `users` (`fname`, `lname`, `email`, `city`, `age`, `state`,`password`) 
    VALUES ('{$fname}', '{$lname}', '{$email}', '{$city}', '{$age}', '{$state}', '{$hashed}');";

    // Run the SQL query
    if ($conn->query($sql) === TRUE) {
        build_response([],true);
    } else {
        build_response([],false,$conn->error);
    }
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

function logg($stuff){
    file_put_contents('log.log',print_R($stuff),true);
}
