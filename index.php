<?php
require "config.php";

try {
    $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
} catch (mysqli_sql_exception $e) {
    echo "Configuration problem";
    echo "It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
    exit();
}

# Do a quick test to make sure that the site's been configured since this is the main page
if (!$db->query("SHOW TABLES LIKE `LibraryInfo`")) {
    #Site's not configured.  Redirect to configuration page.
    $db->close();
    header("Location: $protocol://$server$webdir/admin/configure.php");
    exit();
}

if ($entryrestriction > 0) {
    if ( isset($_SESSION["PHPSESSID"]) && !empty($_SESSION["PHPSESSID"]) ) {
        if (( $_SESSION['UserRole'] == "Edit") || ($_SESSION['UserRole'] == "Admin")) {
            $edit = 1;
            $view = 1;
        } else if ( $_SESSION['UserRole'] == "View") {
            $edit = 0;
            $view = 1;
        } else {
            # This shouldn't happen, but I want to catch anything anomalous
            header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
            exit();
        }
    } else if ($entryrestriction == 1) {
        $edit = 0;
        $view = 1;
    } else {
        #Activity is completely restricted so there's no reason to stay here
        header("Location: $protocol://$server$webdir/login.php");
    }
} else {
    $edit = 1;
    $view = 1;
} 
if ($view == 1) {
    #We'll check for edit later   
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistics Server Administration</title>
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
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex flex-row-reverse">
                <ul class="navbar-nav">
                <?php
                    if (isset($_SESSION['FirstName'])) {
                        #Someone is logged in
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php?logout=1">(Log Out)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Settings</a>
                    </li>
                    <?php
                    if ($_SESSION['UserRole'] == "Admin") {
                        ?>
                    <li class="nav-item">
                        <a href="nav-link" href="admin/index.php">Admin</a>
                    </li>
                    <?php }
                    ?>
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