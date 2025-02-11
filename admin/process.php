<?php
require "../config.php";

try {
    $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
} catch (mysqli_sql_exception $e) {
    echo "Configuration problem";
    echo "It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
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
        if ($matches[1]) {
            $username = strtolower($_REQUEST['username']);
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['firstname'])) {
        preg_match('/^[A-Za-z \-\'.]{2,50}$/', $_REQUEST['firstname'], $matches);
        if ($matches[1]) {
            $firstname = $_REQUEST['firstname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['lastname'])) {
        preg_match('/^[A-Za-z \-\'.]{2,50}$/', $_REQUEST['lastname'], $matches);
        if ($matches[1]) {
            $lastname = $_REQUEST['lastname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['pwhash'])) {
        $salt = saltmachine();
        $password = $_REQUEST['pwhash'] . $salt;
        $pwhash = hash('sha256', $password);
    } else {
        $error = true;
    }

    if ($error == false) {
        //Error being false means everything looks good.  Add the user.
        $query = $db->prepare("INSERT INTO `USERS` (`UserName`, `LastName`, `FirstName`, UNHEX(`Password`), `Salt`, `UserRole`) VALUES (?, ?, ?, ?, ?, 'Admin')");
        $query->bind_param("sssss", $username, $lastname, $firstname, $pwhash, $salt);
        if (!$query->execute()) {
            echo "Error adding user: " . $db->error;
            $query->close();
            $db->close();
            exit();
        }
        $query->close();
        $db->close();
        if (isset($_REQUEST["mainlibrary"])) {
            header("Location: $protocol://$server$webdir/login.php?destination=nomain");
            exit();
        } else {
            header("Location: $protocol://$server$webdir/login.php");
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
        if ($matches[1]) {
            $libraryname = $_REQUEST['libraryname'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['address'])) {
        preg_match('/^[A-Za-z0-9 #\'\-.]{5,150}$/', $_REQUEST['address'], $matches);
        if ($matches[1]) {
            $address = $_REQUEST['address'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['city'])) {
        preg_match('/^[A-Za-z.\' \-]{2,75}$/', $_REQUEST['city'], $matches);
        if ($matches[1]) {
            $city = $_REQUEST['city'];
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if (isset($_REQUEST['fystart'])) {
        preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)$/', $_REQUEST['fystart'], $matches);
        if ($matches[1]) {
            $fystart = $_REQUEST['fystart'];
        } else {
            $error = true;
        }
    }

    if ($error == false) {
        //False error means everything's fine
        $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`, `FYMonth`) VALUES (?, ?, ?, 0, ?)');
        $query->bind_param('ssss', $libraryname, $address, $city, $fystart);
        if(!$query->execute()) {
            echo "Error adding library info: " . $db->error;
            $query->close();
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