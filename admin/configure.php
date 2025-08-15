<?php
session_start();
require "../config.php";
require "sqlconfig.php";

// If we're on this page it's likely we'll need to use the database,
// so just establish a connection
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
        // Don't know if the main library is set or not yet
        // so check for that
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
    # Check for a user table.  If there is none, create it.
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

    # Check for a date lookup table.  If there isn't one, create it
    try {
        $result = $db->query("SHOW TABLES LIKE 'DateLookup'");
        if ($result->num_rows == 0) {
            $db->query($date_lookup_table);
            # Add several years to the date lookup table, starting over a year ago
            try {
                $insert_query = $db->prepare("INSERT INTO `DateLookup` (`Date`, `Weekday`, `Month`, `Year`) VALUES (?, ?, ?, ?)");
                $startyear = date("Y") - 2;
                $date = date_create("$startyear-12-31");
                $endyear = date("Y") + 5;
                $enddate = date_create("$endyear-12-31");
                while ($date < $enddate) {
                    date_add($date, date_interval_create_from_date_string("1 day"));
                    $datestring = date_format($date, 'Y-m-d');
                    $weekday = date_format($date, 'l');
                    $month = date_format($date, 'F');
                    $year = date_format($date, 'Y');
                    $insert_query->bind_param('sssi', $datestring, $weekday, $month, $year);
                    $insert_query->execute();
                }
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>Error</title></head><body>";
                echo "<p>Error adding dates to date lookup table: ". $e->getMessage();
                echo "</p></body></html>";
                $db->close();
                exit();
            }
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating date lookup table: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();    
    }

    # Check for the State Reports Sections table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRSections'");
        if ($result->num_rows == 0) {
            $db->query( $report_sections);
            # After creating the table, add data
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

    # Check for the State Reports Questions table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRQuestions'");
        if ($result->num_rows == 0) {
            $db->query($report_questions);
            # After creating the table, add data
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

    # Check for the State Reports Data table.  If there is none, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRData'");
        if ($result->num_rows == 0) {
            $db->query($report_data);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating state report data table: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    # Check for the User Categories table.  If it doesn't exist, create it.
    try {
        $result = $db->query("SHOW TABLES LIKE 'UserCategories'");
        if ($result->num_rows == 0) {
            $db->query($user_categories);
            # After creating the table add data
            $insert_query = $db->prepare($user_categories_prepared_statement);
            foreach ($user_categories_data AS $category_data) {
                $insert_query->bind_param("s", $category_data);
                $insert_query->execute();
            }          
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating categories table for user created reports: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();       
    }

    # Check for the Custom Tables table.  This lists tables created by the end user.
    try {
        $result = $db->query("SHOW TABLES LIKE 'CustomTables'");
        if ($result->num_rows == 0) {
            $db->query($custom_table_list);       
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating the Custom Tables table for tracking user created tables: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();       
    }

    # Check for the CustomTableDBs table.  This is used for managing data collection from other databases
    try {
        $result = $db->query("SHOW TABLES LIKE 'CustomTableDBs'");
        if ($result->num_rows == 0) {
            $db->query($custom_table_dbs);       
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating the Custom Table Databases table for tracking relationships with external databases and custom user tables: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();       
    }    

    # Check for the CustomTableFiles table.  This is used for storing information about file formats used
    # in populating custom table data
    try {
        $result = $db->query("SHOW TABLES LIKE 'CustomTableFiles'");
        if ($result->num_rows == 0) {
            $db->query($custom_table_files);       
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating the Custom Table Files table for maintaining file formatting information for importing data into custom user tables: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();       
    } 

    # Check to see if there's a defined administrative user
    $result = $db->query("SELECT `Userid` FROM `Users` WHERE `UserRole` = 'Admin'");
    if ($result->num_rows > 0) {
        $adminset = true;
    } else {
        $adminset = false;
    }

    # Check for a Library Information table.  If there is none, create it.
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

    // Create Library Hours Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'LibraryHours'");
        if ($result->num_rows == 0) {
            $db->query($library_hours_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating LibraryHours table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    // Create Library Closings Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'LibraryClosings'");
        if ($result->num_rows == 0) {
            $db->query($library_closings_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating LibraryClosings table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    // Create Library Changes Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'LibraryChanges'");
        if ($result->num_rows == 0) {
            $db->query($library_changes_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating LibraryChanges table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    # After creating the Library Information Table, add the state report
    # support tables.  Many of these use this table so it should be created first

    # Spaces Table
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

    # SpaceUse Table
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

    # BudgetCategories Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRBudgetCategories'");
        if ($result->num_rows == 0) {
            $db->query($budgetcategories_table);
            # BudgetCategories Data
            # Unlike most of these tables there are some fixed values that should be added here
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

    # Budget Adjustment Table - Monthly Expenses & Income Go Here
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

    # Library Visits Table
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

    # Library Programs Table
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

    # Library Collection Table
    try {
        $result = $db->query("SHOW TABLES LIKE 'SRCollection'");
        if ($result->num_rows == 0) {
            $db->query($collection_table);
        }
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Error</title></head><body>";
        echo "<p>Error creating SRCollection table: ". $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();
    }

    # Interlibrary Loan Table
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

    # Computer Inventory Table
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

    # Technology Use Table
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

    # Reference Questions & Assistance Table
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

    # Check to see if there's a defined main library (library defined as not being a branch)
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

    # Check to see if a Perl configuration file has been created
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
        # Administrator already set so redirect them
        # If there is no main library set, though, that's a problem
        # So have the user authenticate and then set that up
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
                    document.getElementById('username').classList.remove('is-valid');
                    document.getElementById('username').classList.add('is-invalid');
                } else {
                    document.getElementById('username').classList.remove('is-invalid');
                    document.getElementById('username').classList.add('is-valid');
                }

                if (/^[A-Za-z][A-Za-z \-'.]{0,48}[A-Za-z.]$/.exec(firstname) === null) {
                    success = false;
                    document.getElementById('firstname').classList.remove('is-valid');
                    document.getElementById('firstname').classList.add('is-invalid');
                } else {
                    document.getElementById('firstname').classList.remove('is-invalid');
                    document.getElementById('firstname').classList.add('is-valid');
                }

                if (/^[A-Za-z][A-Za-z \-'.]{0,48}[A-Za-z.]$/.exec(lastname) === null) {
                    success = false;
                    document.getElementById('lastname').classList.remove('is-valid');
                    document.getElementById('lastname').classList.add('is-invalid');
                } else {
                    document.getElementById('lastname').classList.remove('is-invalid');
                    document.getElementById('lastname').classList.add('is-valid');
                }

                if (password.length < 5) {
                    success = false;
                    document.getElementById('password').classList.remove('is-valid');
                    document.getElementById('password').classList.add('is-invalid');
                } else {
                    document.getElementById('password').classList.remove('is-invalid');
                    document.getElementById('password').classList.add('is-valid');
                }
                
                if (passwordcheck != password) {
                    success = false;
                    document.getElementById('passwordcheck').classList.remove('is-valid');
                    document.getElementById('passwordcheck').classList.add('is-invalid');
                } else {
                    document.getElementById('passwordcheck').classList.remove('is-invalid');
                    document.getElementById('passwordcheck').classList.add('is-valid');
                }

                if (!success) {
                    event.preventDefault();
                    return false;
                } else {
                    if (window.location.protocol === "https:") {
                        // The hash gets hashed again with salt on the server side
                        // but this obscures the password more

                        // Encode password
                        const encodedpw = new TextEncoder().encode(password);

                        // Hash the password
                        const hashBuffer = await crypto.subtle.digest('SHA-256', encodedpw);

                        // Convert ArrayBuffer into an Array
                        const hashArray = Array.from(new Uint8Array(hashBuffer));

                        // Convert bytes into hex
                        const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

                        // Write hashed password to field in form
                        document.getElementById('pwhash').value = hashHex;
                    } else {
                        document.getElementById('pwhash').value = password;
                        document.getElementById('hashalgo').value = "none";
                    }

                    // submit form with hashed password
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
                <div class="alert alert-danger" type="alert" id="badun" style="display:none;"></div>
                <div class="mb-4">
                    <input type="text" id="username" name="username" class="form-control" aria-describedby="usernametips" required>
                    <div class="invalid-feedback">
                        The provided username contains non-alphanumeric characters, is shorter than 2 characters or is more than 25 characters.
                    </div>
                    <div id="usernametips" class="form-text">
                        Username is not case sensitive.  Please use only alpha-numeric characters and no spaces.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="firstname" class="form-label">First Name</label>
                    <input type="text" id="firstname" name="firstname" class="form-control" required>
                    <div class="invalid-feedback">
                        The provided first name includes invalid characters, is less than 2 characters, or is over 50 characters.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" id="lastname" name="lastname" class="form-control" required>
                    <div class="invalid-feedback">
                        The provided last name includes invalid characters, is less than 2 characters, or is over 50 characters.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" aria-describedby="passwordtips" required>
                    <div class="invalid-feedback">
                        The password cannot be extremely short or blank.
                    </div>
                    <div id="passwordtips" class="form-text">
                        All characters are accepted.  Bare minimum is five characters although you should make this a good password (10+ characters) if this site is going to be publicly accessible, though.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="passwordcheck" class="form-label">Password (again)</label>
                    <input type="password" id="passwordcheck" class="form-control" required>
                    <div class="invalid-feedback">
                        The second copy of the password did not match the first.
                    </div>
                </div>             
                <input type="hidden" id="pwhash" name="pwhash" value="">
                <input type="hidden" id="hashalgo" name="hashalgo" value="sha256">
                <input type="hidden" name="formtype" value="administrator">
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
                function validateForm(event) {
                var success = true;
                var libraryname = document.getElementById('libraryname').value;
                var legalname = document.getElementById('legallibraryname').value;
                var address = document.getElementById('address').value;
                var city = document.getElementById('city').value;
                var zip = document.getElementById('zip').value;
                var county = document.getElementById('county').value;
                var telephone = document.getElementById('telephone').value;
                var squarefootage = document.getElementById('squarefootage').value;

                if (/^[A-Za-z][A-Za-z0-9\- '().,]{3,98}[A-Za-z.]$/.exec(libraryname) === null) {
                    success = false;
                    document.getElementById('libraryname').classList.remove('is-valid');
                    document.getElementById('libraryname').classList.add('is-invalid');
                } else {
                    document.getElementById('libraryname').classList.remove('is-invalid');
                    document.getElementById('libraryname').classList.add('is-valid');
                }

                if (/^[A-Za-z][A-Za-z0-9\- '().,]{3,98}[A-Za-z.]$/.exec(legalname) === null) {
                    success = false;
                    document.getElementById('legallibraryname').classList.remove('is-valid');
                    document.getElementById('legallibraryname').classList.add('is-invalid');
                } else {
                    document.getElementById('legallibraryname').classList.remove('is-invalid');
                    document.getElementById('legallibraryname').classList.add('is-valid');
                }

                if (/^[A-Za-z0-9][A-Za-z0-9 #\'\-.]{4,148}[A-Za-z0-9.]$/.exec(address) === null) {
                    success = false;
                    document.getElementById('address').classList.remove('is-valid');
                    document.getElementById('address').classList.add('is-invalid');
                } else {
                    document.getElementById('address').classList.remove('is-invalid');
                    document.getElementById('address').classList.add('is-valid');
                }

                if (/^[A-Za-z][A-Za-z \-'.]{1,73}[A-Za-z.]$/.exec(city) === null) {
                    success = false;
                    document.getElementById('city').classList.remove('is-valid');
                    document.getElementById('city').classList.add('is-invalid');
                } else {
                    document.getElementById('city').classList.remove('is-invalid');
                    document.getElementById('city').classList.add('is-valid');
                }

                if (/^\d{5}(-\d{4}){0,1}$/.exec(zip) === null) {
                    success = false ;
                    document.getElementById('zip').classList.remove('is-valid');
                    document.getElementById('zip').classList.add('is-invalid');
                } else {
                    document.getElementById('zip').classList.remove('is-invalid');
                    document.getElementById('zip').classList.add('is-valid');
                }

                if (/^[A-Za-z][A-Za-z \-'.]{1,73}[A-Za-z.]$/.exec(county) === null) {
                    success = false;
                    document.getElementById('county').classList.remove('is-valid');
                    document.getElementById('county').classList.add('is-invalid');
                } else {
                    document.getElementById('county').classList.remove('is-invalid');
                    document.getElementById('county').classList.add('is-valid');
                }

                if (/^\d{3}[ \-]{0,1}\d{3}[ \-]{0,1}\d{4}$/.exec(telephone) === null) {
                    success = false ;
                    document.getElementById('telephone').classList.remove('is-valid');
                    document.getElementById('telephone').classList.add('is-invalid');
                } else {
                    document.getElementById('telephone').classList.remove('is-invalid');
                    document.getElementById('telephone').classList.add('is-valid');
                }

                if (/^\d{3,9}$/.exec(squarefootage) == null) {
                    success = false ;
                    document.getElementById('squarefootage').classList.remove('is-valid');
                    document.getElementById('squarefootage').classList.add('is-invalid');
                } else {
                    document.getElementById('squarefootage').classList.remove('is-invalid');
                    document.getElementById('squarefootage').classList.add('is-valid');
                }

                if (!success) {
                    event.preventDefault();
                    return false;
                } else {
                    return true;
                }
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
                <div class="mb-4">
                    <label for="libraryname" class="form-label">Library Name</label>
                    <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips" required>
                    <div class="invalid-feedback">
                        The provided library name was too long, too short, or contained unusual characters.
                    </div>
                    <div id="librarynametips" class="form-text">
                        This should represent the common way you refer to the main library.  It can be as simple as "Main Library" or it can be more descriptive ("Harold Washington Library Center of the Chicago Public Library").
                    </div>
                </div>
                <div class="mb-4">
                    <label for="legallibraryname" class="form-label">Legal Library Name</label>
                    <input type="text" id="legallibraryname" name="legallibraryname" class="form-control" aria-describedby="legallibrarynametips">
                    <div class="invalid-feedback">
                        The provided library name was too long, too short, or contained unusual characters.
                    </div>
                    <div id="legallibrarynametips" class="form-text">
                        If the legal name of the library is not the name entered in the Library Name blank, enter the legal name here.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                    <div class="invalid-feedback">
                        No address was provided, it was extremely long or extremely short, or it contained invalid characters.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                    <div class="invalid-feedback">
                        No city was provided, it was absurdly short or absurdly long, or it contained invalid characters.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="ZIP" class="form-label">ZIP Code</label>
                    <input type="text" id="zip" name="zip" class="form-control" size="10" required>
                    <div class="invalid-feedback">
                        No ZIP code was provided or it was in an unexpected format (5 numbers or 5 number plus 4 numbers separated by a hyphen)
                    </div>
                </div>
                <div class="mb-4">
                    <label for="county" class="form-label">County</label>
                    <input type="text" id="county" name="county" class="form-control" size="25" required>
                    <div class="invalid-feedback">
                        No county was provided or it had invalid formatting.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="telephone" class="form-label">Telephone Number</label>
                    <input type="text" id="telephone" name="telephone" class="form-control" size="12" required>
                    <div class="invalid-feedback">
                        No telephone number was entered or it had invalid characters (use only numbers, and optionally, spaces or hyphens)
                    </div>
                </div>
                <div class="mb-4">
                    <label for="squarefootage" class="form-label">Square Footage</label>
                    <input type="number" id="squarefootage" name="squarefootage" class="form-control" size="6" required>
                    <div class="invalid-feedback">
                        No square footage was provided or there were invalid characters.  If you aren't sure about the square footage, put a guess here and you can correct it later.
                    </div>
                </div>
                <div class="mb-4">
                    <label for="islcontrolno" class="form-label">ISL Control Number</label>
                    <input type="number" id="islcontrolno" name="islcontrolno" class="form-control" size="5">
                </div>
                <div class="mb-4">
                    <label for="islbranchno" class="form-label">ISL Branch Number</label>
                    <input type="number" id="islbranchno" name="islbranchno" class="form-control" size="2">
                </div>
                <div class="mb-4">
                    <label for="fymonth" class="form-label">Fiscal Year Start</label>
                    <select class="custom-select" id="fymonth" name="fymonth" aria-describedby="fymonthtips">
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
                    <div id="fymonthtips" class="form-text">
                        Choose the month in which your fiscal year begins.  It is assumed to start on the first of the chosen month.
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Library Hours Open</h2>
                        <label for="sundayopen" class="form-label">Sunday</label>
                        <input type="number" min="0" max="24" id="sundayopen" name="sundayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="mondayopen" class="form-label">Monday</label>
                        <input type="number" min="0" max="24" id="mondayopen" name="mondayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="tuesdayopen" class="form-label">Tuesday</label>
                        <input type="number" min="0" max="24" id="tuesdayopen" name="tuesdayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="wednesdayopen" class="form-label">Wednesday</label>
                        <input type="number" min="0" max="24" id="wednesdayopen" name="wednesdayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="thursdayopen" class="form-label">Thursday</label>
                        <input type="number" min="0" max="24" id="thursdayopen" name="thursdayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="fridayopen" class="form-label">Friday</label>
                        <input type="number" min="0" max="24" id="fridayopen" name="fridayopen" class="form-control" size="2" step=".5" value="0" required>
                        <label for="saturdayopen" class="form-label">Saturday</label>
                        <input type="number" min="0" max="24" id="saturdayopen" name="saturdayopen" class="form-control" size="2" step=".5" value="0" required>
                    </div>
                </div>
                <input type="hidden" name="formtype" value="mainlibrary">
                <button class="btn btn-primary" type="submit">Submit Library Information</button>
            </form>
        <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
        </div>
  </body>
    <?php } ?>
</html>
