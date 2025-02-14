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
    <?php if (isset($_REQUEST['action'])) { ?>
    <script type="text/javascript" language="javascript">
            function validateForm(event) {
                var success = true;
                var libraryname = document.getElementById('libraryname').value;
                var address = document.getElementById('address').value;
                var city = document.getElementById('city').value;

                if (/^[A-Za-z][A-Za-z0-9\- '().,]{3,98}[A-Za-z.]$/.exec(libraryname) === null) {
                    success = false;
                    document.getElementById('badln').style.display = "block";
                } else {
                    document.getElementById('badln').style.display = "none";
                }

                if (/^[A-Za-z0-9][A-Za-z0-9 #\'\-.]{4,148}[A-Za-z0-9.]$/.exec(address) === null) {
                    success = false;
                    document.getElementById('badad').style.display = "block";
                } else {
                    document.getElementById('badad').style.display = "none";
                }

                if (/^[A-Za-z][A-Za-z \-'.]{1,73}[A-Za-z.]$/.exec(city) === null) {
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
        <?php } ?>
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
                        <a class="nav-link active" aria-current="true">Library Branches</a>
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
            <?php if (isset($_REQUEST['action'])) {
                if ($_REQUEST['action'] == "newbranch") { ?>
        <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div alert="alert alert-danger" type="alert" id="badln" style="display:none;">The provided library name was too long, too short, or contained unusual characters.</div>
                <label for="libraryname" class="form-label">Library Name</label>
                <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips">
                <div id="librarynametips" class="form-text">
                    This should represent the common way you refer to the branch.  It can be as simple as "Branch Library" or it can be more descriptive.
                </div>
                <div id="badad" class="alert alert-danger" type="alert" style="display:none;">No address was provided, it was extremely long or extremely short, or it contained invalid characters.</div>
                <label for="address" class="form-label">Address</label>
                <input type="text" id="address" name="address" class="form-control">
                <div id="badcity" class="alert alert-danger" type="alert" style="display:none;">No city was provided, it was absurdly short or absurdly long, or it contained invalid characters.</div>
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" class="form-control">
                <input type="hidden" name="formtype" value="newbranch">
                <button class="btn btn-primary" type="submit">Submit Library Branch Information</button>
            </form>
            <?php } else if (($_REQUEST['action'] == "modifybranch") && (isset($_REQUEST['branchid']))) { 
                //Get branch information
                $query = $db->prepare("SELECT `LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`, `FYMonth`+0 AS FYMonth FROM `LibraryInfo` WHERE `LibraryID` = ?");
                $query->bind_param("i", $_REQUEST['branchid']);
                $query->execute();
                $query->store_result();
                $query->bind_result($libraryname, $address, $city, $branch, $fymonth); 
                ?>
                <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div alert="alert alert-danger" type="alert" id="badln" style="display:none;">The provided library name was too long, too short, or contained unusual characters.</div>
                <label for="libraryname" class="form-label">Library Name</label>
                <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips" value="<?php echo $libraryname; ?>">
                <div id="librarynametips" class="form-text">
                    This should represent the common way you refer to the main library.  It can be as simple as "Main Library" or it can be more descriptive ("Harold Washington Library Center of the Chicago Public Library").
                </div>
                <div id="badad" class="alert alert-danger" type="alert" style="display:none;">No address was provided, it was extremely long or extremely short, or it contained invalid characters.</div>
                <label for="address" class="form-label">Address</label>
                <input type="text" id="address" name="address" class="form-control" value="<?php echo $address; ?>">
                <div id="badcity" class="alert alert-danger" type="alert" style="display:none;">No city was provided, it was absurdly short or absurdly long, or it contained invalid characters.</div>
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" class="form-control" value="<?php echo $city; ?>">
                <?php if ($branch == 0) { 
                    //Only main library has fiscal year adjustable ?>
                <label for="fymonth" class="form-label">Fiscal Year Start</label>
                <select class="custom-select" id="fymonth" name="fymonth" aria-describedby="fymonthtips">
                    <option value="1" <?php if ($fymonth == 1) { echo " selected"; } ?>>January</option>
                    <option value="2" <?php if ($fymonth == 2) { echo " selected"; } ?>>February</option>
                    <option value="3" <?php if ($fymonth == 3) { echo " selected"; } ?>>March</option>
                    <option value="4" <?php if ($fymonth == 4) { echo " selected"; } ?>>April</option>
                    <option value="5" <?php if ($fymonth == 5) { echo " selected"; } ?>>May</option>
                    <option value="6" <?php if ($fymonth == 6) { echo " selected"; } ?>>June</option>
                    <option value="7" <?php if ($fymonth == 7) { echo " selected"; } ?>>July</option>
                    <option value="8" <?php if ($fymonth == 8) { echo " selected"; } ?>>August</option>
                    <option value="9" <?php if ($fymonth == 9) { echo " selected"; } ?>>September</option>
                    <option value="10" <?php if ($fymonth == 10) { echo " selected"; } ?>>October</option>
                    <option value="11" <?php if ($fymonth == 11) { echo " selected"; } ?>>November</option>
                    <option value="12" <?php if ($fymonth == 12) { echo " selected"; } ?>>December</option>
                </select>
                <div id="fymonthtips" class="form-text">
                    Choose the month in which your fiscal year begins.  It is assumed to start on the first of the chosen month.
                </div>
                <?php } ?>

                <input type="hidden" name="formtype" value="modifybranch">
                <button class="btn btn-primary" type="submit">Submit Library Branch Changes</button>
            </form>        
            <?php } else { 
                showList($db);
             } ?>
        <?php } else { 
            showList($db);   
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

function showList($db) {
    //There are a couple conditions where this could come up
    //so I'm making it a function
    //Check for success or error conditions
    if (isset($_REQUEST['branchmodified'])) {
        if ($_REQUEST['branchmodified'] == "true") { ?>
            <div class="alert alert-success" type="alert">Library branch successfully modified</div>
        <?php } else { ?>
            <div class="alert alert-danger" type="alert">Library branch modification failed</div>
        <?php }
    } else if (isset($_REQUEST['branchadded'])) {
        if ($_REQUEST['branchadded'] == "true") { ?>
            <div class="alert alert-success" type="alert">Library branch successfully added</div>
        <?php } else { ?>
            <div class="alert alert-danger" type="alert">Unable to add library branch</div>
       <?php }
    } else if (isset($_REQUEST['branchdeleted'])) { ?>
            <div class="alert alert-success" type="alert">Library branch successfully deleted</div>
    <?php } 
     
    //Get a list of library branches
        
    try {
        $result = $db->query("SELECT `LibraryID`, `LibraryName`, `LibraryAddress`, `LibraryCity`, `Branch`, `FYMonth` FROM `LibraryInfo` ORDER BY `Branch` ASC, LibraryName ASC"); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Library Name</th><th>Address</th><th>City</th><th>Branch</th><th>Fiscal Year Start</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($library = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $library['LibraryName']; ?></td>
                    <td><?php echo $library['LibraryAddress']; ?></td>
                    <td><?php echo $library['LibraryCity']; ?></td>
                    <td><?php if ($library['Branch'] == 0) {
                        echo "Main";
                    } else {
                        echo "Branch";
                    } ?></td>
                    <td>
                    <td>
                        <?php if ($library['Branch'] == 0) {
                            echo $library['FYMonth'];
                        } else {
                            echo "-";
                        } ?>
                    </td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="libraries.php?action=modify&libraryid=<?php echo $library['LibraryID']; ?>">Modify Branch</a>
                    <?php if ($library['Branch'] == 1) {
                        //Cannot delete main library ?>
                        <a class="btn btn-danger btn-sm" href="process.php?formtype=deletelibrary&libraryid=<?php echo $library['LibraryID']; ?>" onclick="return confirm('Are you sure you wish to delete the <?php echo $library['LibraryName']; ?> branch?')">Delete User</a>
                    <?php } ?>
                    </td>
                </tr>
            <?php }  ?>
            </tbody>
        </table>

    <?php } catch (mysqli_sql_exception $e) {
            
        echo "<div class=\"alert alert-danger\" type=\"alert\">Error</div>";
        echo "<p>Error retrieving list of libraries: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();  
    } 
}
?>