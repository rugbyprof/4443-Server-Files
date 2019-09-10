<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
//header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

require('config.php');


// Put all data sources together (POST AND GET)
$requestData = gatherRequestData();

build_response($requestData);

// Associative array of routes with general info about each route.
// Really used to help document our API
$routes = [
    'POST' => [
        ['route'=>"register",'params'=>['data'=>'json'],'handler'=>doRegister],
        ['route'=>"user",'params'=>['data'=>'json'],'handler'=>postUsers],
    ],
    'GET' => [
        ['route'=>"user",'params'=>['id'=>'string'],'handler'=>getUsers],
        ['route'=>"menu",'params'=>['id'=>'int'],'handler'=>getMenus],
        ['route'=>"hash",'params'=>['val'=>'string'],'handler'=>getHash]
    ]
];

// Enter your Host, username, password, database below.
// This password should not end up in github (like I just did).
$conn = mysqli_connect($auth['host'], $auth['user'], $auth['password'], $auth['db']);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

// No route picked? Show available routes.
if (!$requestData['route']) {
    show_routes();
    exit;
}

// Doing a GET or a POST
$method = $requestData['method'];

// What route am I calling
$route = $requestData['route'];

// What params to send to handler
$params = $requestData['params'];

// What function is handling this route
$handler = getHandler($method,$route);

// Call handler with params from request
$handler($params);


/**
 * Looks through our routes array and finds the correct "handler" or function to call
 *
 * @param [string] $method : GET or POST
 * @param [string] $route_name : name of route :) 
 * @param [array] $params
 * @return array ['handler' => handler_name , 'params' => array of values one for every param needed by handler]
 */
function getHandler($method,$route_name){
    global $routes;

    $handler_name = '';

    foreach($routes[$method] as $route){
        if($route['route'] == $route_name){
            return $route['handler'];
        }
    }

    return null;
}


/**
 * Get all request data from get or posted sources
 *
 * @return array $reqestData
 */
function gatherRequestData(){
    // Build a container for our request data from client 
    $requestData = ['params'=>[]];
    $non_param = ['method','route'];

    // Determine actual method since we are putting GET and POST 
    // data into single container
    $requestData['method'] = $_SERVER['REQUEST_METHOD'];


    // Attempt to read raw input from client 
    $_INPUT = json_decode(file_get_contents("php://input"),true);
    
    // If we got the raw input, good otherwise check POST array
    if(sizeof($_INPUT) > 0){
        foreach($_INPUT as $key => $value){
            if(!in_array($key,$non_param)){
                $requestData['params'][$key] = $value;
            }else{
                $requestData[$key] = $value;
            }
        }
    }else{
        foreach($_POST as $key => $value){
            if(!in_array($key,$non_param)){
                $requestData['params'][$key] = $value;
            }else{
                $requestData[$key] = $value;
            }
        } 
    }

    // Throw in any URL params for good measure
    foreach($_GET as $key => $value){
        if(!in_array($key,$non_param)){
            $requestData['params'][$key] = $value;
        }else{
            $requestData[$key] = $value;
        }
    }
    //build_response($requestData);


    $requestData['auth'] = $_SERVER['PHP_AUTH_DIGEST'];

    // Return our data
    return $requestData;
}

/**
 * Dumps the routes out to the browser simply to help programmer see what is available.
 *
 * @param [string] $inroute : a route name passed in if there wasnt a route to match.
 * @return prints response (json)
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
    foreach(['GET','POST'] as $rtype){
        $response[$rtype] = [];
        foreach ($routes[$rtype] as $r) {
            $temp = [];
            foreach ($r as $k => $v) {
                if ($k == 'route') {
                    $v = $prefix.$v;
                }
                $temp[$k] = $v;
            }
            $response[$rtype][] = $temp;
        }
    }
    if($inroute){
        echo build_response($response,false,"Error: Route:{$inroute} does not exist!");
    }else{
        echo build_response($response);
    }
    exit;
}

/**
 * Get a hash for a password
 *
 * @param [string] $val
 * @param boolean $return_response
 * @return json response or hashed password
 */
function getHash($params,$return_response=true){
    //build_response($params);
    if(!array_key_exists('val',$params)){
        build_response([],false,'getHash needs a "val" parameter!');
    }else{
        $val = $params['val'];
    }

    //$hash = password_hash($val, PASSWORD_DEFAULT);
    $hash = md5($val);
    if ($return_response) {
        build_response($hash, true);
    }
    return $hash;
}

function getUser(){

}

/**
 * Add new users to database
 *
 * @param [array] $data : of users
 * @return response
 */
function postUsers($data){
    global $conn;

    $users = $data['users'];

    foreach($users as $user){

        //build_response(print_r($user,true));

        $hashed = md5($user['password']);

        $fname = $user['first_name'];
        $lname = $user['last_name'];
        $email = $user['email'];
        $city = $user['city'];
        $age = $user['age'];
        $state = $user['state'];

        
        // Create SQL statement
        $sql = "INSERT INTO `users` (`fname`, `lname`, `email`, `city`, `age`, `state`,`password`) 
        VALUES ('{$fname}', '{$lname}', '{$email}', '{$city}', '{$age}', '{$state}', '{$hashed}');";
        

        // Run the SQL query
        if (!$conn->query($sql) === TRUE) {
            build_response([],false,$conn->error);
        } 


    }
    build_response(["users:"=>sizeof($users)],true);
    
}

/**
 * Registers a person by adding them to a users table in the database
 *
 * @param [array] $data : associative array with user info
 * @return response with success or fail.
 */
function doRegister($data){
    build_response($data);
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

    // Do passwords match
    if($pwd1 != $pwd2){
        build_response([],false,"Passwords do not match!!");
    }

    $hashed = password_hash($pwd1);

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
  * Builds a response to send back to requestor.
  *
  * @param [type] $response : data from calling entity (function probably)
  * @param boolean $success : true or false
  * @param string $error : Error message if success = false
  * @return prints json result to stdout
  */
function build_response($response,$success=true,$error="")
{
    $response_data = [];

    if ($success) {
        if(!is_array($response)){
            $response = [$response];   
        }
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

/**
 * Helper log function that writes contents to "log.log"
 * 
 * NOTICE!! => make sure `log.log` is writable by server (chmod 777 log.log) or (chown www-data:www-data log.log)
 *
 * @param [any] $stuff
 * @return void
 */
function logg($stuff){
    file_put_contents('log.log',print_R($stuff),true);
}
