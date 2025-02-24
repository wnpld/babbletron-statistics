<?php
session_start();
require "../config.php";

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if (!$_SESSION['UserRole'] == "Admin") {
        #Send to the main site page
        header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
        exit();
    }

    try {
        $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
    } catch (mysqli_sql_exception $e) {
        echo "<html><head><title>Configuration Problem</title></head><body>";
        echo "<h1>Configuration problem</h1>";
        echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
        echo "</p></body></html>";
        exit();
    }

    if (isset($_REQUEST['totalcount'])) {
        //Delete, Update and Insert data (in that order)
        $section = $_REQUEST['section'];
        $deletes = array();
        $updates = array();
        $inserts = array();
        for ($x = 0; $x < $_REQUEST['totalcount']; $x++) {
            $qnumber = $_REQUEST['qnumber' . $x];
            $question = $_REQUEST['question' . $x];
            $qsource = $_REQUEST['qsource' . $x];
            if ($qsource == "Multiple") {
                $qformat = false;
            } else {
                $qformat = $_REQUEST['qformat' . $x];
            }
            if (($qsource == "Query") || ($qsource == "Calculation")) {
                $querydata = $_REQUEST['qformat' . $x];
            } else {
                $querydata = false;
            }
            $modify =  $_REQUEST['qmodify' . $x];
            $delete = $_REQUEST['qdelete' . $x];
            if (isset($_REQUEST['qnew' . $x])) {
                $new = $_REQUEST['qnew' . $x];
                $qid = false;
            } else {
                $new = false;
                $qid = $_REQUEST['qid' . $x];
            }

            if (($delete == "1") && ($qid != false)) {
                //Anything to be deleted should be identified first
                array_push($deletes, $qid);
            } else if ($new == "1") {
                //New will be executed after modified, but new records will have both new and modify set
                //Modified will only have modify, so check for new to identify modified separately from new
                $newvalues = array("SectionID" => $section, "Number" => $qnumber, "Question" => $question, "Source" => $source, "Format" => $format, "Query" => $querydata);
                array_push($inserts, $newvalues);
            } else if (($modify == "1") && ($qid != false)) {
                //No reason to include section id on updates.  It can't be changed on this page
                $newvalues = array("QuestionID" => $qid, "Number" => $qnumber, "Question" => $question, "Source" => $source, "Format" => $format, "Query" => $querydata);
                array_push($updates, $newvalues);
            }

            if (count($deletes) > 0) {
                try {
                    $query = $db->prepare("DELETE FROM `SRQuestions` WHERE `QuestionID` = ?");
                    foreach ($deletes as $deleteid) {
                        $query->bind_param("i", $deleteid);
                        $query->execute();
                    }
                    $query->close();
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error deleting data from SRQuestions table: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                }
            }

            if (count($updates) > 0) {
                try {
                    $query = $db->prepare("UPDATE `SRQuestions` SET `Number` = ?, `Question` = ?, `Source` = ?, `Format` = ?, `Query` = ? WHERE `QuestionID` = ?");
                    foreach ($updates as $updateitem) {
                        //Checkfor false values in Format and Query
                        if ($updateitem['Format']) {
                            $format = $updateitem['Format'];
                        } else {
                            $format = NULL;
                        }
                        if ($updateitem['Query']) {
                            $querydata = $updateitem['Query'];
                        } else {
                            $querydata = NULL;
                        }
                        $query->bind_param('sssssi', $updateitem['Number'], $updateitem['Question'], $updateitem['Source'], $format, $querydata, $updateitem['QuestionID']);
                        $query->execute();
                    }
                    $query->close();
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error updating data in SRQuestions table: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                }
            }

            if (count($inserts) > 0) {
                try {
                    $query = $db->prepare("INSERT INTO `SRQuestions` (`SectionID`, `Number`, `Question`, `Source`, `Format`, `Query`) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($inserts as $insertitem) {
                        //Check for false values in Format and Query
                        if ($insertitem['Format']) {
                            $format = $insertitem['Format'];
                        } else {
                            $format = NULL;
                        }
                        if ($insertitem['Query']) {
                            $querydata = $insertitem['Query'];
                        } else {
                            $querydata = NULL;
                        }
                        $query->bind_param('isssss', $insertitem['SectionID'], $insertitem['Number'], $insertitem['Question'], $insertitem['Source'], $format, $querydata);
                        $query->execute();
                    }
                    $query->close();
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error inserting data into SRQuestions table: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                }
            }
        }
    }
    #Show Page
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>State Report Questions Management</title>
    <link href="<?php echo $bootstrapdir; ?>/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" language="javascript">
        function setModified(counterid) {
            var modfield = "qmodify" + counterid.toString();
            document.getElementById(modfield).value = "1";
            //Check if changes should be made to query textarea
            var queryfield = "query" + counterid.toString();
            const query = document.getElementById(queryfield);
            var sourcefield = "qsource" + counterid.toString();
            const qsource = document.getElementById(sourcefield);
            var formatfield = "qformat" + counterid.toString();
            const qformat = document.getElementById(formatfield);
            //Any time something is changed, make sure the change
            //has an appropriate effect on other fields
            if (qsource.value == "Multiple") {
                qformat.disabled = true;
                query.disabled = true;
            } else if (qsource.value == "Direct") {
                qformat.disabled = false;
                query.disabled = true;
            } else {
                qformat.disabled = false;
                query.disabled = false;
            }
        }

        function addRow() {
            //Get the current row count and increment it
            var rowcount = document.getElementById('totalcount').value;
            rowcount++;
            document.getElementById('totalcount').value = rowcount;
            const table = document.getElementById('tableform');
            const newrow = table.insertRow(rowcount);
            newrow.classList.add("table-success");

            //Create Cells
            const numbercell = newrow.insertCell(0);
            const questioncell = newrow.insertCell(1);
            const sourcecell = newrow.insertCell(2);
            const formatcell = newrow.insertCell(3);
            const querycell = newrow.insertCell(4);
            const deletecell = newrow.insertCell(5);

            //Create input fields
            const numberinput = document.createElement('input');
            numberinput.classList.add('form-control');
            numberinput.setAttribute('id', 'qnumber' + rowcount);
            numberinput.setAttribute('name', 'qnumber' + rowcount);
            numberinput.setAttribute('type', 'text');
            numberinput.setAttribute('maxlength', '3');
            numberinput.setAttribute('size', '3');
            numberinput.setAttribute('onchange', 'numChange(' + rowcount + ')');
            numberinput.setAttribute('required', '');
            numbercell.appendChild(numberinput);
            
            const questioninput = document.createElement('textarea');
            questioninput.classList.add('form-control');
            questioninput.setAttribute('id', 'question' + rowcount);
            questioninput.setAttribute('name', 'question' + rowcount);
            questioninput.setAttribute('rows', '3');
            questioninput.setAttribute('onchange', 'setModified(' + rowcount + ')');
            questioninput.setAttribute('required', '');
            questioncell.appendChild(questioninput);
 
            const sourceinput = document.createElement('select');
            sourceinput.classList.add('form-control');
            sourceinput.setAttribute('id', 'qsource' + rowcount);
            sourceinput.setAttribute('name', 'qsource' + rowcount);
            sourceinput.setAttribute('onchange', 'setModified(' + rowcount + ')');
            sourcecell.appendChild(sourceinput);
            const directoption = document.createElement('option');
            directoption.setAttribute('value', 'Direct');
            directoption.appendChild("Direct");
            sourceinput.appendChild(directoption);
            const queryoption = document.createElement('option');
            queryoption.setAttribute('value', 'Query');
            queryoption.appendChild("Query");
            sourceinput.appendChild(queryoption);
            const calcoption = document.createElement('option');
            calcoption.setAttribute('value', 'Calculation');
            calcoption.appendChild("Calculation");
            sourceinput.appendChild(calcoption);
            const multoption = document.createElement('option');
            multoption.setAttribute('value', 'Multiple');
            multoption.appendChild("Multiple");
            sourceinput.appendChild(multoption);

            const formatinput = document.createElement('select');
            formatinput.classList.add('form-control');
            formatinput.setAttribute('id', 'qformat' + rowcount);
            formatinput.setAttribute('name', 'qformat' + rowcount);
            formatinput.setAttribute('onchange', 'setModified(' + rowcount + ')');
            sourcecell.appendChild(formatinput);
            const integeroption = document.createElement('option');
            integeroption.setAttribute('value', 'Integer');
            integeroption.appendChild("Number");
            formatinput.appendChild(integeroption);
            const currencyoption = document.createElement('option');
            currencyoption.setAttribute('value', 'Currency');
            currencyoption.appendChild("Currency");
            formatinput.appendChild(currencyoption);
            const textoption = document.createElement('option');
            textoption.setAttribute('value', 'Text');
            textoption.appendChild("Text");
            formatinput.appendChild(textoption);            

            const queryinput = document.createElement('textarea');
            queryinput.classList.add('form-control');
            queryinput.setAttribute('id', 'query' + rowcount);
            queryinput.setAttribute('name', 'query' + rowcount);
            queryinput.setAttribute('rows', '3');
            queryinput.setAttribute('onchange', 'setModified(' + rowcount + ')');
            querycell.appendChild(queryinput);

            const deleteinput = document.createElement('input');
            deleteinput.classList.add('form-check-input');
            deleteinput.setAttribute('type', 'checkbox');
            deleteinput.setAttribute('id', 'qdelete' + rowcount);
            deleteinput.setAttribute('name', 'qdelete' + rowcount);
            deleteinput.setAttribute('value', '1');
            deleteinput.setAttribute('onchange', 'qdeleteToggle(' + rowcount + ')');
            deletecell.appendChild(deleteinput);

            const qmodifyhidden = document.createElement('input');
            qmodifyhidden.setAttribute('id', 'qmodify' + rowcount);
            qmodifyhidden.setAttribute('name', 'qmodify' + rowcount);
            qmodifyhidden.setAttribute('value', '1');
            newrow.appeendChild(qmodifyhidden);

            const qnewhidden = document.createElement('input');
            qnewhidden.setAttribute('id', 'qnew' + rowcount);
            qnewhidden.setAttribute('name', 'qnew' + rowcount);
            qnewhidden.setAttribute('value', '1');
            newrow.appendChild(qnewhidden);

        }

        function numChange(counterid) {
            var numberfield = "qnumber" + counterid;
            var rowcount = document.getElementById('totalcount').value;
            const qnumber = document.getElementById(numberfield);
            const re = /^\d{2}[a-z]{0,1}$/;
            if (re.test(qnumber.value)) {
                let match = false;
                for (x = 0; x <= rowcount; x++) {
                    if (counterid != x) {
                        let compfield = "qnumber" + counterid;
                        if (qnumber.value == document.getElementById(compfield).value) {
                            match = true;
                        }
                    }
                }
                if (match === true) {
                    alert("Question number must be unique and the number you have added matches an existing number.  If you need to renumber multiple questions, start from the top of the number set and work your way down.");
                    qnumber.value = "";
                    setModified(counterid);
                }
            } else {
                alert("Question number must be two digits followed by an optional lowercase letter.  Examples are: 10, 05, 08a");
                qnumber.value = "";
                setModified(counterid);
            }
            setModified(counterid);
        }

        function deleteToggle(counterid) {
            var rowid = "row" + counterid;
            const row = document.getElementById(rowid);
            var numberfield = "qnumber" + counterid;
            const qnumber = document.getElementById(numberfield);
            var checkfield = "qdelete" + counterid;
            const checkbox = document.getElementById(checkfield);
            var questionfield = "question" + counterid;
            const question = document.getElementById(questionfield);
            var sourcefield = "qsource" + counterid;
            const qsource = document.getElementById(sourcefield);
            var formatfield = "qformat" + counterid;
            const qformat = document.getElementById(formatfield);
            var queryfield = "query" + counterid;
            const query = document.getElementById(query);

            if (checkbox.checked) {
                //Item is marked as to be deleted
                row.classList.add('table-danger');
                qnumber.disabled = true;
                qnumber.removeAttribute('required');
                question.disabled = true;
                question.removeAttribute('required');
                qsource.disabled = true;
                qformat.disabled = true;
                query.disabled = true;
                query.removeAttribute('required');
            } else {
                //Item is marked as to be retained
                row.classList.remove('table-danger');
                qnumber.disabled = false;
                qnumber.setAttribute('required', '');
                question.disabled = false;
                question.setAttribute('required', '');
                qsource.disabled = false;
                if (source.value != "Multiple") {
                    qformat.disabled = false;
                    if (qsource.value != "Direct") {
                        query.disabled = false;
                        query.setAttribute('required', '');
                    }
                }
            }
        }

        function validateFields(event) {
            var rowcount = document.getElementById('totalcount').value;
            const problemalert = document.getElementById('problemalert');
            const problemlist = document.getElementById('problemlist');
            //Reset problem items
            while (problemlist.firstChild) {
                problemlist.removeChild(problemlist.lastChild);
            }
            //Reset visability on alert in case it's been set to visible
            problemalert.setAttribute("style", "display: none;");
            problems = false;
            for (x = 0; x < rowcount; x++) {
                let modifiedState = document.getElementById('qmodify' + x.toString()).value;
                let deletedState = document.getElementById('qdelete' + x.toString()).value;
                //There's no need to validate rows being deleted or rows not being modified
                if ((deletedState != "1") && (modifiedState != '0')) {
                    let source = document.getElementById('qsource' + x.toString()).value;
                    let query = document.getElementById('query' + x.toString());
                    let qnumber = document.getElementById('qnumber' + x.toString());
                    let question = document.getElementById('question' + x.toString());
                    let queryvalid = true;
                    let qnumbervalid = true;
                    let questionvalid = true;
                    //Validate query if qsource is query or Calculation
                    if (source == "Query") {
                        let re = /^SELECT [A-Za-z`'| (),._]+$/;
                        if (!re.test(query.value)) {
                            queryvalid = false;
                            query.classList.remove('is-valid');
                            query.classList.add('is-invalid');
                        } else {
                            query.classList.remove('is-invalid');
                            query.classList.add('is-valid');
                        }
                    } else if (source == "Calculation") {
                        let re = /^(\d\d\.\d\d[a-z]{0,1})(,\d\d\.\d\d[a-z]{0,1})+$/;
                        if (!re.text(query.value)) {
                            queryvalid = false;
                            query.classList.remove('is-valid');
                            query.classList.add('is-invalid');
                        } else {
                            query.classList.remove('is-invalid');
                            query.classList.add('is-valid');
                        }
                    }
                    let re = /^\d\d[a-z]{0,1}$/;
                    if (!re.test(qnumber.value)) {
                        qnumbervalid = false;
                        qnumber.classList.remove('is-valid');
                        qnumber.classList.add('is-invalid');
                    } else {
                        qnumber.classList.remove('is-invalid');
                        qnumber.classList.add('is-valid');
                    }
                    let re = /^[A-Za-z \/.,()\-'"$%#!*;:]{10,}/;
                    if (!re.test(question.value)) {
                        questionvalid = false;
                        question.classList.remove('is-valid');
                        question.classList.add('is-invalid');
                    } else {
                        question.classList.remove('is-invalid');
                        question.classList.add('is-valid');
                    }
                    if ((queryvalid === false) || (qnumbervalid === false) || (questionvalid === false)) {
                        //Add information to the alert
                        if (problems === false) {
                            //If the alert isn't on, turn it on
                            problemalert.setAttribute("style", "display: block;");
                        }
                        const problemitem = document.createElement('li');
                        problemitem.appendChild("");
                        problemlist.appendChild(problemitem);
                    }
                }
            }
            if (problems === true) {
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
                        <a class="nav-link active" aria-current="true">State Report Questions</a>
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
            //Check for existing data in the SRData table.  Changing question data is likely to cause chaos
            //with question data, so force a user to export that data (if desired) and delete the data before
            //changing the questions
            if (isset($_REQUEST['action'])) {
                if ($_REQUEST['action'] == "force") {
                    //Force editing questions even with undeleted data
                    $existingdata = false;
                } else if ($_REQUEST['action'] == "deletesrdata") {
                    try {
                        if( $db->query("DELETE * FROM `SRData`") ) {
                            $existingdata = false;
                        } else {
                            //Don't know why this would happen, but just in case
                            $existingdata = true;
                        }
                    } catch (mysqli_sql_exception $e) {
                        echo "<html><head><title>Error</title></head><body>";
                        echo "<p>Error deleting data from SRData table: ". $e->getMessage();
                        echo "</p></body></html>";
                        $db->close();
                        exit();
                    }
                }

            } else {

                $existingdata = false;
                try {
                    $result = $db->query("SELECT * FROM `SRData`");
                    if ($result->num_rows > 0) {
                        $existingdata == true;
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error checking for content in SRData table: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                }
            }
            if ($existingdata == true) {
                //Provide tools for saving and deleting data ?>
            <h1>Existing State Report Data</h1>    
            <p>
                You have existing state report data established.  Changing the state report
                questions is almost certainly going to break relationships between existing
                questions and responses.  Consequently it is strongly recommended to archive your
                existing report data as an Excel spreadsheet and then delete the data before proceeding
                to modify the state report questions.
            </p>
            <p>
                Deleting report data will delete any information that's been manually input into the state
                report, but it will not touch data entered elsewhere.  The data entered through monthly collection
                that is automatically entered into the state report or any data that is calculated from that data
                will not be touched.
            </p>
            <p><a class="btn btn-success btn-lg" href="/<?php echo $cgiwebdir; ?>/srxlsx.pl?action=dump" target="_blank"></a>Export All Report Data as Excel</p>
            <p><a class="btn btn-danger btn-lg" href="srquestions.php?action=deletesrdata" onclick="return confirm('Are you sure you wish to delete the existing report data?')">Delete Existing Report Data and Proceed</a></p>
            <div class="alert alert-danger">If you are certain of what you are doing, you can <a href="srquestions.php?action=force">proceed without deleting old data</a>, but know that you may break some things badly if you do.</div>
            <?php } else if (!isset($_REQUEST['section'])) {
                try { 
                    $result = $db->query("SELECT `SectionID`, `SectionDescription` FROM `SRSections` ORDER BY `SectionID` ASC"); 
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error getting State Report section list: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();
                } ?>
                <h1>Select Report Section</h1>
                <p>Select the report section you wish to work on:</p>
                <form action="srquestions.php" method="POST">
                    <div class="mb-4">
                        <select name="section" class="custom-select custom-select-lg mb-3">
                            <option selected>Select a Category</option>
                        <?php 
                        while ($section = $result->fetch_assoc()) { ?>
                            <option value="<?php echo $section['SectionID']; ?>"><?php echo $section['SectionDescription']; ?> (<?php echo $section['SectionID']; ?>)</option>
                        <?php }
                        ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg">Load Questions</button>
                </form>
            <?php } else { 
                try {
                    $query = $db->prepare("SELECT `SectionDescription`, `SectionID` FROM `SRSections` WHERE `SectionID` = ?");
                    $query->bind_param("i", $_REQUEST['section']);
                    $query->execute();
                    $result = $query->get_result();
                    $section = $result->fetch_assoc();

                    $query = $db->prepare("SELECT `QuestionID`, `Number`, `Question`, `Source`, `Format`, `Query` FROM `SRQuestions` WHERE `SectionID` = ? ORDER BY `Number` ASC");
                    $query->bind_param("i", $_REQUEST['section']);
                    $query->execute();
                    $result = $query->get_result();
                } catch (mysqli_sql_exception $e) {
                    echo "<html><head><title>Error</title></head><body>";
                    echo "<p>Error getting State Report section list: ". $e->getMessage();
                    echo "</p></body></html>";
                    $db->close();
                    exit();                    
                }
                ?>
                <h1><?php echo $section['SectionDescription']; ?> (<?php echo $section['SectionID']; ?>) Questions</h1>
                <div id="problemalert" class="alert alert-danger" type="alert" style="display: none;">
                    <p><strong>The following issues prevented form validation:</strong></p>
                    <ul id="problemlist"></ul>
                </div>
                <p>If you have <em>any</em> questions about what you're doing here, <a href="sectionguide.php" target="_blank">check the section guide</a> (link opens in a new window).</p>
                <form action="srquestions.php" method="POST">
                    <table class="table" id="tableform">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Question</th>
                                <th scope="col">Source</th>
                                <th scope="col">Format</th>
                                <th scope="col">Query</th>
                                <th scope="col">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php
                $counter = 0;
                while ($question = $result->fetch_assoc()) { ?>
                <tr id="row<?php echo $counter; ?>">
                    <th scope="row">
                        <input class="form-control" type="text" id="qnumber<?php echo $counter; ?>" name="qnumber<?php echo $counter; ?>" value="<?php echo $question['Number']; ?>" maxlength="3" size="3" onchange="numChange(<?php echo $counter; ?>" required>
                    </th>
                    <td>
                        <textarea class="form-control" id="question<?php echo $counter; ?>" name="question<?php echo $counter; ?>" rows="3" onchange="setModified(<?php echo $counter; ?>)" required><?php echo $question['Question']; ?>
                        </textarea>
                    </td>
                    <td>
                        <select class="form-control" id="qsource<?php echo $counter; ?>" name="qsource<?php echo $counter; ?>" onchange="setModified(<?php echo $counter; ?>)">
                            <option value="Direct" <?php if ($question['Source'] == "Direct") { echo "selected"; } ?>>Direct</option>
                            <option value="Query" <?php if ($question['Source'] == "Query") { echo "selected"; } ?>>Query</option>
                            <option value="Calculation" <?php if ($question['Source'] == "Calculation") { echo "selected"; } ?>>Calculation</option>
                            <option value="Multiple" <?php if ($question['Source'] == "Multiple") { echo "selected"; } ?>>Multiple</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control" id="qformat<?php echo $counter; ?>" name="qformat<?php echo $counter; ?>" onchange="setModified(<?php echo $counter; ?>)">
                            <option value="Integer" <?php if ($question['Format'] == "Integer") { echo "selected"; } ?>>Number</option>
                            <option value="Currency" <?php if ($question['Format'] == "Currency") { echo "selected"; } ?>>Currency</option>
                            <option value="Text" <?php if ($question['Format'] == "Text") { echo "selected"; } ?>>Text</option>
                        </select>
                    </td>
                    <td>
                    <?php if (($question['Source'] == "Direct") || ($question['Source'] == "Multiple")) { ?>
                        <textarea class="form-control" id="query<?php echo $counter; ?>" name="query<?php echo $counter; ?>" rows="3" onchange="setModified(<?php echo $counter; ?>)" disabled></textarea>
                    <?php } else { ?>
                        <textarea class="form-control" id="query<?php echo $counter; ?>" name="query<?php echo $counter; ?>" rows="3" onchange="setModified(<?php echo $counter; ?>)" required><?php echo $question['Query']; ?></textarea>
                    <?php } ?>
                    </td>
                    <td>
                        <input class="form-check-input" type="checkbox" id="qdelete<?php echo $counter; ?>" name="qdelete<?php echo $counter; ?>" value="1" onchange="qdeleteToggle(<?php echo $counter; ?>)">
                        <input type="hidden" id="qid<?php echo $counter; ?>" name="qid<?php echo $counter; ?>" value="<?php echo $question['QuestionID']; ?>">
                        <input type="hidden" id="qmodify<?php echo $counter; ?>" name="qmodify<?php echo $counter; ?>" value="0" >
                        <input type="hidden" id="qnew<?php echo $counter; ?>" name="qnew<?php echo $counter; ?>" value="0">
                    </td>
                </tr>
               <?php
                    $counter++; 
                    } ?>
                        </tbody>
                    </table>
                    <button id="addrow" onclick="addRow()" class="btn btn-success">Add a New Question</button>
                    <input type="hidden" name="totalcount" value="<?php echo $counter; ?>">
                    <input type="hidden" name="section" value="<?php echo $section['SectionID']; ?>">
                    <button type="submit" class="btn btn-primary btn-lg" onclick="validateFields(event)">Submit Changes</button>
                </form>
           <?php } ?>
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