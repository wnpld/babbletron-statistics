<?php
session_start();
require "../config.php";

try {
    $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
} catch (mysqli_sql_exception $e) {
    echo "<html><head><title>Configuration Problem</title></head><body>";
    echo "<h1>Configuration problem</h1>";
    echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
    echo "</p></body></html>";
    exit();
}

//Any error will send the user to the login page.
//Since the inputs are validated in the form, it's assumed that
//any invalid inputs are the result of foul play.
if (isset($_REQUEST['administrator'])) {
    #New administrator login
    $error = false;
    if (isset($_REQUEST['username'])) {
        preg_match('/^[A-Za-z0-9]{2,25}$/', $_REQUEST['username'], $matches);
        if ($matches[0]) {
            $username = strtolower($_REQUEST['username']);
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['firstname'])) {
        preg_match('/^[A-Za-z \-\'.]{2,50}$/', $_REQUEST['firstname'], $matches);
        if ($matches[0]) {
            $firstname = $_REQUEST['firstname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['lastname'])) {
        preg_match('/^[A-Za-z \-\'.]{2,50}$/', $_REQUEST['lastname'], $matches);
        if ($matches[0]) {
            $lastname = $_REQUEST['lastname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['pwhash'])) {
        if (isset($_REQUEST['hashalgo'])) {
            $algorithm = $_REQUEST['hashalgo'];
        } else {
            //Should have something in it, but 
            //if there's nothing assume none
            $algorithm = "none";
        }
        //In an http context getting a hash in javascript isn't possible
        //so it will be hashed twice here: once on its own and then with salt
        if ($algorithm == "none") {
            $password = hash('sha256', $_REQUEST['pwhash']);
        } else {
            $password = $_REQUEST['pwhash'];
        }
        $salt = saltmachine();
        $password .= $salt;
        $pwhash = hash('sha256', $password);
    } else {
        $error = true;
    }

    if ($error == false) {
        //Error being false means everything looks good.  Add the user.
        try {
            $query = $db->prepare("INSERT INTO `Users` (`UserName`, `LastName`, `FirstName`, `Password`, `Salt`, `UserRole`) VALUES (?, ?, ?, UNHEX(?), ?, 'Admin')");
            $query->bind_param("sssss", $username, $lastname, $firstname, $pwhash, $salt);
            $query->execute();
         } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>Error</title></head><body>";
            echo "<p>Error adding user: " . $e->getMessage();
            echo "</p></body></html>";
            $db->close();
            exit();
        }
        $query->close();
        $db->close();
        if (isset($_REQUEST["mainlibset"])) {
            //The "mainlibrary" variable being set means that a library already exists
            //Somehow the admin(s) had been deleted and needed to be reestablished.
            header("Location: $protocol://$server$webdir/login.php");
            exit();
        } else {
            header("Location: $protocol://$server$webdir/login.php?destination=nomain");
            exit();
        }
    } else {
        $db->close();
        header("Location: $protocol://$server$webdir/index.php");
        exit();
    }
} else if (isset($_REQUEST['mainlibrary'])) {
    #New main library
    $error = false;
    if (isset($_REQUEST['libraryname'])) {
        preg_match('/^[A-Za-z0-9]{4,100}$/', $_REQUEST['libraryname'], $matches);
        if ($matches[0]) {
            $libraryname = $_REQUEST['libraryname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['address'])) {
        preg_match('/^[A-Za-z0-9 #\'\-.]{5,150}$/', $_REQUEST['address'], $matches);
        if ($matches[0]) {
            $address = $_REQUEST['address'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['city'])) {
        preg_match('/^[A-Za-z.\' \-]{2,75}$/', $_REQUEST['city'], $matches);
        if ($matches[0]) {
            $city = $_REQUEST['city'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['fystart'])) {
        preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)$/', $_REQUEST['fystart'], $matches);
        if ($matches[0]) {
            $fystart = $_REQUEST['fystart'];
        } else {
            $error = true;
        }
    }

    if ($error == false) {
        //False error means everything's fine
        try{
            $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`, `FYMonth`) VALUES (?, ?, ?, 0, ?)');
            $query->bind_param('ssss', $libraryname, $address, $city, $fystart);
            $query->execute();
         } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>Error</title></head><body>";
            echo "<p>Error adding library info: " . $e->getMessage();
            echo "</p></body></html>";
            $db->close();
            exit();
        }
        $query->close();
        $db->close();
        header("Location: $protocol://$server$webdir/index.php");
        exit();
    } else {
        $db->close();
        header("Location: $protocol://$server$webdir/index.php");
        exit();
    }
}

function saltmachine() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,./<>?!^&*()-_=+';
    $randomstring = '';
    for ($x = 0; $x < 100; $x++) {
        $randomstring .= $characters[random_int(0, strlen($characters) -1)];
    }
    return $randomstring;
}
?>