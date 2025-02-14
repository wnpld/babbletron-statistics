<?php
session_start();
require "../config.php";

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if (!$_SESSION['UserRole'] == "Admin") {
        #Send to the main site page
        header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
        exit();
    }
    #Show Admin modules

    try {
        $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Configuration Problem</title></head><body>";
        echo "<h1>Configuration problem</h1>";
        echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
        echo "</p></body></html>";
        exit();
    }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistics Server Administration</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" language="javascript">
        <?php if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "add")) {
            //This version of validateForm is for added users ?>

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

            if (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(firstname) === null) {
                success = false;
                document.getElementById('badfn').style.display = "block";
            } else {
                document.getElementById('badfn').style.display = "none";
            }

            if (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(lastname) === null) {
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
        <?php } else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "modify")) {
            //This version of validateForm is for modifying users
            ?>
        async function validateForm(event) {
            //For modifying only check fields with values
            //Fields left empty will be unchanged
            var success = true;
            var username = document.getElementById('username').value;
            var firstname = document.getElementById('firstname').value;
            var lastname = document.getElementById('lastname').value;
            var password = document.getElementById('password').value;
            var passwordcheck = document.getElementById('passwordcheck').value;
            if ((username) && (/^[A-Za-z0-9]{2,25}$/.exec(username) === null)) {
                success = false;
                document.getElementById('badun').style.display = "block";
            } else {
                document.getElementById('badun').style.display = "none";
            }

            if ((firstname) && (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(firstname)) === null) {
                success = false;
                document.getElementById('badfn').style.display = "block";
            } else {
                document.getElementById('badfn').style.display = "none";
            }

            if ((lastname) && (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(lastname)) === null) {
                success = false;
                document.getElementById('badln').style.display = "block";
            } else {
                document.getElementById('badln').style.display = "none";
            }

            if ((password) && (password.length < 5)) {
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
                if (password) {
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
                }

                //submit form with hashed password
                return true;
            }
        }
        <?php } ?>
    </script>
  </head>
  <body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo $sitename; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="true">User Management</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex flex-row-reverse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php?logout=1">(Log Out)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" disabled aria-disabled="true">Welcome, <?php echo $_SESSION['FirstName']; ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container-fluid">
            <?php 
            if (isset($_REQUEST['action'])) { 
                if ($_REQUEST['action'] == "add") { ?>
        <h1>New User</h1>
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
                <h2>User Role (Editor/Viewer applicable if related system-wide settings configured)</h2>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="userrole" value="Admin" id="adminuserrole">
                    <label class="form-check-label" for="adminuserrole">
                        Administrator
                    </label> 
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="userrole" value="Edit" id="editoruserrole">
                    <label class="form-check-label" for="editoruserrole">
                        Editor
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="userrole" value="View" id="vieweruserrole" checked>
                    <label class="form-check-label" for="vieweruserrole">
                        Viewer
                    </label>
                </div>
                <input type="hidden" id="pwhash" name="pwhash" value="">
                <input type="hidden" id="hashalgo" name="hashalgo" value="sha256">
                <input type="hidden" name="formtype" value="newuser">
                 <button class="btn btn-primary" type="submit">Submit User Information</button>
            </form>
            <?php 
                } else if (($_REQUEST['action'] == "modify") && isset($_REQUEST['userid']) && ($_REQUEST['userid'] != $_SESSION['UserID'])) { 
                    //Get information about the user (not password) that can be filled into the blanks
                    //Anything that is blank will be ignored for processing
                    //Users cannot edit themselves using this form.  That could create a paradox where they remove their own admin rights 
                    // while logged in as admin and no other admin user existing who can fix the problem
                    try {
                        $query = $db->prepare("SELECT `UserName`, `FirstName`, `LastName`, `UserRole` FROM `Users` WHERE `UserID` = ?");
                        $query->bind_param("i", $_REQUEST['userid']);
                        $query->execute();
                        $query->store_result();
                        $query->bind_result($username, $firstname, $lastname, $userrole); 
                        ?>
                        <h1>Modify User</h1>
                        <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                            <div class="alert alert-danger" type="alert" id="badun" style="display:none;">The provided username contains non-alphanumeric characters, is shorter than 2 characters or is more than 25 characters.</div>
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" aria-describedby="usernametips" value="<?php echo $username; ?>">
                            <div id="usernametips" class="form-text">
                                Username is not case sensitive.  Please use only alpha-numeric characters and no spaces.
                            </div>
                            <div id="badfn" class="alert alert-danger" type="alert" style="display:none;">The provided first name includes invalid characters, is less than 2 characters, or is over 50 characters.</div>
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo $firstname; ?>">
                            <div id="badln" class="alert alert-danger" type="alert" style="display:none;">The provided last name includes invalid characters, is less than 2 characters, or is over 50 characters.</div>
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo $lastname; ?>">
                            <div id="badpw" class="alert alert-danger" type="alert" style="display:none;">The password cannot be extremely short or blank.</div>
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" class="form-control" aria-describedby="passwordtips">
                            <div id="passwordtips" class="form-text">
                                All characters are accepted.  Bare minimum is five characters although you should make this a good password (10+ characters) if this site is going to be publicly accessible, though.
                            </div>
                            <div id="badpc" class="alert alert-danger" type="alert" style="display:none;">The second copy of the password did not match the first.</div>
                            <label for="passwordcheck" class="form-label">Password (again)</label>
                            <input type="password" id="passwordcheck" class="form-control">
                            <h2>User Role (Editor/Viewer applicable if related system-wide settings configured)</h2>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="userrole" value="Admin" id="adminuserrole"<?php if ($userrole == "Admin") { echo " checked"; } ?>>
                                <label class="form-check-label" for="adminuserrole">
                                    Administrator
                                </label> 
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="userrole" value="Edit" id="editoruserrole"<?php if ($userrole == "Edit") { echo " checked"; } ?>>
                                <label class="form-check-label" for="editoruserrole">
                                    Editor
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="userrole" value="View" id="vieweruserrole"<?php if ($userrole == "View") { echo " checked"; } ?>>
                                <label class="form-check-label" for="vieweruserrole">
                                    Viewer
                                </label>
                            </div>
                            <input type="hidden" id="pwhash" name="pwhash" value="">
                            <input type="hidden" id="hashalgo" name="hashalgo" value="sha256">
                            <input type="hidden" name="formtype" value="modifyuser">
                            <input type="hidden" name="userid" value="<?php echo $_REQUEST['userid']; ?>">
                             <button class="btn btn-primary" type="submit">Submit User Information</button>
                        </form>
                    <?php } catch (mysqli_sql_exception $e) {
                        echo "<html><head><title>Error</title></head><body>";
                        echo "<p>Error retrieving user information: " . $e->getMessage();
                        echo "</p></body></html>";
                        $db->close();
                        exit();
                    }
                    ?>

                <?php } 
            } else {
                //No action has been declared ?>
            <h1>User Management</h1>
            <?php 
            //Check for success or error conditions
            if (isset($_REQUEST['usermodified'])) {
                if ($_REQUEST['usermodified'] == "true") { ?>
                    <div class="alert alert-success" type="alert">User successfully modified</div>
                <?php } else { ?>
                    <div class="alert alert-danger" type="alert">User modification failed</div>
                <?php }
            } else if (isset($_REQUEST['useradded'])) {
                if ($_REQUEST['useradded'] == "true") { ?>
                    <div class="alert alert-success" type="alert">User successfully added</div>
                <?php } else { ?>
                    <div class="alert alert-danger" type="alert">Unable to add user</div>
               <?php }
            } else if (isset($_REQUEST['userdeleted'])) { ?>
                    <div class="alert alert-success" type="alert">User successfully deleted</div>
            <?php } 
             } 
            //Get a list of current users
            
            try {
                $result = $db->query("SELECT `UserID`, `UserName`, `FirstName`, `LastName`, `UserRole` FROM `Users` ORDER BY `UserRole` ASC, `LastName` ASC, `FirstName` ASC"); ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th><th>Last Name</th><th>First Name</th><th>User Rights</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($user = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $user['UserName']; ?></td>
                            <td><?php echo $user['LastName']; ?></td>
                            <td><?php echo $user['FirstName']; ?></td>
                            <td><?php echo $user['UserRole']; ?></td>
                            <td>
                            <?php
                            if ($_SESSION['UserID'] != $user['UserID']) {
                                //User can't delete themselves or downgrade their own rights
                                ?>
                                <a class="btn btn-primary btn-sm" href="users.php?action=modify&userid=<?php echo $user['UserID']; ?>">Modify User</a>
                                <a class="btn btn-danger btn-sm" href="process.php?formtype=deleteuser&userid=<?php echo $user['UserID']; ?>" onclick="return confirm('Are you sure you wish to delete the user <?php echo $user['FirstName'] . ' ' . $user['LastName']; ?>')">Delete User</a>
                            <?php } else { ?>
                                Use the <a href="../settings.php">Settings</a> page edit your account.
                            <?php } ?>
                            </td>
                        </tr>
                    <?php }  ?>
                    </tbody>
                </table>


            <?php } catch (mysqli_sql_exception $e) {
                
                echo "<div class=\"alert alert-danger\" type=\"alert\">Error</div>";
                echo "<p>Error retrieving list of users: " . $e->getMessage();
                echo "</p></body></html>";
                $db->close();
                exit();  
            } ?>
            
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php 
} else {
    #Redirect the user to the login page
    header("Location: $protocol://$server$webdir/login.php?destination=admin_index");
    exit();
}
?>