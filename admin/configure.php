<?php
session_start();
require "../config.php";
require "sqlconfig.php";

//If we're on this page it's likely we'll need to use the database,
//so just establish a connection
try {
    $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
} catch (mysqli_sql_exception $e) {
    echo "<html><head><title>Configuration problem</title></head><body>";
    echo "<h1>Configuration problem</h1>";
    echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
    echo "</p></body></html>";
    exit();
}

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if ($_SESSION['UserRole'] == "Admin") {
        //Don't know if the main library is set or not yet
        //so check for that
        $adminset = true;
        $result = $db->query("SELECT `LibraryID` FROM `LibraryInfo` WHERE `Branch` = 0");
        if ($result->num_rows > 0) {
            $mainlibset = true;
        } else {
            $mainlibset = false;
        }
    } else {
        header("Location: $protocol://$server$webdir/index.php");
        exit;
    }
} else {
    #Check for a user table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'Users'");
        if ($result->num_rows == 0) {
            $db->query($users_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating Users table: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Check for the State Reports Sections table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRSections'");
        if ($result->num_rows == 0) {
            $db->query( $report_sections);
            #After creating the table, add data
            $insert_query = $db->prepare($report_sections_prepared_statement);
            foreach ($report_sections_data AS $section_data) {
                $insert_query->bind_param("is", $section_data[0], $section_data[1]);
                $insert_query->execute();
            }
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating state report sections table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Check for the State Reports Questions table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRQuestions'");
        if ($result->num_rows == 0) {
            $db->query($report_questions);
            #After creating the table, add data
            $insert_query = $db->prepare($report_questions_prepared_statement);
            foreach ($report_questions_data AS $question_data) {
                $insert_query->bind_param("iisssss", $question_data[0], $question_data[1], $question_data[2], $question_data[3], $question_data[4], $question_data[5], $question_data[6]);
                $insert_query->execute();
            }
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating state report questions table: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Check to see if there's a defined administrative user
    $result = $db->query("SELECT `Userid` FROM `Users` WHERE `UserRole` = 'Admin'");
    if ($result->num_rows > 0) {
        $adminset = true;
    } else {
        $adminset = false;
    }

    #Check for a Library Information table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'LibraryInfo'");
        if ($result->num_rows == 0) {
            $db->query($libraries_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating LibraryInfo table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #After creating the Library Information Table, add the state report
    #support tables.  Many of these use this table so it should be created first

    #Spaces Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRSpaces'");
        if ($result->num_rows == 0) {
            $db->query($spaces_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRSpaces table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #SpaceUse Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRSpaceUse'");
        if ($result->num_rows == 0) {
            $db->query($spaceuse_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRSpaceUse table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #BudgetCategories Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRBudgetCategories'");
        if ($result->num_rows == 0) {
            $db->query($budgetcategories_table);
            #BudgetCategories Data
            #Unlike most of these tables there are some fixed values that should be added here
            $insert_query = $db->prepare($budgetcategories_stmt);
            foreach ($budgetcategories_data as $budgetcategory) {
                $insert_query->bind_param("ss", $budgetcategory[0], $budgetcategory[1]);
                $insert_query->execute();
            }
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRBudgetCategories table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Budget Adjustment Table - Monthly Expenses & Income Go Here
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRBudgetAdjustments'");
        if ($result->num_rows == 0) {
            $db->query($budgetadjustments_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRBudgetAdjustments table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Library Visits Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRVisits'");
        if ($result->num_rows == 0) {
            $db->query($visits_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRVisits table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Library Programs Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRPrograms'");
        if ($result->num_rows == 0) {
            $db->query($programs_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRPrograms table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Library Physical Collection Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRPhysicalCollection'");
        if ($result->num_rows == 0) {
            $db->query($physicalcollection_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRPhysicalCollection table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Interlibrary Loan Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRILL'");
        if ($result->num_rows == 0) {
            $db->query($ill_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRILL table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Computer Inventory Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRComputers'");
        if ($result->num_rows == 0) {
            $db->query($computers_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRComputers table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Technology Use Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRTechnologyCounts'");
        if ($result->num_rows == 0) {
            $db->query($technologies_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRTechnologyCounts table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Reference Questions & Assistance Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRPatronAssistance'");
        if ($result->num_rows == 0) {
            $db->query($patronassistance_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRPatronAssistance table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    #Check to see if there's a defined main library (library defined as not being a branch)
    try {
        $result = $db->query("SELECT `LibraryID` FROM `LibraryInfo` WHERE `Branch` = 0");
        if ($result->num_rows > 0) {
            $mainlibset = true;
        } else {
            $mainlibset = false;
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error checking for library in LibraryInfo table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    # Database operations are complete;
    $db->close();

    #Check to see if a Perl configuration file has been created
    $path = $cgidir . "/shared/Common.pm";
    if (!file_exists($path)) {
        $perlconfig = fopen($path, "w") or die("Unable to create common Perl file in cgi-bin shared directory.  Make sure that you have created a folder in the cgi-bin directory which the web service can write to.");
        fwrite($perlconfig,"# Common.pm\n# Custom variables for statistics processing\npackage common;\nuse strict;\n\n");
        fwrite($perlconfig, "# Database Variables\n");
        fwrite($perlconfig,"our \$mysqlhost = '$mysqlhost';\n");
        fwrite($perlconfig,"our \$mysqldb = '$dbname';\n");
        fwrite($perlconfig,"our \$dbadmin = '$dbadmin';\n");
        fwrite($perlconfig,"our \$dbadminpw = '$dbadminpw';\n");
        fwrite($perlconfig,"our \$dbuser = '$dbuser';\n");
        fwrite($perlconfig,"our \$dbuserpw = '$dbuserpw';\n\n");
        fwrite($perlconfig,"# Site Settings\n");
        fwrite($perlconfig,"our \$protocol = '$protocol';\n");
        fwrite($perlconfig,"our \$server = '$server';\n");
        fwrite($perlconfig,"our \$webdir = '$webdir';\n");
        fwrite($perlconfig,"our \$bootstrapdir = '$bootstrapdir';\n");
        fwrite($perlconfig,"our \$cgiwebdir = '$cgiwebdir';\n");
        fwrite($perlconfig,"our \$cgidir = '$cgidir';\n");
        fclose($perlconfig);
    }

    if ($adminset) {
        #Administrator already set so redirect them
        #If there is no main library set, though, that's a problem
        #So have the user authenticate and then set that up
        if ($mainlibset) {
            header("Location: $protocol://$server$webdir/index.php");
            exit;
        } else {
            header("Location: $protocol://$server$webdir/login.php?destination=nomain");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<?php if (!$adminset) { ?>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configure Your Statistics Server</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" language="javascript">
            async function validateForm(event) {
                var success = true;
                var username = document.getElementById('username').value;
                var firstname = document.getElementById('firstname').value;
                var lastname = document.getElementById('lastname').value;
                var password = document.getElementById('password').value;
                var passwordcheck = document.getElementById('passwordcheck').value;
                if (/^[A-Za-z0-9]{2,25}$/.exec(username) === null) {
                    success = false;
                    document.getElementById('badun').style.display = "block";
                } else {
                    document.getElementById('badun').style.display = "none";
                }

                if (/^[A-Za-z \-'.]{3,50}$/.exec(firstname) === null) {
                    success = false;
                    document.getElementById('badfn').style.display = "block";
                } else {
                    document.getElementById('badfn').style.display = "none";
                }

                if (/^[A-Za-z \-'.]{3,50}$/.exec(lastname) === null) {
                    success = false;
                    document.getElementById('badln').style.display = "block";
                } else {
                    document.getElementById('badln').style.display = "none";
                }

                if (password.length < 5) {
                    success = false;
                    document.getElementById('badpw').style.display = "block";
                } else {
                    document.getElementById('badpw').style.display = "none";
                }
                
                if (passwordcheck != password) {
                    success = false;
                    document.getElementById('badpc').style.display = "block";
                } else {
                    document.getElementById('badpw').style.display = "none";
                }

                if (!success) {
                    event.preventDefault();
                    return false;
                } else {
                    if (window.location.protocol === "https:") {
                        //The hash gets hashed again with salt on the server side
                        //but this obscures the password more

                        //Encode password
                        const encodedpw = new TextEncoder().encode(password);

                        //Hash the password
                        const hashBuffer = await crypto.subtle.digest('SHA-256', encodedpw);

                        //Convert ArrayBuffer into an Array
                        const hashArray = Array.from(new Uint8Array(hashBuffer));

                        //Convert bytes into hex
                        const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

                        //Write hashed password to field in form
                        document.getElementById('pwhash').value = hashHex;
                    } else {
                        document.getElementById('pwhash').value = password;
                        document.getElementById('hashalgo').value = "none";
                    }

                    //submit form with hashed password
                    return true;
                }
            }
        </script>
  </head>
  <body>
    <div class="container-fluid">
        <h1>Configure Your Statistics Server</h1>
            <h2>Step 1: Configure an Administor Account</h2>
            <p>You need to create one initial administrative account.  After that you can create new accounts from the administrative interface.</p>
            <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div class="alert alert-danger" type="alert" id="badun" style="display:none;">The provided username contains non-alphanumeric characters, is shorter than 2 characters or is more than 25 characters.</div>
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" aria-describedby="usernametips">
                <div id="usernametips" class="form-text">
                    Username is not case sensitive.  Please use only alpha-numeric characters and no spaces.
                </div>
                <div id="badfn" class="alert alert-danger" type="alert" style="display:none;">The provided first name includes invalid characters, is less than 2 characters, or is over 50 characters.</div>
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" id="firstname" name="firstname" class="form-control">
                <div id="badln" class="alert alert-danger" type="alert" style="display:none;">The provided last name includes invalid characters, is less than 2 characters, or is over 50 characters.</div>
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" id="lastname" name="lastname" class="form-control">
                <div id="badpw" class="alert alert-danger" type="alert" style="display:none;">The password cannot be extremely short or blank.</div>
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" class="form-control" aria-describedby="passwordtips">
                <div id="passwordtips" class="form-text">
                    All characters are accepted.  Bare minimum is five characters although you should make this a good password (10+ characters) if this site is going to be publicly accessible, though.
                </div>
                <div id="badpc" class="alert alert-danger" type="alert" style="display:none;">The second copy of the password did not match the first.</div>
                <label for="passwordcheck" class="form-label">Password (again)</label>
                <input type="password" id="passwordcheck" class="form-control">
                <input type="hidden" id="pwhash" name="pwhash" value="">
                <input type="hidden" id="hashalgo" name="hashalgo" value="sha256">
                <input type="hidden" name="administrator" value="1">
                <?php if ($mainlibset) { ?>
                    <input type="hidden" name="mainlibset" value="1">
                <?php } ?>
                <button class="btn btn-primary" type="submit">Submit User Information</button>
            </form>
            <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
        </div>
  </body>
    <?php } else if (!$mainlibset) { ?>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Configure Your Statistics Server</title>
        <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
        <script type="text/javascript" language="javascript">
            function validateForm(event) {
                var success = true;
                var libraryname = document.getElementById('libraryname').value;
                var address = document.getElementById('address').value;
                var city = document.getElementById('city').value;

                if (/^[A-Za-z0-9\- '().,]{4,100}$/.exec(libraryname) === null) {
                    success = false;
                    document.getElementById('badln').style.display = "block";
                } else {
                    document.getElementById('badln').style.display = "none";
                }

                if (/^[A-Za-z0-9 #\'\-.]{5,150}$/.exec(address) === null) {
                    success = false;
                    document.getElementById('badad').style.display = "block";
                } else {
                    document.getElementById('badad').style.display = "none";
                }

                if (/^[A-Za-z \-'.]{2,75}$/.exec(city) === null) {
                    success = false;
                    document.getElementById('badcity').style.display = "block";
                } else {
                    document.getElementById('badcity').style.display = "none";
                }

                if (!success) {
                    event.preventDefault();
                    return false;
                } else {
                    return true;
                }
            }
        </script>        
    </head>
    <body>
        <div class="container-fluid">
            <h1>Configure Your Statistics Server</h1>
            <h2>Step 2: Configure a Main Library</h2>
            <p>Once you've created a main library you can create branches or modify main library or branch library details in the administrative module.</p>
            <?php if (isset($_REQUEST['liberror'])) {?>
                <div class="alert alert-danger" type="alert">There was some kind of problem processing the main library's information.</div>
            <?php } ?>
            <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div alert="alert alert-danger" type="alert" id="badln" style="display:none;">The provided library name was too long, too short, or contained unusual characters.</div>
                <label for="libraryname" class="form-label">Library Name</label>
                <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips">
                <div id="librarynametips" class="form-text">
                    This should represent the common way you refer to the main library.  It can be as simple as "Main Library" or it can be more descriptive ("Harold Washington Library Center of the Chicago Public Library").
                </div>
                <div id="badad" class="alert alert-danger" type="alert" style="display:none;">No address was provided, it was extremely long or extremely short, or it contained invalid characters.</div>
                <label for="address" class="form-label">Address</label>
                <input type="text" id="address" name="address" class="form-control">
                <div id="badcity" class="alert alert-danger" type="alert" style="display:none;">No city was provided, it was absurdly short or absurdly long, or it contained invalid characters.</div>
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" class="form-control">
                <label for="fystart" class="form-label">Fiscal Year Start</label>
                <select class="custom-select" id="fystart" name="fystart" aria-describedby="fystarttips">
                    <option value="1" selected>January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <div id="fystarttips" class="form-text">
                    Choose the month in which your fiscal year begins.  It is assumed to start on the first of the chosen month.
                </div>

                <input type="hidden" name="mainlibrary" value="1">
                <button class="btn btn-primary" type="submit">Submit Library Information</button>
            </form>
        <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
        </div>
  </body>
    <?php } ?>
</html>
