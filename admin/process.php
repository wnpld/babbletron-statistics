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
        $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['legallibraryname'], $_REQUEST['address'], $_REQUEST['city'], $_REQUEST['zip'], $_REQUEST['county'], $_REQUEST['telephone'], $_REQUEST['squarefootage'], $_REQUEST['islcontrolno'], $_REQUEST['islbranchno'], $_REQUEST['fymonth'], $_REQUEST['sundayopen'], $_REQUEST['mondayopen'], $_REQUEST['tuesdayopen'], $_REQUEST['wednesdayopen'], $_REQUEST['thursdayopen'], $_REQUEST['fridayopen'], $_REQUEST['saturdayopen'], "new");

        if ($checked != false) {
            //If checked returns an array everything's fine.  If it's false there's an error
            try{
                $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LegalName`, `LibraryAddress`, `LibraryCity`, `LibraryZIP`, `LibraryCounty`, `LibraryTelephone`, `SquareFootage`, `ISLControlNo`, `ISLBranchNo`, `Branch`, `FYMonth`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)');
                $query->bind_param('sssssssissi', $checked['LibraryName'], $checked['LegalName'], $checked['LibraryAddress'], $checked['LibraryCity'], $checked['LibraryZIP'], $checked['LibraryCounty'], $checked['LibraryTelephone'], $checked['SquareFootage'], $checked['ISLControlNo'], $checked['ISLBranchNo'], $checked['FYMonth']);
                $query->execute();
                $lastid = $db->insert_id;
                $query->close();
                $days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
                #For date implemented we'll just January 1 from 5 years ago.
                $year = date('Y');
                $year = $year - 5;
                $impdate = $year . "-01-01";
                $query = $db->prepare('INSERT INTO `LibraryHours` (`LibraryID`, `DateImplemented`, `DayOfWeek`, `HoursOpen`) VALUES (?, ?, ?, ?)');
                foreach ($days as $day) {
                    $query->bind_param("issi", $lastid, $impdate, $day, $checked[$day]);
                    $query->execute();
                }
                $query->close();
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>Error</title></head><body>";
                echo "<p>Error adding library info: " . $e->getMessage();
                echo "</p></body></html>";
                $db->close();
                exit();
            }
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
            $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['legallibraryname'], $_REQUEST['address'], $_REQUEST['city'], $_REQUEST['zip'], $_REQUEST['county'], $_REQUEST['telephone'], $_REQUEST['squarefootage'], $_REQUEST['ISLControlNo'], $_REQUEST['ISLBranchNo'], null, $_REQUEST['sundayopen'], $_REQUEST['mondayopen'], $_REQUEST['tuesdayopen'], $_REQUEST['wednesdayopen'], $_REQUEST['thursdayopen'], $_REQUEST['fridayopen'], $_REQUEST['saturdayopen'], "new");

            if ($checked != false) {
                //If checked returns false something failed
                try{
                    $query = $db->prepare('INSERT INTO `LibraryInfo` (`LibraryName`, `LegalName`, `LibraryAddress`, `LibraryCity`, `LibraryZIP`, `LibraryCounty`, `LibraryTelephone`, `SquareFootage`, `ISLControlNo`, `ISLBranchNo`, `Branch`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
                    $query->bind_param('ssssssssss', $checked['LibraryName'], $checked['LegalName'], $checked['LibraryAddress'], $checked['LibraryCity'], $checked['LibraryZIP'], $checked['LibraryCounty'], $checked['LibraryTelephone'], $checked['SquareFootage'], $checked['ISLControlNo'], $checked['ISLBranchNo']);
                    $query->execute();
                    $lastid = $db->insert_id;
                    $query->close();
                    $days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
                    #For date implemented we'll just January 1 from 5 years ago.
                    $year = date('Y');
                    $year = $year - 5;
                    $impdate = $year . "-01-01";
                    $query = $db->prepare('INSERT INTO `LibraryHours` (`LibraryID`, `DateImplemented`, `DayOfWeek`, `HoursOpen`) VALUES (?, ?, ?, ?)');
                    foreach ($days as $day) {
                        $query->bind_param("issi", $lastid, $impdate, $day, $checked[$day]);
                        $query->execute();
                    }
                    $query->close();                    
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
            #Get fiscal month -- needed for official changes
            $fymonth_info = $db->query("SELECT `FYMonth`+0 AS `Month` FROM `LibraryInfo` WHERE `Branch` = 0");
            $fymonth = $fymonth_info->fetch_column(0);

            #Modify branch/main library
            $checked = branchchecks($_REQUEST['libraryname'], $_REQUEST['legallibraryname'], $_REQUEST['address'], $_REQUEST['city'], $_REQUEST['zip'], $_REQUEST['county'], $_REQUEST['telephone'], $_REQUEST['squarefootage'], $_REQUEST['islcontrolno'], $_REQUEST['islbranchno'], $_REQUEST['fymonth'], $_REQUEST['sundayopen'], $_REQUEST['mondayopen'], $_REQUEST['tuesdayopen'], $_REQUEST['wednesdayopen'], $_REQUEST['thursdayopen'], $_REQUEST['fridayopen'], $_REQUEST['saturdayopen'], "old");

            #ChangeFields
            $changefields = array('LegalName' => array('legallibraryname-radio', 'legallibraryname-calendar', 'LegalNameOfficial', 'legallibraryname-checkbox'), 'LibraryAddress' => array('address-radio', 'address-calendar', 'LibraryAddressPhysical', 'address-checkbox'), 'LibraryCity' => array('city-radio', 'city-calendar'), 'LibraryZIP' => array('zip-radio', 'zip-calendar'), 'LibraryCounty' => array('county-radio', 'county-calendar'), 'LibraryTelephone' => array('telephone-radio', 'telephone-calendar'), 'SquareFootage' => array('squarefootage-radio', 'SquareFootageReason', 'squarefootage-change-reason'), 'ISLControlNo' => 0, 'ISLBranchNo' => 0, 'FYmonth' => 0, 'hours' => array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'));

            if ($checked != false) {
                //If checked returns false something failed
                preg_match('/^\d+$/', $_REQUEST['libraryid'], $matches);
                $libraryid = $matches[0];
                if ($libraryid) {
                    $params = array();
                    $paramtypes = "";
                    foreach ($changefields as $field => $value) {
                        #Go through the change fields and use those to determine what
                        #fields should be being paid attention to.
                        if (gettype($value) == "array") {
                            if (isset($_REQUEST[$value[0]])) {
                                #Legal name, Library Address, or Square Footage
                                if ($_REQUEST[$value[0]] == "correction") {
                                    #no need to update the changes table
                                    try {
                                        $sql = $db->prepare("UPDATE `LibraryInfo` SET `$field` = ? WHERE `LibraryID` = $libraryid");
                                        $sql->bind_param('s', $checked[$field]);
                                        $sql->execute();
                                        $sql->close();
                                    } catch (mysqli_sql_exception $e) {
                                        echo "<html><head><title>Error</title></head><body>";
                                        echo "<p>Error updating library: " . $e->getMessage();
                                        echo "</p></body></html>";
                                        $db->close();
                                        exit();      
                                    }
                                } else {
                                    #If it's not a correction, it's a change. A lot more steps.
                                    #Get the old value
                                    try {
                                        $sql = $db->prepare("SELECT `$field` FROM LibraryInfo WHERE `LibraryID` = ?");
                                        $sql->bind_param('i', $_REQUEST['libraryid']);
                                        $sql->execute();
                                        $result = $sql->get_result();
                                        $oldvalue = $result->fetch_column(0);
                                        $sql->close();

                                        #Using the submitted date, figure out the fiscal year for the change
                                        preg_match('/(\d\d\d\d)-(\d\d)-\d\d/', $_REQUEST[$value[1]], $matches);
                                        if ($matches[0]) {
                                            $year = $matches[1];
                                            $month = $matches[2];
                                            if ($fymonth == 1) {
                                                $fyear = $year;
                                            } else {
                                                if ($month <= $fymonth) {
                                                    $fyear = $year;
                                                } else {
                                                    $fyear = $year-1;
                                                }
                                            }
                                            $change_info = $db->query("SELECT `LibraryID` FROM `LibraryChanges` WHERE `LibraryID` = '$libraryid' AND `FYChanged` = '$fyear'");
                                            if ($change_info->num_rows == 0) {
                                                #Add a row
                                                $db->query("INSERT INTO `LibraryChanges` (`LibraryID`, `FYChanged`) VALUES ('$libraryid', '$fyear')");
                                            }
                                            #Three data types have specific forms, so do those first
                                            #The old information goes into the changes table,
                                            #not the new.  This is so we have a record of what the old
                                            #information was, but the current information is in the primary
                                            #record
                                            if (count($value) == 4) {
                                                #This is a category with two values to update
                                                $sql = $db->prepare("UPDATE `LibraryChanges` SET `$field` = ?, `$value[2]` =? WHERE `LibraryID` = '$libraryid' AND `FYChanged` = '$fyear'");
                                                $sql->bind_param('ss', $oldvalue, $_REQUEST[$value[3]]);
                                                $sql->execute();
                                                $sql->close();
                                            } else {
                                                #Everything else
                                                $sql = $db->prepare("UPDATE `LibraryChanges` SET `$field` = ? WHERE `LibraryID` = '$libraryid' AND `FYChanged` = '$fyear'");
                                                $sql->bind_param('s', $oldvalue);
                                                $sql->execute();
                                                $sql->close();
                                            }
                                            #Finally, update the library info table with the new information
                                            $sql = $db->prepare("UPDATE `LibraryInfo` SET `$field` = ? WHERE `LibraryID` = '$libraryid'");
                                            $sql->bind_param('s', $checked[$field]);
                                            $sql->execute();
                                            $sql->close();
                                        } else {
                                            #Invalid or blank calendar submission
                                            #error out
                                        }
                                    } catch (mysqli_sql_exception $e) {
                                        echo "<html><head><title>Error</title></head><body>";
                                        echo "<p>Error adding branch info: " . $e->getMessage();
                                        echo "</p></body></html>";
                                        $db->close();
                                        exit();
                                    }
                                }
                            }
                        } else {
                            #One of the three integers, which don't induce
                            #changes in the changes table
                            try {
                                $sql = $db->prepare("UPDATE `LibraryInfo` SET `$field` = ? WHERE `LibraryID` = '$libraryid'");
                                $sql->bind_param('s', $checked[$field]);
                                $sql->execute();
                                $sql->close();
                            } catch (mysqli_sql_exception $e) {
                                echo "<html><head><title>Error</title></head><body>";
                                echo "<p>Error adding branch info (fiscal year month, control no, or branch no): " . $e->getMessage();
                                echo "</p></body></html>";
                                $db->close();
                                exit();
                            }
                        }
                    }
                    
                    foreach ($checked as $field => $value) {
                        #First do everything except hours.
                        #Hours changes go in a different database table

                        if ($field != 'hours') {

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
                    }
                    header("Location: $protocol://$server$webdir/admin/libraries.php?modify=true");
                    exit();
                } else {
                    //Can't do anything without a library id
                    $db->close();
                    header("Location: $protocol://$server/$webdir/admin/libraries.php?modify=false");
                    exit();
                }
            } else {
                $db->close();
                header("Location: $protocol://$server$webdir/admin/libraries.php?branchmodified=false");
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

function branchchecks($libraryname, $legallibraryname, $address, $city, $zip, $county, $telephone, $footage, $islcontrolno, $islbranchno, $fymonth, $sundayhours, $mondayhours, $tuesdayhours, $wednesdayhours, $thursdayhours, $fridayhours, $saturdayhours, $status) {
    $error = false;
    $changed = array();
    if (isset($libraryname)) {
        preg_match('/^[A-Za-z][A-Za-z0-9 \-\'().,]{3,98}[A-Za-z().]$/', $libraryname, $matches);
        if ($matches[0]) {
            $checked_libn = $libraryname;
            if ($status == "old") {
                $changed['LibraryName'] = $checked_libn;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($legallibraryname)) {
        preg_match('/^[A-Za-z][A-Za-z0-9 \-\'().,]{3,98}[A-Za-z().]$/', $legallibraryname, $matches);
        if ($matches[0]) {
            $checked_legn = $legallibraryname;
            if ($status == "old") {
                $changed['LegalName'] = $checked_legn;
            }
        } else {
            $error = true;
        }        
    } else {
        if (isset($checked_libn)) {
           $checked_legn = $checked_libn;
        } else {
            #No valid name at all
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
            $checked_city = $city;
            if ($status == "old") {
                $changed['LibraryCity'] = $checked_city;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($zip)) {
        preg_match('/^\d{5}(-\d{4}){0,1}$/', $zip, $matches);
        if ($matches[0]) {
            $checked_zip = $zip;
            if ($status == "old") {
                $changed['ZIP'] = $checked_zip;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($county)) {
        preg_match('/^[A-Za-z][A-Za-z.\' \-]{1,73}[A-Za-z.]$/', $county, $matches);
        if ($matches[0]) {
            $checked_cnty = $county;
            if ($status == "old") {
                $changed['LibraryCounty'] = $checked_cnty;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($telephone)) {
        $telephone = preg_replace('/[\-() ]/', '', $telephone);
        preg_match('/^\d{10}$/', $telephone, $matches);
        if ($matches[0]) {
            $checked_tel = $telephone;
            if ($status == "old") {
                $changed['LibraryTelephone'] = $checked_tel;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($footage)) {
        preg_match('/^\d+$/', $footage, $matches);
        if ($matches[0]) {
            $checked_footage = $footage;
            if ($status == "old") {
                $changed['SquareFootage'] = $checked_footage;
            }
        } else {
            $error = true;
        }
    } else {
        if ($status == "new") {
            $error = true;
        }
    }

    if (isset($islcontrolno)) {
        preg_match('/^\d+$/', $islcontrolno, $matches);
        if ($matches[0]) {
            $checked_ctrlno = $islcontrolno;
            if ($status == "old") {
                $changed['ISLControlNo'] = $checked_ctrlno;
            }
        } else {
            $checked_ctrlno = null;
        }
    } else {
        $checked_ctrlno = null;
    }

    if (isset($islbranchno)) {
        preg_match('/^\d+$/', $islbranchno, $matches);
        if ($matches[0]) {
            $checked_brchno = $islbranchno;
            if ($status == "old") {
                $changed['ISLBranchNo'] = $checked_brchno;
            }
        } else {
            $checked_brchno = null;
        }
    } else {
        $checked_brchno = null;
    }

    if (isset($fymonth)) {
        if ($fymonth != "null") { //Non-main branches don't use this and I'm using "null" in the form to
                                  //avoid pointless error messages about the field being not used.
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
    }

    $hours = array("Sunday" => $sundayhours, "Monday" => $mondayhours, "Tuesday" => $tuesdayhours, "Wednesday" => $wednesdayhours, "Thursday" => $thursdayhours, "Friday" => $fridayhours, "Saturday" => $saturdayhours);
    foreach ($hours as $day => $openhours) {
        if ($status == "new") {
            #This check won't trigger an error, but if the value is
            #weird it will just make it 0
            if (is_numeric($openhours)) {
                if (($hours < 0) or ($hours > 24)) {
                    $hours[$day] = 0;
                }
            } else {
                $hours[$day] = 0;
            }
        } else {
            #If it's not new, we'll be more picky
            #and only take valid numbers
            if (is_numeric($openhours)) {
                if (($hours >= 0) or ($hours <= 24)) {
                    #Put all of the hours in an additional layer for changed
                    #so they can be easily identified
                    $changed['hours'][$day] = $hours;
                }
            }
        }
    }

    if ($error == true) {
        return false;
    } else {
        if ($status == "new") {
            if (isset($checked_fym)) {
                $values = array("LibraryName" => $checked_libn, "LegalName" => $checked_legn, "LibraryAddress" => $checked_ad, "LibraryCity" => $checked_city, "LibraryZIP" => $checked_zip, "LibraryCounty" => $checked_cnty, "LibraryTelephone" => $checked_tel, "SquareFootage" => $checked_footage, "ISLControlNo" => $checked_ctrlno, "ISLBranchNo" => $checked_brchno, "FYMonth" => $checked_fym, "Sunday" => $hours['Sunday'], "Monday" => $hours['Monday'], "Tuesday" => $hours['Tuesday'], "Wednesday" => $hours['Wednesday'], "Thursday" => $hours['Thursday'], "Friday" => $hours['Friday'], "Saturday" => $hours['Saturday']);
            } else {
                $values = array("LibraryName" => $checked_libn, "LegalName" => $checked_legn, "LibraryAddress" => $checked_ad, "LibraryCity" => $checked_city, "LibraryZIP" => $checked_zip, "LibraryCounty" => $checked_cnty, "LibraryTelephone" => $checked_tel, "SquareFootage" => $checked_footage, "ISLControlNo" => $checked_ctrlno, "ISLBranchNo" => $checked_brchno, "Sunday" => $hours['Sunday'], "Monday" => $hours['Monday'], "Tuesday" => $hours['Tuesday'], "Wednesday" => $hours['Wednesday'], "Thursday" => $hours['Thursday'], "Friday" => $hours['Friday'], "Saturday" => $hours['Saturday']);
            }
            return $values;
        } else {
            return $changed;
        }

    }

}
?>