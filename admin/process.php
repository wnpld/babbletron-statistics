<?php
//This script processes user and library changes
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
if (isset($_REQUEST['formtype'])) {
    if ($_REQUEST['formtype'] == "administrator") {
        #New administrator login
        $checked = userchecks($_REQUEST['username'], $_REQUEST['firstname'], $_REQUEST['lastname'], $_REQUEST['pwhash'], $_REQUEST['hashalgo'], null, "new"); 

        if ($checked != false) {
            //Error being false means everything looks good.  Add the user.
            try {
                $query = $db->prepare("INSERT INTO `Users` (`UserName`, `LastName`, `FirstName`, `Password`, `Salt`, `UserRole`) VALUES (?, ?, ?, UNHEX(?), ?, 'Admin')");
                $query->bind_param("sssss", $checked['UserName'], $checked['LastName'], $checked['FirstName'], $checked['Password'], $checked['Salt']);
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
    } else if ($_REQUEST['formtype'] == "mainlibrary") {
        #New main library
        $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['address'], $_REQUEST['city'], $_REQUEST['fymonth'], "new");

        if ($checked != false) {
            //If checked returns an array everything's fine.  If it's false there's an error
            try{
                $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`, `FYMonth`) VALUES (?, ?, ?, 0, ?)');
                $query->bind_param('sssi', $checked['LibraryName'], $checked['LibraryAddress'], $checked['LibraryCity'], $checked['FYMonth']);
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
            header("Location: $protocol://$server$webdir/admin/index.php");
            exit();
        } else {
            $db->close();
            header("Location: $protocol://$server$webdir/admin/configure.php?liberror=1");
            exit();
        }
    } else {
        #This script is also used for creating new users and new library branches
        #Those operations are handled here after doing a user authentication check
        if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
            if (!$_SESSION['UserRole'] == "Admin") {
                #Send to the main site page
                header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
                exit();
            }
        if ($_REQUEST['formtype'] == "newuser") {
            $checked = userchecks($_REQUEST['username'], $_REQUEST['firstname'], $_REQUEST['lastname'], $_REQUEST['pwhash'], $_REQUEST['hashalgo'], null, "new");

            if (isset($_REQUEST['userrole'])) {
                preg_match('/^(Admin|Edit|View)$/', $_REQUEST['userrole'], $matches);
                if ($matches[0]) {
                    $role = $matches[0];
                } else {
                    $role = "View";
                }
            } else {
                #Don't know why this wouldn't be set, but
                #configure at the least priviledged level
                $role = "View";
            }

            if ($checked != false) {
                //A return of false means that there was an error
                try {
                    $query = $db->prepare("INSERT INTO `Users` (`UserName`, `LastName`, `FirstName`, `Password`, `Salt`, `UserRole`) VALUES (?, ?, ?, UNHEX(?), ?, ?)");
                    $query->bind_param("ssssss", $checked['UserName'], $checked['LastName'], $checked['FirstName'], $checked['Password'], $checked['Salt'], $role);
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
                header("Location: $protocol://$server$webdir/admin/users.php?useradded=true");
                exit();
            } else {
                //Bad data submitted.  Send back to index
                header("Location: $protocol://$server$webdir/admin/users.php?useradded=false");
                exit();
            }
        } else if ($_REQUEST['formtype'] == "modifyuser") {
            if (isset($_REQUEST['userid'])) {
                # Get salt from existing user
                try {
                    $query = $db->prepare("SELECT `Salt` FROM `Users` WHERE `UserID` = ?");
                    $query->bind_param('i', $_REQUEST['userid']);
                    $query->execute();
                    $result = $query->get_result();
                    $salt = $result->fetch_column(0);
                    $query->close();
                    if (isset($salt)) {
                        $checked = userchecks($_REQUEST['username'], $_REQUEST['firstname'], $_REQUEST['lastname'], $_REQUEST['pwhash'], $_REQUEST['hashalgo'], $salt, "old");

                        if ($checked != false) {
                            $update_sql = "UPDATE Users SET ";
                            $params = array();
                            $paramtypes = "";
                            $count = 0;
                            if (isset($_REQUEST['userrole'])) {
                                preg_match('/^(Admin|Edit|View)$/', $_REQUEST['userrole'], $matches);
                                if ($matches[0]) {
                                    $count++;
                                    $update_sql .= "`UserRole` = ?";
                                    array_push($params, $matches[0]);
                                    $paramtypes .= "s";
                                }
                            }
                            foreach ($checked as $field => $value) {
                                if ($count > 0) {
                                    $update_sql .= ", ";
                                }
                                $update_sql .= "`$field` = ?";
                                array_push($params, $value);
                                $paramtypes .= "s";
                                $count++;
                            }
                            $update_sql .= " WHERE `UserID` = ?";
                            array_push($params, $_REQUEST['userid']);
                            $paramtypes .= "i";

                            $query = $db->prepare($update_sql);
                            $query->bind_param($paramtypes, ...$params);
                            $query->execute();
                            $query->close();
                            $db->close();
                            header("Location: $protocol://$server$webdir/admin/users.php?usermodified=true");
                            exit();
                        } else {
                            header("Location: $protocol://$server$webdir/admin/users.php?usermodified=false");
                        }

                    } else {
                        header("Location: $protocol://$server$webdir/admin/users.php?usermodified=false");
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error modifying user: " . $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();                
                }
            } else {
                #Can't do an update without a user id
                header("Location: $protocol://$server$webdir/admin/users.php?usermodified=false");
                exit();
            }

        } else if ($_REQUEST['formtype'] == "deleteuser") {
            if (isset($_REQUEST['userid'])) {
                try {
                    $query = $db->prepare("DELETE FROM `Users` WHERE `UserID` = ?");
                    $query->bind_param("i", $_REQUEST['userid']);
                    $query->execute();
                    $query->close();
                    $db->close();
                    header("Location: $protocol://$server$webdir/admin/users.php?userdeleted=true");
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error deleting user: " . $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();      
                }
            }

        } else if ($_REQUEST['formtype'] == "newbranch") {
            #New branch library
            $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['address'], $_REQUEST['city'], null, "new");

            if ($checked != false) {
                //If checked returns false something failed
                try{
                    $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`) VALUES (?, ?, ?, 1)');
                    $query->bind_param('sss', $checked['libraryname'], $checked['address'], $checked['city']);
                    $query->execute();
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error adding branch info: " . $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                }
                $query->close();
                $db->close();
                header("Location: $protocol://$server$webdir/admin/libraries.php?branchadded=true");
                exit();
            } else {
                $db->close();
                header("Location: $protocol://$server$webdir/admin/libraries.php?branchadded=false");
                exit();
            }
        } else if ($_REQUEST['formtype'] == "modifybranch") {
            #Modify branch/main library
            $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['address'], $_REQUEST['city'], $_REQUEST['fymonth'], "old");

            if ($checked != false) {
                //If checked returns false something failed
                if (isset($_REQUEST['libraryid'])) {
                    $params = array();
                    $paramtypes = "";
                    $update_sql = "UPDATE LibraryInfo SET ";
                    foreach ($checked as $field => $value) {
                        if (strlen($paramtypes) > 0) {
                            $update_sql .= ", ";
                        }
                        $update_sql .= "`$field` = ?";
                        array_push($params, $value);
                        if ($field == "fymonth") {
                            $paramtypes .= "i";
                        } else {
                            $paramtypes .= "s";
                        }
                    }
                    try{
                        $query = $db->prepare($update_sql);
                        $query->bind_param($paramtypes, ...$params);
                        $query->execute();
                        $query->close();
                        $db->close();
                        header("Location: $protocol://$server$webdir/admin/libraries.php?modify=true");
                        exit();
                    } catch (mysqli_sql_exception $e) {
                        echo "<html><head><title>Error</title></head><body>";
                        echo "<p>Error adding branch info: " . $e->getMessage();
                        echo "</p></body></html>";
                        $db->close();
                        exit();
                    }
                } else {
                    //Can't do anything without a library id
                    $db->close();
                    header("Location: $protocol://$server/$webdir/admin/libraries.php?modify=false");
                    exit();
                }
            } else {
                $db->close();
                header("Location: $protocol://$server$webdir/admin/libraries.php?branchadded=false");
                exit();
            }
        } else if ($_REQUEST['formtype'] == "deletebranch") {
            if (isset($_REQUEST['libraryid'])) {
                try {
                    //Want to avoid any situation where the main library were to be deleted
                    $query = $db->prepare("DELETE FROM LibraryInfo WHERE LibraryID = ? AND Branch != 0");
                    $query->bind_param("i", $_REQUEST['libraryid']);
                    $query->execute();
                    $query->close();
                    $db->close();
                    header("Location: $protocol://$server$webdir/admin/libraries.php?delete=true");
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error deleting library branch: " . $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();      
                }
            }
        } else {
            #Don't know what the user wants to do but
            #They aren't going to be able to do it
            header("Location: $protocol://$server$webdir/admin/index.php");
            exit();
        }

        } else {
            #User isn't logged in and shouldn't be here.  Forward to login page
            header("Location: $protocol://$server$webdir/login.php");
            exit();
        }
    }
} else {
    #No formtype variable declared
    header("Location: $protocol://$server$webdir/index.php");
    exit();    
}

function saltmachine() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,./<>?!^&*()-_=+';
    $randomstring = '';
    for ($x = 0; $x < 100; $x++) {
        $randomstring .= $characters[random_int(0, strlen($characters) -1)];
    }
    return $randomstring;
}

function userchecks($username, $firstname, $lastname, $pwhash, $hashalgo, $salt, $status) {
    $error = false;
    $changed = array();
    if (isset($username)) {
        preg_match('/^[A-Za-z0-9]{2,25}$/', $username, $matches);
        if ($matches[0]) {
            $checked_un = strtolower($username);
            if ($status == "old") {
                $changed['UserName'] = $checked_un;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($firstname)) {
        preg_match('/^[A-Za-z][A-Za-z \-\'.]{0,48}[A-Za-z.]$/', $firstname, $matches);
        if ($matches[0]) {
            $checked_fn = $firstname;
            if ($status == "old") {
                $changed['FirstName'] = $checked_fn;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($lastname)) {
        preg_match('/^[A-Za-z][A-Za-z \-\'.]{0,48}[A-Za-z.]$/', $lastname, $matches);
        if ($matches[0]) {
            $checked_ln = $_REQUEST['lastname'];
            if ($status == "old") {
                $changed['LastName'] = $checked_ln;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($pwhash)) {
        if (isset($hashalgo)) {
            $algorithm = $hashalgo;
        } else {
            //Should have something in it, but 
            //if there's nothing assume none
            $algorithm = "none";
        }
        //In an http context getting a hash in javascript isn't possible
        //so it will be hashed twice here: once on its own and then with salt
        if ($algorithm == "none") {
            $pwhash = hash('sha256', $pwhash);
        }

        if (!isset($salt)) {
            $salt = saltmachine();
        }
        $pwhash .= $salt;
        $password = hash('sha256', $pwhash);
        if ($status == "old") {
            $changed['Password'] = $password;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if ($error == true) {
        return false;
    } else {
        if ($status == "new") {
            $values = array("UserName" => $checked_un, "FirstName" => $checked_fn, "LastName" => $checked_ln, "Password" => $password, "Salt" => $salt);
            return $values;
        } else {
            return $changed;
        }
    }
}

function branchchecks($libraryname, $address, $city, $fymonth, $status) {
    $error = false;
    $changed = array();
    if (isset($libraryname)) {
        preg_match('/^[A-Za-z][A-Za-z0-9 \-\'().,]{3,98}[A-Za-z().]$/', $libraryname, $matches);
        if ($matches[0]) {
            $checked_ln = $libraryname;
            if ($status == "old") {
                $changed['LibraryName'] = $checked_ln;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($address)) {
        preg_match('/^[A-Za-z0-9][A-Za-z0-9 #\'\-.]{4,148}[A-Za-z0-9#.]$/', $address, $matches);
        if ($matches[0]) {
            $checked_ad = $address;
            if ($status == "old") {
                $changed['LibraryAddress'] = $checked_ad;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($city)) {
        preg_match('/^[A-Za-z][A-Za-z.\' \-]{1,73}[A-Za-z.]$/', $city, $matches);
        if ($matches[0]) {
            $checked_cy = $city;
            if ($status == "old") {
                $changed['LibraryCity'] = $checked_cy;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($fymonth)) {
        preg_match('/^([1-9]|1[0-2])$/', $fymonth, $matches);
        if ($matches[0]) {
            $checked_fym = $fymonth;
            if ($status == "old") {
                $changed['FYMonth'] = $checked_fym;
            }
        } else {
            $error = true;
        }
    }

    if ($error == true) {
        return false;
    } else {
        if ($status == "new") {
            if (isset($checked_fym)) {
                $values = array("LibraryName" => $checked_ln, "LibraryAddress" => $checked_ad, "LibraryCity" => $checked_cy, "FYMonth" => $checked_fym);
            } else {
                $values = array("LibraryName" => $checked_ln, "LibraryAddress" => $checked_ad, "LibraryCity" => $checked_cy);
            }
            return $values;
        } else {
            return $changed;
        }

    }

}
?>