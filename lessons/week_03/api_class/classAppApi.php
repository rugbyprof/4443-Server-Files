<?php

require_once "config.php";
require_once "classApi.php";

class AppApi extends API
{
    public function __construct($host, $user, $password, $db, $secret)
    {
        parent::__construct($host, $user, $password, $db, $secret);
    }

    /**
     * Get a hash for a password
     *
     * @param [string] $val
     * @param boolean $return_response
     * @return json response or hashed password
     */
    public function getHash($params, $return_response = true)
    {
        //$this->send_response($params);
        if (!array_key_exists('val', $params)) {
            $this->send_response([], false, 'getHash needs a "val" parameter!');
        } else {
            $val = $params['val'];
        }

        $hash = password_hash($val, PASSWORD_BCRYPT);
        //$hash = md5($val);
        if ($return_response) {
            $this->send_response($hash, true);
        }
        return $hash;
    }

    /**
     * get authorization for a user
     *
     * @param [array] $data : needs to contain email and password
     * @return response with succes:true
     */
    public function getAuth($data)
    {

        if (!array_key_exists('email', $data) || !array_key_exists('password', $data)) {
            $this->send_response([], false, 'Error: getAuth needs email and password!');
        } else {
            $email = $data['email'];
            $password = $data['password'];
        }

        // Create SQL statement
        $sql = "SELECT * FROM `users` where email like '{$email}';";

        $result = $this->conn->query($sql);

        // User exists because email was correct
        if ($result->num_rows > 0) {
            // output data of each row
            $row = $result->fetch_assoc();
            // check password

            // password_verify is need to check a password created by password_hash
            if (password_verify($password, $row['password'])) {
                $this->send_response([], true);
            }

        }

        $this->send_response([], false, "Error: Failed to authenticate.");

    }

    /**
     * Registers a person by adding them to a users table in the database
     *
     * @param [array] $data : associative array with user info
     * @return response with success or fail.
     */
    public function doRegister($data)
    {
        $this->send_response($data);
        // Lets us access the global connection at the top

        $required = ['first-name', 'last-name', 'email', 'city', 'age', 'state', 'pwd1', 'pwd2'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                $this->send_response($data, false, "Registration field: {$field} is required! ");
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
        if ($pwd1 != $pwd2) {
            $this->send_response([], false, "Passwords do not match!!");
        }

        $hashed = password_hash($pwd1);

        // Create SQL statement
        $sql = "INSERT INTO `users` (`fname`, `lname`, `email`, `city`, `age`, `state`,`password`)
        VALUES ('{$fname}', '{$lname}', '{$email}', '{$city}', '{$age}', '{$state}', '{$hashed}');";

        // Run the SQL query
        if ($this->conn->query($sql) === true) {
            $this->send_response([], true);
        } else {
            $this->send_response([], false, $this->conn->error);
        }
    }

}
