<?php
session_start();
require "../config.php";

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if (!$_SESSION['UserRole'] == "Admin") {
        #Send to the main site page
        header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
        exit();
    }
    #Show Admin restricted information

    try {
        $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Configuration Problem</title></head><body>";
        echo "<h1>Configuration problem</h1>";
        echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
        echo "</p></body></html>";
        exit();
    }

    if (!isset($_REQUEST['action'])) {
        #If action is set we are creating, editing, or deleting an entry
        #Get a list of all closings and put them into JSON for dynamic display
        try {
            $closings = $db->query('SELECT lc.ClosingID, li.LibraryName, lc.DateClosed, lc.HoursClosed, lc.ClosingType, IF((SELECT FYMonth+0 FROM LibraryInfo WHERE Branch = 0) != 1 AND MONTH(lc.DateClosed) >= (SELECT FYMonth+0 FROM LibraryInfo WHERE Branch = 0), YEAR(lc.DateClosed) + 1, YEAR(lc.DateClosed)) AS FiscalYear FROM LibraryClosings lc LEFT JOIN LibraryInfo li ON lc.LibraryID = li.LibraryID ORDER BY lc.DateClosed ASC');
            $fiscalyears = array();
            $libraries = array();
            if ($closings->num_rows > 0) {
                $json_array = "var closings: [";
                while ($closinginfo = $closings->fetch_assoc()) {
                    $json_array .= "{";
                    $json_array .= "\"id\": \"" . $closinginfo['ClosingID'] . "\",";
                    $json_array .= "\"fy\": \"" . $closinginfo['FiscalYear'] . "\",";
                    if (!in_array($closinginfo['FiscalYear'], $fiscalyears)) {
                        array_push($fiscalyears, $closinginfo['FiscalYear']);
                    }
                    $json_array .= "\"date\": \"" . $closinginfo['DateClosed'] . "\",";
                    if ($closinginfo['LibraryName'] == null) {
                        $json_array .= "\"library\": \"All Libraries\",";
                    } else {
                        if (!in_array($closinginfo['LibraryName'], $libraries)) {
                            array_push($libraries, $closinginfo['LibraryName']);
                        }
                        $json_array .= "\"library\": \"" . $closinginfo['LibraryName'] . "\",";
                    }
                    if ($closinginfo['HoursClosed'] < 0) {
                        #This is extended hours, not a closing
                        $json_array .= "\"type\": \"Extra Hours Open\",";
                        $json_array .= "\"hours\": " . abs($closinginfo['HoursClosed']) . ",";
                    } else {
                        $json_array .= "\"type\": \"Hours Closed\",";
                        $json_array .= "\"hours\": " . $closinginfo['HoursClosed'] . ",";
                    }
                    $json_array .= "\"reason\": \"" . $closinginfo['ClosingType'] . "\"},";

                }
                $json_array .= "]";
            } else {
                #If there aren't any, just put 0 as the first element in the array
                $json_array = "closings: [ 0 ]";
            }
            $closings->close();
        } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>SQL Query Error</title></head><body>";
            echo "<h1>Error Retrieving Closed Dates</h1>";
            echo "<p>There was an error retrieving the list of closed dates: " . $e->getMessage();
            echo "</p></body></html>";
            exit();        
        }
    } else if (($_REQUEST['action'] == "update") or ($_REQUEST['action'] == "insert")) {
        // Create a proxy value to differentiate actions
        if ($_REQUEST['action'] == "update") {
            $update = true;
        } else {
            $update = false;
        }
        if ($update) {
            // Update a record based on a completed form
            if (isset($_REQUEST['id']) and is_numeric($_REQUEST['id'])) {
                $id = $_REQUEST['id'];
            } else {
                $id = null;
            }
        }

        if (isset($_REQUEST['date'])) {
            $date = $_REQUEST['date'];
        } else {
            $date = null;
        }

        if (isset($_REQUEST['hours']) and is_numeric($_REQUEST['hours'])) {
            $hours = $_REQUEST['hours'];
        } else {
            $hours = null;
        }

        if (isset($_REQUEST['type'])) {
            #The way that a closed or extended hours event are differentiated
            #is that an extended hours event has a negative value
            if ($_REQUEST['type'] == "extended") {
                $hours = 0 - $hours;
            }
        }

        if (isset($_REQUEST['library'])) {
            if ($_REQUEST['library'] == "all") {
                $library = null;
            } else {
                if (is_numeric($_REQUEST['library'])) {
                    $library = $_REQUEST['library'];
                } else {
                    $library = "error";
                }
            }
        } else {
            $library = "error";
        }

        if (isset($_REQUEST['reason'])) {
            preg_match('/^[A-Za-z ]+$/', $_REQUEST['reason'], $matches);
            if ($matches[0]) {
                $reason = $matches[0];
            } else {
                $reason = null;
            }
        } else {
            $reason = null;
        }

        if ($update) {
            if (($id == null) or ($date == null) or ($hours == null) or ($library == "error") or ($reason == null)) {
                $fail = true;
            } else {
                $fail = false;
            }
        } else {
            if (($date == null) or ($hours == null) or ($library == "error") or ($reason == null)) {
                $fail = true;
            } else {
                $fail = false;
            }
        }
        if ($fail) {
            if ($update) {
                echo "<html><head><title>Error Updating Closing/Extended Hours</title></head><body>";
                echo "<h1>Closing/Extended Hours Update Error - Incomplete data</h1>";
            } else {
                echo "<html><head><title>Error Adding Closing/Extended Hours</title></head><body>";
                echo "<h1>New Closing/Extended Hours Submission Error - Incomplete data</h1>";
            }
            echo "<p>The data submitted to this form for processing was incomplete.  This is what was not received:<ul>";
            if ($update) {
                if ($id == null) {
                    echo "<li>Closing ID</li>";
                }
            }
            if ($date == null) {
                echo "<li>Date</li>";
            }
            if ($hours == null) {
                echo "<li>Hours</li>";
            }
            if ($library == "error") {
                echo "<li>Library</li>";
            }
            if ($reason == null) {
                echo "<li>Reason for closing/Extension";
            }
            echo "</ul></p></body></html>";
            exit();  
        } else {
            if ($update) {
                try {
                    $query = $db->prepare("UPDATE LibraryClosings SET LibraryID = ?, DateClosed = ?, HoursClosed = ?, ClosingType = ? WHERE ClosingID = ?");
                    $query->bind_param('isisi', $library, $date, $hours, $reason, $id);
                    $query->execute();
                    $query->close();
                    header("Location: $protocol://$server$webdir/admin/closings.php");
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>SQL Query Error</title></head><body>";
                    echo "<h1>Error Updating LibraryClosings table</h1>";
                    echo "<p>There was an error updating the LibraryClosings table: " . $e->getMessage();
                    echo "</p></body></html>";
                    exit();  
                }
            } else {
                try {
                    $query = $db->prepare("INSERT INTO LibraryClosings (LibraryID, DateClosed, HoursClosed, ClosingType) VALUES (?, ?, ?, ?)");
                    $query->bind_param('isis', $library, $date, $hours, $reason);
                    $query->execute();
                    $query->close();
                    header("Location: $protocol://$server$webdir/admin/closings.php");
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>SQL Query Error</title></head><body>";
                    echo "<h1>Error Adding to LibraryClosings table</h1>";
                    echo "<p>There was an error adding to the LibraryClosings table: " . $e->getMessage();
                    echo "</p></body></html>";
                    exit();  
                }   
            }
        }

    } else if ($_REQUEST['action'] != "delete") {
        // Action should be either edit or add
        // Both require building out a form with some basic information
        try {
            $librarylist = $db->query("SELECT LibraryID, LibraryName FROM LibraryInfo");
            $libraries = array();
            while ($item = $librarylist->fetch_assoc()) {
                array_push($libraries, array('id' => $item['LibraryID'], 'library' => $item['LibraryName']));
            }
        } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>SQL Query Error</title></head><body>";
            echo "<h1>Error Retrieving List of Libraries</h1>";
            echo "<p>There was an error retrieving the list of libraries: " . $e->getMessage();
            echo "</p></body></html>";
            exit();  
        }

        try {
            // Just in case it's changed in the future, dynamically get a list of the 
            // Enum values available in LibraryClosings ClosingType
            $closingtypedata = $db->query("SHOW COLUMNS FROM LibraryClosings LIKE 'ClosingType'");
            $closingtypeinfo = $closingtypedata->fetch_assoc();
            $closingtypestring = $closingtypeinfo['Type'];
            preg_match('/enum\((.*)\)/', $closingtypestring, $matches);
            $closingtypestring = str_replace("'", "", $matches[1]);
            $closingtypes = explode(",", $closingtypestring);
        } catch (mysqli_sql_exception $e) {
            echo "<html><head><title>SQL Query Error</title></head><body>";
            echo "<h1>Error Retrieving List of Closing Types</h1>";
            echo "<p>There was an error retrieving the list of closing types: " . $e->getMessage();
            echo "</p></body></html>";
            exit();  
        }

        $closeinfo = array();
        if ($_REQUEST['action'] == 'edit') {
            try {
                $query = $db->prepare("SELECT * FROM LibraryClosings WHERE ClosingID = ?");
                $query->bind_param('i', $_REQUEST['id']);
                $query->execute();
                $result = $query->get_result();
                $closeinfo = $result->fetch_assoc();
                $query->close();
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>SQL Query Error</title></head><body>";
                echo "<h1>Error Retrieving Closure Detail</h1>";
                echo "<p>There was an error retrieving information about a closure: " . $e->getMessage();
                echo "</p></body></html>";
                exit(); 
            }
        }

    } else {
        // Action is delete, so delete the record and then redirect back to this page without an action
        if (isset($_REQUEST['id'])) {
            try {
                $query = $db->prepare('DELETE FROM LibraryClosings WHERE LibraryID = ?');
                $query->bind_param('i', $_REQUEST['id']);
                $query->execute();
                header("Location: $protocol://$server$webdir/closings.php");
                exit;
            } catch (mysqli_sql_exception $e) {
                echo "<h1>Error Retrieving Closure Detail</h1>";
                echo "<p>There was an error retrieving information about a closure: " . $e->getMessage();
                echo "</p></body></html>";
                exit(); 
            }
        } else {
            // Just redirect back to this page
            header("Location: $protocol://$server$webdir/closings.php");
            exit;
        }
    }
    if (!isset($_REQUEST['action'])) {
?>
<!doctype html>
<html lang="en">
  <head>
    <style>
        label {
            font-weight: bold;
        }
    </style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Closings and Extended Hours</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function buildRow(id, date, library, type, hours, reason) {
            const newrow = document.createElement('tr');
            const cellone = document.createElement('td');
            cellone.textContent = date;
            newrow.appendChild(cellone);
            const celltwo = document.createElement('td');
            celltwo.textContent = library;
            newrow.appendChild(celltwo);
            const cellthree = document.createElement('td');
            cellthree.textContent = type;
            newrow.appendChild(cellthree);
            const cellfour = document.createElement('td');
            cellfour.textContent = hours;
            newrow.appendChild(cellfour);
            const cellfive = document.createElement('td');
            cellfive.textContent = reason;
            newrow.appendChild(cellfive);
            const cellsix = document.createElement('td');
            const editbutton = document.createElement('a');
            editbutton.classList.add('btn');
            editbutton.classList.add('btn-primary');
            editbutton.setAttribute('href', 'closings.php?action=edit&id=' + id);
            editbutton.setAttribute('role', 'button');
            editbutton.textContent = "Edit";
            cellsix.appendChild(editbutton);
            const deletebutton = document.createElement('a');
            deletebutton.classList.add('btn');
            deletebutton.classList.add('btn-danger');
            deletebutton.setAttribute('href', 'closings.php?action=delete&id=' + id);
            deletebutton.setAttribute('role', 'button');
            deletebutton.setAttribute('onclick', 'return confirm("Are you sure you want to delete this event?")');
            deletebutton.textContent = "Delete";
            cellsix.appendChild(deletebutton);
            newrow.appendChild(cellsix);
            return newrow;
        }

        function rebuildTable() {
            <?php
                // Drop in the array built earlier
                echo $json_array; ?>;
            var fiscalyear = document.getElementById("fiscalyear").value;
            var library = document.getElementById("library").value;
            const closingbody = document.getElementById("closing-body");
            closingbody.innerHTML = '';
            if (closings[0] == 0) {
                // There's nothing to filter
                const tablerow = document.createElement('tr');
                const unicell = document.createElement('td');
                unicell.setAttribute('colspan', '6');
                unicell.textContent = "No closing dates have been set.";
                tablerow.appendChild(unicell);
                closingbody.appendChild(tablerow);
            } else {
                rowcount = 0;
                closings.forEach((closing) => {
                    if ((fiscalyear == "all") && (library == "all")) {
                        // Don't check values of either field
                        const closingrow = buildRow(closing.id, closing.date, closing.library, closing.type, closing.hours, closing.reason);
                        closingbody.appendChild(closingrow);
                        rowcount++;
                    } else if (fiscalyear == "all") {
                        // Check library field
                        if (closing.library == library) {
                            const closingrow = buildRow(closing.id, closing.date, closing.library, closing.type, closing.hours, closing.reason);
                            closingbody.appendChild(closingrow);
                            rowcount++;
                        }
                    } else if (library == "all") {
                        // Check fiscal year field
                        if (closing.fy == fiscalyear) {
                            const closingrow = buildRow(closing.id, closing.date, closing.library, closing.type, closing.hours, closing.reason);
                            closingbody.appendChild(closingrow);
                            rowcount++;
                        }
                    } else {
                        // Check both fields
                        if ((closing.fy == fiscalyear) && (closing.library == library)) {
                            const closingrow = buildRow(closing.id, closing.date, closing.library, closing.type, closing.hours, closing.reason);
                            closingbody.appendChild(closingrow);
                            rowcount++;
                        }
                    }
                });
                if (rowcount == 0) {
                    // Nothing matched the criteria, so print a row that says that
                    const emptyrow = document.createElement('tr');
                    const emptycell = document.createElement('td');
                    emptycell.setAttribute('colspan', '6');
                    emptycell.textContent = "The selected combination of fiscal year and library had no closings.";
                    emptyrow.appendChild(emptycell);
                    closingbody.appendChild(emptyrow);
                }
            }

        }
    </script>
  </head>
  <body onload="rebuildTable()">
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
                        <a class="nav-link active" aria-current="true">Closings/Extended Hours</a>
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
            <h1>Branch Closings &amp; Extended Hours</h1>
            <div class="row">
                <div class="col">
                    <label for="fiscalyear" class="form-label">Fiscal Year</label>
                    <?php if (count($fiscalyears) == 0) { ?>
                        <select class="form-select" aria-label="Choose a fiscal year to limit results to" id="fiscalyear" disabled>
                    <?php } else { ?>
                    <select class="form-select" aria-label="Choose a fiscal year to limit results to" id="fiscalyear" onchange="rebuildTable()">
                    <?php } ?>
                        <option value="all" selected>All</option>
                    <?php foreach ($fiscalyears as $fy) { ?>
                        <option value="<?php echo $fy; ?>"><?php echo $fy; ?></option>
                    <?php } ?> 
                    </select>
                </div>
                <div class="col">
                    <label for="libraries" class="form-label">Libraries</label>
                    <?php if (count($libraries) == 0) { ?>
                        <select class="form-select" aria-label="Choose a library to limit results to" id="library" disabled>
                    <?php } else { ?>
                        <select class="form-select" aria-label="Choose a library to limit results to" id="library" onchange="rebuildTable()">
                    <?php } ?>
                        <option value="all" selected>All Closings</option>
                    <?php if (count($libraries) > 0) { ?>
                        <option value="All Libraries">All Libraries</option>
                    <?php } ?>
                    <?php foreach ($libraries as $library) { ?>
                        <option value="<?php echo $library; ?>"><?php echo $library; ?></option>
                    <?php } ?>
                        </select>
                </div>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Library</th>
                        <th>Kind of Event</th>
                        <th>Number of Hours</th>
                        <th>Reason</th>
                        <th>Edit/Delete</th>
                    </tr>
                </thead>
                <tbody id="closing-body">
                    <?php 
                        if (count($fiscalyears) == 0) { ?>
                    <tr>
                        <td colspan="6">
                            No closing dates have been set.
                        </td>
                    </tr>
                        <?php } else { ?>

                        <?php }
                    ?>
                </tbody>
            </table>
            <p><a class="btn btn-primary" href="closings.php?action=add">Add New Closed/Extended Hours</a></p>
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php 
    } else {
        // Insert/Update form ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Closings and Extended Hours</title>
    <style>
        label {
            font-weight: bold;
        }
    </style>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $jquery; ?>"></script>
    <link id="bdsp-css" href="<?php echo $datepickercss; ?>" rel="stylesheet">
    <script src="<?php echo $datepickerjs; ?>"></script>
    <script type="text/javascript">
        function validateForm(event) {
            var success = true;
            var date = document.getElementById('date').value;
            var hours = document.getElementById('hours').value;

            if (/^2\d{3}-[01][0-9]-[0-3][0-9]$/.exec(date) === null) {
                success = false;
                document.getElementById('date').classList.remove('is-valid');
                document.getElementById('date').classList.add('is-invalid');
            } else {
                document.getElementById('date').classList.remove('is-invalid');
                document.getElementById('date').classList.add('is-valid');
            }

            if(/^\d{1,2}\.{0,1}\d{0,1}$/.exec(hours) === null) {
                success = false;
                document.getElementById('hours').classList.remove('is-valid');
                document.getElementById('hours').classList.add('is-invalid');
            } else {
                if (hours > 24) {
                    success = false;
                    document.getElementById('hours').classlist.remove('is-valid');
                    document.getElementById('hours').classlist.add('is-invalid');
                } else {
                    document.getElementById('hours').classList.remove('is-invalid');                    
                    document.getElementById('hours').classList.add('is-valid');
                }
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
                        <a class="nav-link active" aria-current="true">Closings/Extended Hours</a>
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
            <?php if ($_REQUEST['action'] == "edit") { ?>
                <h1>Update Closed Hours/Hour Extension</h1>
            <?php } else { ?>
                <h1>Add Closed Hours/Hour Extension</h1>
            <?php } ?>
            <form action="<?php echo $protocol . '://' . $server . $webdir . '/admin/closings.php'; ?>" method="POST"  onsubmit="validateForm(event)">
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <div class="input-group date" data-provide="datepicker">
                        <input id="date" name="date" class="form-control" type="text" value="<?php if (isset($closeinfo)) {
                            echo $closeinfo['DateClosed'];
                        } else {
                            echo date('Y-m-d');
                         } ?>"  aria-describedby="datetips">
                        <div class="input-group-addon">
                            <span class="gylphicon gylphicon-th"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        The provided date was not in the proper format: yyyy-mm-dd.
                    </div>
                    <div id="datetips" class="form-text">
                        The date when the library was closed or had extended hours.
                    </div>
                </div>
                <div class="mb-3">
                    <label for="library" class="form-label">Library</label>
                    <select class="form-select" aria-describedby="librarytips" id="library" name="library">
                        <option value="all" <?php if (isset($closeinfo)) {
                            if ($closeinfo['LibraryID'] == null) { echo " selected"; } }  ?>>All</option>
                        <?php foreach ($libraries as $libinfo) { ?>
                            <option value="<?php echo $libinfo['id']; ?>" <?php if (isset($closeinfo)) {
                                if ($closeinfo['LibraryID'] == $libinfo['id']) {
                                    echo " selected";
                                } 
                            } ?>><?php echo $libinfo['library']; ?></option>
                        <?php } ?>
                    </select>
                    <div id="librarytips" class="form-text">
                        The library affected by the closure or extended hours.  If all libraries in the system are affected, choose "all".
                    </div>
                </div>
                <?php 
                    if (isset($closeinfo)) {
                        if ($closeinfo['HoursClosed'] < 0) {
                            $closeinfo['Type'] = "Extended";
                            $closeinfo['HoursClosed'] = abs($closeinfo['HoursClosed']);
                        } else {
                            $closeinfo['Type'] = "Closed";
                        }
                    }
                ?>
                <div class="mb-3">
                    <label for="type" class="form-label" aria>Closed or Extended Hours</label>
                    <select id="type" name="type" class="form-select" aria-describedby="typetips">
                        <option value="closed" <?php if (isset($closeinfo)) {
                            if ($closeinfo['Type'] == "Closed") {
                                echo " selected";
                            }
                        } ?>>Closed Hours</option>
                        <option value="extended" <?php if (isset($closeinfo)) {
                            if ($closeinfo['Type'] == "Extended") {
                                echo " selected";
                            }
                        } ?>>Extended Hours</option>
                    </select>
                    <div id="typetips" class="form-text">
                        The type of change in hours; are these hours closed or extra hours open.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="hours" class="form-label">Number of Hours to Cut or Add</label>
                    <input type="number" min="0" max="24" id="hours" name="hours" class="form-control" size="2" step=".5" <?php if (isset($closeinfo)) { echo "value=\"" . $closeinfo['ClosedHours'] . "\""; } ?> aria-describedby="hourstips">
                    <div class="invalid-feedback">
                        The time provided was either not numeric or was greater than 24 hours.
                    </div>
                    <div id="hourtips" class="form-text">
                        The number of hours removed from or added to the regular schedule for the chosen day.  Time can be selected in half-hour increments.
                    </div>                    
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Closing/Extra Hours</label>
                    <select id="reason" name="reason" class="form-select" aria-describedby="reasontips">
                    <?php foreach ($closingtypes as $reason) { ?>
                        <option value="<?php echo $reason; ?>" <?php if (isset($closeinfo)) {
                            if ($closeinfo['ClosingType'] == $reason) {
                                echo " selected";
                            } } ?>><?php echo $reason; ?></option>
                    <?php } ?>
                    </select>
                    <div id="hourstips" class="form-text">
                        The reason for the closed hours or extended hours.  Nothing matches, choose "other."
                    </div>
                </div>
                
                <?php if (isset($_REQUEST['id'])) { ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo $closeinfo['ClosingID']; ?>">
                <?php } else { ?>
                    <input type="hidden" name="action" value="insert">
                <?php } ?>
                <button class="btn btn-primary" type="submit">Submit Closed/Extended Hours</button>
            </form>
        </div>
    </main>
    <script src="<?php echo $bootstrapdir; ?>/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $.fn.datepicker.defaults.format = "yyyy-mm-dd";
        $('.date').datepicker({ 
            startDate: "-7d" 
        });
    </script>
  </body>
</html>
<?php  }
} else {
    #Redirect the user to the login page
    header("Location: $protocol://$server$webdir/login.php?destination=admin_index");
    exit();
}
?>