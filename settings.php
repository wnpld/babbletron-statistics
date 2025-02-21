<?php
session_start();
require "config.php";

try {
    $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
} catch (mysqli_sql_exception $e) {
    echo "<html><head><title>Configuration problem</title></head><body>";
    echo "<h1>Configuration problem</h1>";
    echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
    echo "</p></body></html>";
    exit();
}

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "updatesettings")) {
    if (isset($_REQUEST['UserID']) && isset($_SESSION['UserID']) && ($_REQUEST['UserID'] == $_SESSION['UserID'])) {
        $fieldlist = array();
        if (isset($_REQUEST['username'])) {
            preg_match('/^[A-Za-z0-9]{2,25}$/', $_REQUEST['username'], $matches);
            if ($matches[0]) {
                $fieldlist['UserName'] = $matches[0];
            }
        }

        if (isset($_REQUEST['firstname'])) {
            preg_match('/^[A-Za-z][A-Za-z \-\'.]{2,48}[A-Za-z.]$/', $_REQUEST['firstname'], $matches);
            if ($matches[0]) {
                $fieldlist['Firstname'] = $matches[0];
            }
        }

        if (isset($_REQUEST['lastname'])) {
            preg_match('/^[A-Za-z][A-Za-z \-\'.]{2,48}[A-Za-z.]$/', $_REQUEST['lastname'], $matches);
            if ($matches[0]) {
                $fieldlist['LastName'] = $matches[0];
            }
        }

        if (isset($_REQUEST['pwhash'])) {
            //Password change
            if (isset($_REQUEST['hashalgo'])) {
                if ($_REQUEST['hashalgo'] == 'sha256') {
                    $hashed = true;
                    $pwhash = $_REQUEST['pwhash'];
                } else {
                    $hashed = false;
                }
            } else {
                $hashed = false;
            }
            if ($hashed == false) {
                //Password hasn't had first round of hashing
                $pwhash = hash('sha256', $_REQUEST['pwhash']);
            }
            //Now we need the salt
            try {
                $query = $db->prepare("SELECT Salt FROM Users WHERE UserID = ?");
                $query->bind_param('i', $_SESSION['UserID']);
                $query->execute();
                $result = $query->get_result();
                $salt = $result->fetch_column(0);
                $query->close();
                //Add the salt to the hash
                $pwhash .= $salt;
                $fieldlist['Password'] = hash('sha256', $pwhash);
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>Error</title></head><body>";
                echo "<p>Error getting password salt for existing user: " . $e->getMessage();
                echo "</p></body></html>";
                $db->close();
                exit(); 
            }
        }

        $update_sql = "UPDATE `Users` SET ";
        $fieldtypes = "";
        $values = array();
        foreach ($fieldlist AS $field => $value) {
            if (strlen($fieldtypes) > 0) {
                $update_sql .= ", ";
            }
            $update_sql .= "`$field` = ";
            array_push($values, $value);
            $fieldtypes .= "s";
            if ($field == "Password") {
                $update_sql .= "UNHEX(?)";
            } else {
                $update_sql .= "?";
            }
        }
        $update_sql .= " WHERE `UserID` = ?";
        $fieldtypes .= "i";
        array_push($values, $_SESSION['UserID']);
        try {
            $query = $db->prepare($update_sql);
            $query->bind_param($fieldtypes, ...$values);
            $query->execute();
            $query->close();
            $db->close();
            header("Location: $protocol://$server$webdir/settings.php?updated=true");
            exit();
        } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>Error</title></head><body>";
            echo "<p>Error updating user settings: " . $e->getMessage();
            echo "</p></body></html>";
            $db->close();
            exit(); 
        }
    } else {
        //Something weird has happened (either a needed variable isn't set
        //or the user is trying to edit a different user's profile)
        $db->close();
        if (isset($_REQUEST['UserID'])) {
            //Redirect back to this page for the user
            header("Location: $protocol://$server$webdir/settings.php");
            exit();
        } else {
            //Redirect to the login page
            header("Location: $protocol://$server$webdir/login.php");
            exit();
        }
    }
} else if (isset($_SESSION['UserID'])) {

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistics Server - User Settings</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" language="javascript">
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
                document.getElementById('password').classList.remove('is-valid');
                document.getElementById('password').classList.add('is-invalid');
            } else {
                document.getElementById('password').classList.remove('is-invalid');
                document.getElementById('password').classList.add('is-valid');
            }

            if ((firstname) && (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(firstname)) === null) {
                success = false;
                document.getElementById('firstname').classList.remove('is-valid');
                document.getElementById('firstname').classList.add('is-invalid');
            } else {
                document.getElementById('firstname').classList.remove('is-invalid');
                document.getElementById('firstname').classList.add('is-valid');
            }

            if ((lastname) && (/^[A-Za-z][A-Za-z \-'.]{2,48}[A-Za-z.]$/.exec(lastname)) === null) {
                success = false;
                document.getElementById('lastname').classList.remove('is-valid');
                document.getElementById('lastname').classList.add('is-invalid');
            } else {
                document.getElementById('lastname').classList.remove('is-invalid');
                document.getElementById('lastname').classList.add('is-valid');
            }

            if ((password) && (password.length < 5)) {
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex flex-row-reverse">
                <ul class="navbar-nav">
                <?php
                    if (isset($_SESSION['FirstName'])) {
                        #Someone is logged in
                ?>
                    <?php
                    if ($_SESSION['UserRole'] == "Admin") {
                        ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php">Admin</a>
                    </li>
                    <?php }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="true" href="settings.php">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php?logout=1">(Log Out)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" disabled aria-disabled="true">Welcome, <?php echo $_SESSION['FirstName']; ?></a>
                    </li>
                <?php 
                    } else {
                        #No one is logged in                    }
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container-fluid">
        <h1>User Settings</h1>
        <?php
            if (isset($_REQUEST['updated'])) {
                if ($_REQUEST['updated'] == "true") { ?>
                    <div class="alert alert-success" type="alert">User settings successfully updated.</div>
                <?php }
            }
            //Get user's information to display here
            $query = $db->prepare("SELECT `UserName`, `FirstName`, `LastName` FROM `Users` WHERE `UserID` = ?");
            $query->bind_param("i", $_SESSION['UserID']);
            $query->execute();
            $result = $query->get_result();
            $userinfo = $result->fetch_assoc();
        ?>
        <form action="<?php echo "$protocol://$server$webdir/settings.php" ?>" method="POST" onsubmit="validateForm(event)">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" aria-describedby="usernametips" value="<?php echo $userinfo['UserName']; ?>" required>
                <div class="invalid-feedback">
                    The provided username contains non-alphanumeric characters, is shorter than 2 characters or is more than 25 characters.
                </div>
                <div id="usernametips" class="form-text">
                    Username is not case sensitive.  Please use only alpha-numeric characters and no spaces.
                </div>
            </div>
            <div class="mb-4">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo $userinfo['FirstName']; ?>" required>
                <div class="invalid-feedback">
                    The provided first name includes invalid characters, is less than 2 characters, or is over 50 characters.
                </div>
            </div>
            <div class="mb-4">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo $userinfo['LastName']; ?>" required>
                <div class="invalid-feedback">
                    The provided last name includes invalid characters, is less than 2 characters, or is over 50 characters.
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</labe>
                <input type="password" id="password" class="form-control" aria-describedby="passwordtips">
                <div class="invalid-feedback">
                    The password cannot be extremely short or blank.
                </div>
                <div id="passwordtips" class="form-text">
                    All characters are accepted.  Bare minimum is five characters although you should make this a good password (10+ characters) if this site is going to be publicly accessible, though.
                </div>
            </div>
            <div class="mb-4">
                <label for="passwordcheck" class="form-label">Password (again)</label>
                <input type="password" id="passwordcheck" class="form-control">
                <div class="invalid-feedback">
                    The second copy of the password did not match the first.
                </div>
            </div>

            <input type="hidden" id="pwhash" name="pwhash" value="">
            <input type="hidden" id="hashalgo" name="hashalgo" value="sha256">
            <input type="hidden" name="action" value="updatesettings">
            <input type="hidden" name="userid" value="<?php echo $_SESSION['UserID']; ?>">
            <button class="btn btn-primary" type="submit">Submit Changes</button>
        </form>
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php 
} else {
    #Redirect the user to the login page
    header("Location: $protocol://$server$webdir/login.php?destination=admin_index");
}
?>