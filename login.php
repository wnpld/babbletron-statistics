<?php
require "config.php";

#If there is login information to be processed, do that
if (isset($_REQUEST["username"]) && isset($_REQUEST["password"])) {

    #Connect to the database
    try {
        $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
    } catch (mysqli_sql_exception $e) {
        echo "Configuration problem";
        echo "It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
        exit();
    }

    #Check username for safety
    preg_match("/^[A-Za-z0-9]{5,25}$/", $_REQUEST['username'], $matches);
    if (isset($matches[1])) {
        #Get salt for this username if there is any
        $username = $matches[1];
        $query = $db->prepare("SELECT salt FROM Users WHERE UserName = ?");
        $query->bind_param("s", $username);
        if (! $query->execute()) {
            echo "Error executing user search: " . $db->error;
            $query->close();
            $db->close();
            exit();
        }
        $results = $query->get_result();
        if ($results->num_rows > 0) {
            $row = $results->fetch_assoc();
            $salt = $row['salt'];
            $saltedpw = $_REQUEST['pwhash'] . $salt;
            $pwhash = hash('sha256', $saltedpw);
            $query = $db->prepare('SELECT UserID, FirstName, LastName, UserRole FROM Users WHERE UserName = ? AND Password = UNHEX(?)');
            $query->bind_param('ss', $username, $pwhash);
            if (! $query->execute()) {
                echo "Error executing password check: " . $db->error;
                $query->close();
                $db->close();
                exit();
            }
            $results = $query->get_result();
            if ($results->num_rows > 0) {
                $row = $results->fetch_assoc();
                #Start session
                $options = array("cookie_path" => $webdir);
                session_start($options);
                $_SESSION['UserID'] = $row['UserID'];
                $_SESSION['FirstName'] = $row['FirstName'];
                $_SESSION['LastName'] = $row['LastName'];
                $_SESSION['UserRole'] = $row['UserRole'];
            } else {
                #Password was wrong
                $query->close();
                $db->close();
                header("Location: $protocol://$server$webdir/login.php?nomatch=credentials");
                exit();
            }
        } else {
            #Invalid username
            $query->close();
            $db->close();
            header("Location: $protocol://$server$webdir/login.php?nomatch=credentials");
        }
    }
}

if ( isset($_SESSION["PHPSESSID"]) && !empty($_SESSION["PHPSESSID"]) ) {
    if ( isset($_REQUEST['logout'])) {
        session_destroy();
        header("Location: $protocol://$server$webdir/login.php");
        exit();
    }
    if ( isset($_REQUEST['destination'])) {
        $destination = $_REQUEST['destination'];
        if ($destination == "nomain") {
            header("Location: $protocol://$server$webdir/admin/configure.php?nomain=1");
            exit();
        } else {
            str_replace("_", "/", $destination);
            $destination .= ".php";
            header("Location: $protocol://$server$webdir/$destination");
            exit();
        }
    } else {
        header("Location: $protocol://$server$webdir/index.php");
        exit();
    }
} else {
    #Show a login screen 
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="true" href="#">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="container-fluid">
            <h1>Login</h1>
            <form action="login.php" method="POST"  onsubmit="hashPassword(event)">
                <?php
                    if (isset($_REQUEST['nomatch'])) {
                        if ($_REQUEST['nomatch'] == "credentials") { ?>
                            <div class="alert alert-danger" type="alert">The provided password and/or username was incorrect.</div>
                        <?php } else if ($_REQUEST['nomatch'] == "privilege") { ?>
                            <div class="alert alert-warning" type="alert">You have been logged in, but you do not have the appropriate rights to access the page you wanted to go to.</div>
                        <?php }
                    }
                ?>
                <div class="mb-3">
                    <label for="username" class="form-label">User Name</label>
                    <input type="text" class="form-control" id="username" name="username">
                    </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password">
                </div>
                <input type="hidden" id="pwhash" name="pwhash" value="">
            <?php 
                if (isset($_REQUEST['destination'])) {
                    $destination = $_REQUEST['destination'];
                    if ($destination == 'nomain') { ?>
                        <input type="hidden" name="destination" value="nomain">
                <?php }
                }
            ?>
            <button class="btn btn-primary" type="submit">Log In</button> 
            </form>
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
    <script>
        async function hashPassword(event) {
            //Hash password before submitting.
            //The hash gets hashed again with salt on the server side
            //but this obscures the password more in the case of an unencrypted connection
            var password = Document.getElementById('password').value;
            
            //Encode password
            const encodedpw = new TextEncoder().encode(password);

            //Hash the password
            const hashBuffer = await crypto.subtle.digest('SHA-256', encodedpw);

            //Convert ArrayBuffer into an Array
            const hashArray = Array.from(new Uint8Array(hashBuffer));

            //Convert bytes into hex
            const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');

            //Write hashed password to field in form
            Document.getElementById('pwhash').value = hashHex;

            //Submit form
            return true;
        }
    </script>
  </body>
</html>
<?php }

?>