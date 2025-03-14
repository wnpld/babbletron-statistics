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

            <?php if ($_REQUEST['action'] == "modify")  { ?>
            function toggleCalendarEntry(calendar, state) {
                calendarfield = document.getElementById(calendar);
                if (state == "off") {
                    calendarfield.removeAttribute('disabled');
                    if (calendar == "legallibraryname-calendar") {
                        document.getElementById('legallibraryname-checkbox').removeAttribute('disabled');
                    } else if (calendar == "address-calendar") {
                        document.getElementById('address-checkbox').removeAttribute('disabled');
                    } else if (calendar == "squarefootage-calendar") {
                        document.getElementById('squarefootage-change-reason').removeAttribute('disabled');
                        document.getElementById('squarefootage-change-reson').setAttribute('required', '');
                    }
                } else {
                    calendarfield.setAttribute('disabled', '');
                    if (calendar == "legallibraryname-calendar") {
                        document.getElementById('legallibraryname-checkbox').setAttribute('disabled', '');
                    } else if (calendar == "address-calendar") {
                        document.getElementById('address-checkbox').setAttribute('disabled', '');
                    } else if (calendar == "squarefootage-calendar") {
                        document.getElementById('squarefootage-change-reason').setAttribute('disabled', '');
                        document.getElementById('squarefootage-change-reason').removeAttribute('required', '');
                    }
                }
            }
            
            function addQualifier(field) {
                const fieldblock = document.getElementById(field + '-block');
                const qualifierset = document.createElement('div');
                const radioset = document.createElement('div');
                radioset.classList.add('form-check');
                radioset.classList.add('form-check-inline');
                radioset.classList.add('mb-3');

                const correctionradio = document.createElement('input');
                correctionradio.classList.add('form-check-input');
                correctionradio.setAttribute('type', 'radio');
                correctionradio.setAttribute('name', field + '-radio');
                correctionradio.setAttribute('id', field + '-correction-radio');
                correctionradio.setAttribute('value', 'correction');
                correctionradio.setAttribute('onclick', 'toggleCalendarEntry("' + field + '-calendar", "off")');
                correctionradio.checked = true;
                radioset.appendChild(correctionradio);

                const correctionlabel = document.createElement('label');
                correctionlabel.classList.add('form-check-label');
                correctionlabel.setAttribute('for', field + '-correction-radio');
                correctionlabel.textContent = "Correction only";
                radioset.appendChild(correctionlabel);

                const changeradio = document.createElement('input');
                changeradio.classList.add('form-check-input');
                changeradio.setAttribute('type', 'radio');
                changeradio.setAttribute('name', field + '-radio')
                changeradio.setAttribute('id', field + '-change-radio');
                changeradio.setAttribute('value', 'change');
                correctionradio.setAttribute('onclick', 'toggleCalendarEntry("' + field + '-calendar", "on")');
                radioset.appendChild(changeradio);

                const changelabel = document.createElement('label');
                changelabel.classList.add('form-check-label');
                changelabel.textContent = "Formal Change";
                radioset.appendChild(changelabel);

                qualifierset.appendChild(radioset);

                if ((field == "legallibraryname") or (field == "address")) {
                    //There's an additional qualifier for this
                    const officialcheck = document.createElement('input');
                    officialcheck.classList.add('form-check-input');
                    officialcheck.setAttribute('type', 'checkbox');
                    officialcheck.setAttribute('name', field + '-checkbox');
                    officialcheck.setAttribute('id', field + '-checkbox');
                    officialcheck.setAttribute('value', 'Yes');
                    officialcheck.setAttribute('disabled', '');
                    radioset.appendChild(officialcheck);

                    const officialchecklabel = document.createElement('label');
                    officialchecklabel.classList.add('form-check-label');
                    if (field == "legallibraryname") {
                        officialchecklabel.textContent = "Official Change";
                    } else {
                        officialchecklabel.textConent = "Physical Change";
                    }
                    
                    officialchecklabel.setAttribute('for', field + '-checkbox');
                    radioset.appendChild(officialchecklabel);
                } else if (field == "squarefootage") {
                    //There's an additional qualifier
                    const changereasonlabel = document.createElement('label');
                    changereasonlabel.classList.add('form-label');
                    changereasonlabel.setAttribute('for', field + '-change-reason');
                    changereasonlabel.textContent = "Reason for Change";
                    radioset.appendChild(changereasonlabel);

                    const changereason = document.createElement('input');
                    changereason.setAttribute('type', 'text');
                    changereason.classList.add('form-control');
                    changereason.setAttribute('name', field + '-change-reason');
                    changereason.setAttribute('size', '6');
                    changereason.setAttribute('disabled', '');
                    radioset.appendChild(changereason);

                    const changereasonfeedback = document.createElement('div');
                    changereasonfeedback.classList.add('invalid-feedback');
                    changereasonfeedback.textContent = "If the square footage is formally different from the previous year's, it's necessary to provide a reason.";
                    radioset.appendChild(changereasonfeedback);
                }

                const calendarblock = document.createElement('div');
                calendarblock.classList.add('input-group');
                calendarblock.classList.add('date');
                calendarblock.classlist.add('mb-3');
                calendarblock.setAttribute('data-provide', 'datepicker');
                calendarblock.setAttribute('data-date-end-date', '<?php echo date('Y-m-d'); ?>');
                
                const calendarspan = document.createElement('span');
                calendarspan.classList.add('input-group-text');
                calendarspan.setAttribute('id', field + "-calendar-span");
                calendarspan.textContent = "Effective Date of Change";
                calendarblock.appendChild(calendarspan);

                const calendarinput = document.createElement('input');
                calendarinput.classList.add('form-control');
                calendarinput.setAttribute('type', 'text');
                calendarinput.setAttribute('id', field + '-calendar');
                calendarinput.setAttribute('name', field + '-calendar');
                calendarinput.setAttribute('disabled', '');
                calendarblock.appendChild(calendarinput);

                const glyphdiv = document.createElement('div');
                glyphdiv.classList.add('input-group-addon');

                const glyphspan = document.createElement('span');
                glyphspan.classList.add('glyphicon');
                glyphspan.classList.add('glyphicon-th');
                glyphdiv.appendChild(glyphspan);
                calendarblock.appendChild(glyphdiv);
                qualifierset.appendChild(calendarblock);
                fieldblock.appendChild(qualifierset);
            }
            <?php } ?>
        </script>
        <?php } ?>
  </head>

  <label for="vacationDate" class="form-label">Date of Vacation:</label>
              <div class="input-group date" data-provide="datepicker" data-date-start-date="2025-02-27" data-date-end-date="2026-12-31">
                <input id="vacationDate" name="vacationDate" class="form-control" type="text">
                <div class="input-group-addon">
                  <span class="glyphicon glyphicon-th">
                  </span>
                </div>
              </div>

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
                        <a class="nav-link" href="../login.php?logout=1">(Log Out)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../settings.php">Settings</a>
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
                if ($_REQUEST['action'] == "add") { ?>
            <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div class="mb-4">
                <div class="alert alert-secondary" type="alert">All values entered (legal name, address, city, ZIP, county, hours open) should be the values from the beginning of the fiscal year. If these values have changed since the beginning of the fiscal year, enter the old values and then update the record with the changed values noting the effective dates.</div>
                <label for="libraryname" class="form-label">Library Name</label>
                    <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips" required>
                    <div class="invalid-feedback">
                    The provided library name was too long, too short, or contained unusual characters.
                    </div>
                    <div id="librarynametips" class="form-text">
                        This should represent the common way you refer to the branch.  It can be as simple as "Branch Library" or it can be more descriptive.
                    </div>
                </div>
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
                <input type="hidden" name="formtype" value="newbranch">
                <button class="btn btn-primary" type="submit">Submit Library Branch Information</button>
            </form>
            <?php } else if (($_REQUEST['action'] == "modify") && (isset($_REQUEST['libraryid']))) { 
                //Get branch information
                $query = $db->prepare("SELECT `LibraryName`, `LegalLibraryName`, `LibraryAddress`, `LibraryCity`, `LibraryZIP`, `LibraryCounty`, `SquareFootage`, `Branch`, `FYMonth`+0 AS FYMonth FROM `LibraryInfo` WHERE `LibraryID` = ?");
                $query->bind_param("i", $_REQUEST['libraryid']);
                $query->execute();
                $result = $query->get_result();
                $libraryinfo = $result->fetch_assoc();
                $query->close();

                //Get hour data separately and append it onto the end of the existing result set
                $query->$db->prepare("SELECT MAX(`DateImplemented`) AS `DateImplemented`, `DayOfWeek`, `HoursOpen` FROM `LibraryHours` WHERE `LibraryID` = ? GROUP BY `DayOfWeek`");
                $query->bind_param('i', $_REQUEST['libraryid']);
                $query->execute();
                $result = $query->get_result();
                while ($hourinfo = $result->fetch_assoc()) {
                    $libraryinfo[$hourinfo['DayOfWeek'] . 'Hours'] = $hourinfo['HoursOpen'];
                }
                $query->close();
                ?>
            <form action="<?php echo "$protocol://$server$webdir/admin/process.php" ?>" method="POST" onsubmit="validateForm(event)">
                <div class="mb-4">
                    <label for="libraryname" class="form-label">Library Name</label>
                    <input type="text" id="libraryname" name="libraryname" class="form-control" aria-describedby="librarynametips" value="<?php echo $libraryinfo['LibraryName']; ?>"  required>
                    <div class="invalid-feedback">
                        The provided library name was too long, too short, or contained unusual characters.
                    </div>
                    <div id="librarynametips" class="form-text">
                        This should represent the common way you refer to the main library.  It can be as simple as "Main Library" or it can be more descriptive ("Harold Washington Library Center of the Chicago Public Library").
                    </div>
                </div>
                <div class="mb-4">
                    <div id="legallibraryname-block">
                        <label for="legallibraryname" class="form-label">Library Name</label>
                        <input type="text" id="legallibraryname" name="legallibraryname" class="form-control" aria-describedby="legallibrarynametips" value="<?php echo $libraryinfo['LegalLibraryName']; ?>" onchange="addQualifier('legallibraryname')" required>
                        <div class="invalid-feedback">
                            The provided library name was too long, too short, or contained unusual characters.
                        </div>
                        <div id="legallibrarynametips" class="form-text">
                            This should represent the common way you refer to the main library.  It can be as simple as "Main Library" or it can be more descriptive ("Harold Washington Library Center of the Chicago Public Library").
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div id="address-block">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo $libraryinfo['LibraryAddress']; ?>" onchange="addQualifier('address')" required>
                        <div class="invalid-feedback">
                            No address was provided, it was extremely long or extremely short, or it contained invalid characters.
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div id="city-block">
                        <label for="city" class="form-label">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?php echo $libraryinfo['LibraryCity']; ?>" onchange="addQualifier('city')" required>
                        <div class="invalid-feedback">
                            No city was provided, it was absurdly short or absurdly long, or it contained invalid characters.
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div id="zip-block">
                        <label for="zip" class="form-label">ZIP Code</label>
                        <input type="text" id="zip" name="zip" class="form-control" value="<?php echo $libraryinfo['ZIP']; ?>" onchange="addQualifier('zip')" required>
                        <div class="invalid-feedback">
                            No ZIP code was provided or it contained characters other than numbers and a hyphen.
                        </div>          
                    </div>
                </div>
                <div class="mb-4">
                    <div id="county-block">
                        <label for="county" class="form-label">County</label>
                        <input type="text" id="county" name="county" class="form-control" value="<?php echo $libraryinfo['county']; ?>" onchange="addQualifier('county')" required>
                        <div class="invalid-feedback">
                            No county was provided or it invalid characters.
                        </div>                            
                    </div>
                </div>
                <div class="mb-4">
                    <div id="telephone-block">
                        <label for="telephone" class="form-label">Telephone Number</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="<?php echo $libraryinfo['telephone']; ?>" onchange="addQualifier('telephone')" required>
                        <div class="invalid-feedback">
                        No telephone number was entered or it had invalid characters (use only numbers, and optionally, spaces or hyphens)
                        </div>          
                    </div>
                </div>                
                <div class="mb-4">
                    <div id="squarefootage-block">
                        <label for="squarefootage" class="form-label">Square Footage</label>
                        <input type="number" id="squarefootage" name="squarefootage" class="form-control" size="6" onchange="addQualifier('squarefootage')" required>
                        <div class="invalid-feedback">
                            No square footage was provided or there were invalid characters.  If you aren't sure about the square footage, put a guess here and you can correct it later.
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="islcontrolno" class="form-label">ISL Control Number</label>
                    <input type="number" id="islcontrolno" name="islcontrolno" class="form-control" value="<?php echo $libraryinfo['islcontrolno']; ?>" size="5">
                </div>
                <div class="mb-4">
                    <label for="islbranchno" class="form-label">ISL Branch Number</label>
                    <input type="number" id="islbranchno" name="islbranchno" class="form-control" value="<?php echo $libraryinfo['islbranchno']; ?>" size="2">
                </div>
                <?php if ($libraryinfo['Branch'] == 0) { 
                    //Only main library has fiscal year adjustable ?>
                <div class="mb-4">
                    <label for="fymonth" class="form-label">Fiscal Year Start</label>
                    <select class="custom-select" id="fymonth" name="fymonth" aria-describedby="fymonthtips">
                        <option value="1" <?php if ($libraryinfo['FYMonth'] == 1) { echo " selected"; } ?>>January</option>
                        <option value="2" <?php if ($libraryinfo['FYMonth'] == 2) { echo " selected"; } ?>>February</option>
                        <option value="3" <?php if ($libraryinfo['FYMonth'] == 3) { echo " selected"; } ?>>March</option>
                        <option value="4" <?php if ($libraryinfo['FYMonth'] == 4) { echo " selected"; } ?>>April</option>
                        <option value="5" <?php if ($libraryinfo['FYMonth'] == 5) { echo " selected"; } ?>>May</option>
                        <option value="6" <?php if ($libraryinfo['FYMonth'] == 6) { echo " selected"; } ?>>June</option>
                        <option value="7" <?php if ($libraryinfo['FYMonth'] == 7) { echo " selected"; } ?>>July</option>
                        <option value="8" <?php if ($libraryinfo['FYMonth'] == 8) { echo " selected"; } ?>>August</option>
                        <option value="9" <?php if ($libraryinfo['FYMonth'] == 9) { echo " selected"; } ?>>September</option>
                        <option value="10" <?php if ($libraryinfo['FYMonth'] == 10) { echo " selected"; } ?>>October</option>
                        <option value="11" <?php if ($libraryinfo['FYMonth'] == 11) { echo " selected"; } ?>>November</option>
                        <option value="12" <?php if ($libraryinfo['FYMonth'] == 12) { echo " selected"; } ?>>December</option>
                    </select>
                    <div id="fymonthtips" class="form-text">
                        Choose the month in which your fiscal year begins.  It is assumed to start on the first of the chosen month.
                    </div>
                </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Library Hours Open</h2>
                        <div id="sundayopen-block">
                            <label for="sundayopen" class="form-label">Sunday</label>
                            <input type="number" min="0" max="24" id="sundayopen" name="sundayopen" class="form-control" size="2" onchange="addQualifier('sundayopen')" step=".5" value="<?php echo $libraryinfo['SundayHours']; ?>" required>
                        </div>
                        <div id="mondayopen-block">
                            <label for="mondayopen" class="form-label">Monday</label>
                            <input type="number" min="0" max="24" id="mondayopen" name="mondayopen" class="form-control" size="2" onchange="addQualifier('mondayopen')" step=".5" value="<?php echo $libraryinfo['MondayHours']; ?>" required>
                        </div>
                        <div id="tuesdayopen-block">
                            <label for="tuesdayopen" class="form-label">Tuesday</label>
                            <input type="number" min="0" max="24" id="tuesdayopen" name="tuesdayopen" class="form-control" size="2" onchange="addQualifier('tuesdayopen')" step=".5" value="<?php echo $libraryinfo['TuesdayHours']; ?>" required>
                        </div>
                        <div id="wednesdayopen-block">
                            <label for="wednesdayopen" class="form-label">Wednesday</label>
                            <input type="number" min="0" max="24" id="wednesdayopen" name="wednesdayopen" class="form-control" size="2" onchange="addQualifier('wednesdayopen')" step=".5" value="<?php echo $libraryinfo['WednesdayHours']; ?>" required>
                        </div>
                        <div id="thursdayopen-block">
                            <label for="thursdayopen" class="form-label">Thursday</label>
                            <input type="number" min="0" max="24" id="thursdayopen" name="thursdayopen" class="form-control" size="2" onchange="addQualifier('thursdayopen')" step=".5" value="<?php echo $libraryinfo['ThursdayHours']; ?>" required>
                        </div>
                        <div id="fridayopen-block">
                            <label for="fridayopen" class="form-label">Friday</label>
                            <input type="number" min="0" max="24" id="fridayopen" name="fridayopen" class="form-control" size="2" onchange="addQualifier('fridayopen')" step=".5" value="<?php echo $libraryinfo['FridayHours']; ?>" required>
                        </div>
                        <div id="saturdayopen-block">
                            <label for="saturdayopen" class="form-label">Saturday</label>
                            <input type="number" min="0" max="24" id="saturdayopen" name="saturdayopen" class="form-control" size="2" onchange="addQualifier('saturdayopen')" step=".5" value="<?php echo $libraryinfo['SaturdayHours']; ?>" required>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="libraryid" value="<?php echo $_REQUEST['libraryid']; ?>">
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
                        <a class="btn btn-danger btn-sm" href="process.php?formtype=deletelibrary&libraryid=<?php echo $library['LibraryID']; ?>" onclick="return confirm('Are you sure you wish to delete the <?php echo $library['LibraryName']; ?> branch?')">Delete Branch</a>
                    <?php } ?>
                    </td>
                </tr>
            <?php }  ?>
            </tbody>
        </table>
        <p><a class="btn btn-primary" href="libraries.php?action=add">Add a Library Branch</a></p>

    <?php } catch (mysqli_sql_exception $e) {
            
        echo "<div class=\"alert alert-danger\" type=\"alert\">Error</div>";
        echo "<p>Error retrieving list of libraries: " . $e->getMessage();
        echo "</p></body></html>";
        $db->close();
        exit();  
    } 
}
?>