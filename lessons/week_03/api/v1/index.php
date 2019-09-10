<?php

// required headers
header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require "config.php";
require "vendor/autoload.php";
use \Firebase\JWT\JWT;

// Put all data sources together (POST AND GET)
$requestData = gatherRequestData();

// Associative array of routes with general info about each route.
// Really used to help document our API
$routes = [
    'POST' => [
        ['route'=>"register",'params'=>['data'=>'json'],'handler'=>"doRegister"],
        ['route'=>"user",'params'=>['data'=>'json'],'handler'=>"postUsers"]
    ],
    'GET' => [
        ['route'=>"user",'params'=>['id'=>'string'],'handler'=>"getUsers"],
        ['route'=>"menu",'params'=>['id'=>'int'],'handler'=>"getMenus"],
        ['route'=>"hash",'params'=>['val'=>'string'],'handler'=>"getHash"],
        ['route'=>"auth",'params'=>['email'=>'string','password'=>'string'],'handler'=>"getAuth"]
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
if (!array_key_exists('route',$requestData)) {
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

    global $secret_key; // needed for jwt

    // Build a container for our request data from client 
    $requestData = ['params'=>[]];
    $non_param = ['method','route'];

    // Determine actual method since we are putting GET and POST 
    // data into single container
    if(isset($_SERVER['REQUEST_METHOD'])){
        $requestData['method'] = $_SERVER['REQUEST_METHOD'];
    }

    // Not fully implemented
    // https://www.techiediaries.com/php-jwt-authentication-tutorial/
    $requestData['bearer'] = getBearerToken();

    $decoded = JWT::decode($requestData['bearer'], $secret_key, array('HS256'));

    build_response($decoded);
    
    // Attempt to read raw input from client 
    $_INPUT = json_decode(file_get_contents("php://input"),true);
    
    // If we got the raw input, good otherwise check POST array
    if(is_array($_INPUT)){
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
    
    build_response($requestData);

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

    if(array_key_exists('REQUEST_SCHEME',$_SERVER)){
        $scheme = $_SERVER['REQUEST_SCHEME'];   // gets http or https
    }else{
        $scheme = '';
    }
    if(array_key_exists('HTTP_HOST',$_SERVER)){
        $host = $_SERVER['HTTP_HOST'];          // gets domain name (or ip address)
    }else{
        $host = 'localhost/';
    }
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

    $hash = password_hash($val, PASSWORD_BCRYPT);
    //$hash = md5($val);
    if ($return_response) {
        build_response($hash, true);
    }
    return $hash;
}

function getAuth($data){
    global $conn;

    if(!array_key_exists('email',$data) || !array_key_exists('password',$data)){
        build_response([],false,'Error: getAuth needs email and password!');
    }else{
        $email = $data['email'];
        $password = $data['password'];
    }

    // Create SQL statement
    $sql = "SELECT * FROM `users` where email like '{$email}';";

    $result = $conn->query($sql);

    // User exists because email was correct
    if ($result->num_rows > 0) {
        // output data of each row
        $row = $result->fetch_assoc(); 
        // check password

        if(password_verify($password, $row['password'])){
            build_response([],true);
        }

    }

    build_response([],false,"Error: Failed to authenticate.");
    
}

function buildJWT($data){

    global $secret_key; // included in config file

    if(array_key_exists('HTTP_HOST',$_SERVER)){
        $host = $_SERVER['HTTP_HOST'];          // gets domain name (or ip address)
    }else{
        $host = 'localhost/';
    }

    $issuer_claim = $host;   // this can be the servername
    $audience_claim = "profgriffin-api"; // target audience
    $issuedat_claim = time(); // issued at
    $notbefore_claim = $issuedat_claim + 10; //not before in seconds
    $expire_claim = $issuedat_claim + 60 * 15; // expire time 15 minutes
    $token = array(
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => $data
    );

    return JWT::encode($token, $secret_key);
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
        $hashed = getHash($params,false);

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
    
    $response_data['jwt'] = buildJWT(['token'=>'test']);
    
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


//https://stackoverflow.com/questions/40582161/how-to-properly-use-bearer-tokens
/** 
 * Get header Authorization
 * */
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}
/**
* get access token from header
* */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}