<?php

function account_controller() {
    global $route, $session, $mysqli, $redis, $user, $settings;

    require_once ("Modules/feed/feed_model.php");
    $feed_class = new Feed($mysqli, $redis, $settings['feed']);

    require_once("Modules/account/account_model.php");
    $account_class = new Accounts($mysqli, $redis, $user, $feed_class);
    
    // Redirect to switch
    if ($route->action == "list" && isset($_SESSION['adminuser'])) {
        $route->action = "switch";
    }
    
    // List linked accounts
    // GET /account/list
    // GET /account/list.json 
    if ($route->action == "list" && $session["write"]) {
        if ($route->format == 'html') {
            return view("Modules/account/account_view.php",array());
        } else {
            $route->format = 'json';
            return $account_class->list($session["userid"]);
        }
    }

    // Add account
    // POST /account/add.json (post body: username, password)
    if ($route->action == "add" && $session["write"]) {
        
        $username = post("username",true);
        $password = post("password",true);
        $email = post("email",true);
        $timezone = post("timezone",true);

        $result = $account_class->add($session["userid"],$username,$password,$email,$timezone);
        $route->format = 'json';
        return $result;
    }

    // Changed linked user password
    // POST /account/password.json (post body: adminuser, linkeduser, password)
    if ($route->action == "password" && $session["write"]) {
        $route->format = 'json';
        $userid = post("userid",true);
        $password = post("password",true);
        return $account_class->change_password($session["userid"],$userid,$password);
    }

    // Edit location
    // POST /account/location.json (post body: location) 
    if ($route->action == "location" && $session["write"]) {
        $route->format = 'json';
        $userid = post("userid",true);
        $location = post("location",true);
        return $account_class->change_location($session["userid"],$userid,$location);
    }

    // Edit email
    // POST /account/email.json (post body: email)
    if ($route->action == "email" && $session["write"]) {
        $route->format = 'json';
        $userid = post("userid",true);
        $email = post("email",true);
        return $account_class->change_email($session["userid"],$userid,$email);
    }

    // Unlink account
    // GET /account/unlink.json (get body: userid)
    if ($route->action == "unlink" && $session["write"]) {
        $route->format = 'json';
        $userid = post("userid",true);
        return $account_class->unlink($session["userid"],$userid);
    }

    // Switch user
    // GET /account/switch.json?userid=123
    if ($route->action == "switch" && $session["write"]) {
        $route->format = 'json';
        $userid = get("userid",false);
        return $account_class->switch($session["userid"],$userid);
    }

    // Set access
    // POST /account/access.json (post body: userid, access)
    if ($route->action == "setaccess" && $session["write"]) {
        $route->format = 'json';
        $userid = post("userid",true);
        $access = post("access",true);
        return $account_class->set_access($session["userid"],$userid,$access);
    }

    // Refresh account stats, disk use and active feeds
    // GET /account/refresh.json
    if ($route->action == "refresh" && $session["write"]) {
        $route->format = 'json';
        return $account_class->refresh_account_stats($session["userid"]);
    }

}
