<?php
session_start();
require "../config.php";

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if (!$_SESSION['UserRole'] == "Admin") {
        # Send to the main site page
        header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
        exit();
    }

    if (isset($_REQUEST['action'])) {
        preg_match('/^show([a-z]+)$/', $_REQUEST['action'], $matches);
        if (isset($matches[1])) {
            $display = $matches[1];
        } else {
            if ($_REQUEST['action'] = "createmanualdb") {
                # Create database tables for manual entry
                $fieldcount = $_REQUEST['fieldcount'];
                $category = $_REQUEST['sectionid'];
                # Other fields are fieldname(x), fieldtype(x), and fieldoptions(x)
                # where (x) is a number between 0 and fieldcount-1
                # fieldtype determines data type which can be:
                # * yeartype (year)
                # * fixed list (enum) - used for month
                # * adjustable list (create a lookup table)
                # * text (varchar)
                # * number (tinyint, smallint, int, or bigint)
                # * currency (decimal 5,2 or 8,2 or 11,2)
            }
        }
        
    } else {
        // If no action is set, display will be default
        $display = null;
    }

    #Connect to the database
    try {
        $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Configuration problem</title></head><body>";
        echo "<h1>Configuration problem</h1>";
        echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage() . "</p></body></html>";
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
    <script src="sourcemgmt.js"></script>
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
                        <a class="nav-link active" aria-current="true">Data Sources</a>
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
            <?php if ($display == "add") {
                if (isset($_REQUEST['sourcetype'])) {
                    if ($_REQUEST['sourcetype'] == "manual") { ?>
                        <h1>Create a Manual Entry Data Source</h1>
                        <form id="dsourceoptions" action="javascript:void(0);">
                            <div class="form-group row mb-3">
                                <label for="sectionselect" class="col-sm-2 col-form-label">Select a Category</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="sectionselect">
                                        <option value="" disabled selected hidden>Select a category...</option>
                                    <?php 
                                        try {
                                            $query = $db->prepare("SELECT UserCategoryID, UserCategoryName FROM UserCategories");
                                            if (! $query->execute()) {
                                                echo "<html><head><title>Error</title></head><body>";
                                                echo "<p>Error executing user search: " . $db->error;
                                                echo "<p></body></html>";
                                                $query->close();
                                                $db->close();
                                                exit();
                                            }
                                            $results = $query->get_result();
                                            do {
                                                echo "<option value=\"" . $row['UserCategoryID'] . "\">" . $row['UserCategoryName'] . "</option>";
                                            } while ($row = $results->fetch_assoc());
                                        } catch (mysqli_sql_exception $e) {
                                            echo "<html><head><title>Error</title></head><body>";
                                            echo "<p>Error checking user account information: ". $e->getMessage();
                                            echo "</p></body></html>";
                                            $db->close();
                                            exit();
                                        }

                                    ?>                                    
                                    </select>
                                    <small id="sectionhelp" class="form-text text-muted">You need to select a primary category which your data is for.  It is possible to use data from one category different category's report.</small>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <legend class="col-form-label col-sm-2 pt-0">Select an Input Frequency</legend>
                                <div class="col-sm-10">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="freqradio" id="freqradiomonthly" value="monthly" checked>
                                        <label class="form-check-label" for="freqradiomonthly">
                                            Monthly
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="freqradio" id="freqradioannual" value="annual">
                                        <label class="form-check-label" for="freqradioannual">
                                            Annual
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="freqradio" id="freqradiostatic" value="static">
                                        <label class="form-check-label" for="freqradiostatic">
                                            Static (information that reflects a current state, like an address or number of staff)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" onclick="startManualForm()">Start Data Source Creation</button>
                        </form>
                        <form style="display:none;" id="dsourceform" action="datasources.php" method="POST" onsubmit="return validateForm()">
                            <fieldset id="dsourcelist">
                                <legend></legend>
                                <h2 id="catheading"></h2>
                                <div class="form-group">
                                    <label for="sourcename">Enter the name for your data source initiative:</label>
                                    <input type="text" name="sourcename" id="sourcename" required>
                                </div>
                                <div class="card mt-3 mr-3 ml-3">
                                    <div class="card-body">
                                        <div class="row" id="month">
                                            <div class="col form-group">
                                                <label for="monthname" class="form-label">Field Name</label>
                                                <input type="text" size="50" id="monthname" name="fieldname0" value="Month" disabled>
                                            </div>
                                            <div class="col form-group">
                                                <label for="monthtype" class="form-label">Field Type</label>
                                                <select id="monthtype" class="form-select" name="fieldtype0" disabled>
                                                <option value="fixedlist">List (Fixed)</option>
                                                </select>
                                            </div>
                                            <div class="col form-group">
                                                <label for="monthoptions" class="form-label">Field Options</label>
                                                <textarea name="fieldoptions0" id="fieldoptions0" disabled>January, February, March, April, May, June, July, August, September, October, November, December</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-3 mr-3 ml-3">
                                    <div class="card-body">
                                        <div class="row" id="year">
                                            <div class="col form-group">
                                                <label for="yearname" class="form-label">Field Name</label>
                                                <input type="text" size="50" id="yearname" name="fieldname1" value="Year" disabled>
                                            </div>
                                            <div class="col form-group">
                                                <label for="yeartype" class="form-label">Field Type</label>
                                                <select id="yeartype" name="fieldtype1" disabled>
                                                    <option>Year</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="mb-3 mt-3">
                                <input type="button" class="btn btn-primary" onclick="addFormField()" value="Add New Field">
                                <input type="hidden" name="sectionid" id="catid" value="">
                                <input type="hidden" name="fieldcount" id="fieldcount" value="2">
                                <input type="hidden" name="action" value="createmanualdb">
                            </div>
                            <button class="btn btn-primary btn-lg" type="submit">Submit Data Source Fields</button>
                        </form>


                    <?php } else if ($_REQUEST['sourcetype'] == "file") {

                    } else if ($_REQUEST['sourcetype'] == "db") {

                    }
                } else {

                }
            } else if ($display == "edit") {

            } else if ($display == "delete") {

            } else { ?>
            <h1>Data Source Management</h1>
            <div class="col">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Add a Data Source</h2>
                            <form action="datasources.php" method="POST">
                                <select class="form-control" name="sourcetype" id="sourcetype">
                                    <option value="manual">Manually Entered</option>
                                    <option value="file">Collected from File</option>
                                    <option value="db">Collected from Database</option>
                                </select>
                                <input type="hidden" name="action" value="showadd">
                                <button class="btn btn-primary" type="submit">Start Data Source Creation</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Edit Data Source</h2>
                            <form action="datasources.php" method="POST">
                                ???
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Retire/Delete Source</h2>
                            <form action="datasources.php" method="POST">
                                ???
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php 
} else {
    # Redirect the user to the login page
    header("Location: $protocol://$server$webdir/login.php?destination=admin_index");
    exit();
}
?>
