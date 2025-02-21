<?php
session_start();
require "../config.php";

if ( isset($_SESSION["UserID"]) && !empty($_SESSION["UserID"]) ) {
    if (!$_SESSION['UserRole'] == "Admin") {
        #Send to the main site page
        header("Location: $protocol://$server$webdir/login.php?nomatch=privilege");
        exit();
    }
    #Show Content
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>State Report Questions Section Guide</title>
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
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Admin</a>
                    </li>
                    <li>
                        <a class="nav-link" href="srquestions.php">State Report Questions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="true">Section Guide</a>
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
        < class="container-fluid">
        <h1>State Report Questions Section Guide</h1>
        <p>The state report is divided into multiple sections and each section has many numbered questions.  Questions can be identified by combining the section number and the question number joined by a period (e.g. question 18a in section 8 can be uniquely identified as 8.18a).</p>
        <p>The full list of available sections with numbers is as follows:</p>
        <table class="table">
            <thead>
                <th>Section ID</th>
                <th>Section Description</th>
            </thead>
            <tbody>
            <?php 
            //Check for existing data in the SRData table.  Changing question data is likely to cause chaos
            //with question data, so force a user to export that data (if desired) and delete the data before
            //changing the questions
            try {
                $db = new mysqli($mysqlhost, $dbadmin, $dbadminpw, $dbname);
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>Configuration Problem</title></head><body>";
                echo "<h1>Configuration problem</h1>";
                echo "<p>It was not possible to establish a connection to a MySQL or MariaDB server to begin site configuration.  Make sure that you have established a MySQL database following the instructions in the README and have added the required information to the config.php file in the root directory.  Here is the exact error message that was returned from the connection attempt: " . $e->getMessage();
                echo "</p></body></html>";
                exit();
            }

            try {
                $result = $db->query("SELECT SectionID, SectionDescription FROM SRSections");
                while ($section = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $section['SectionID']; ?></td>
                    <td><?php echo $section['SectionDescription']; ?></td>
                </tr>
               <?php }
            } catch (mysqli_sql_exception $e) {
                echo "<html><head><title>Error</title></head><body>";
                echo "<p>Error getting results from SRSections table: ". $e->getMessage();
                echo "</p></body></html>";
                $db->close();
                exit();
            }

        ?>
            </tbody>
        </table>
        <h2>The Questions Table</h2>
            <p>The state report questions are stored in their own database table which references the table with these section identifiers.  The questions themselves have been designed to be able to be adjusted with time as the state report changes.  There are four different general sources for the answers to questions with three different formats.</p>
            <p>The sources are:</p>
            <ul>
                <li><strong>Direct</strong> - These are answers which are directly entered by the user at the time of compiling the report.  These are typically answers which would be difficult to obtain from monthly reporting (e.g. organization's website address).</li>
                <li><strong>Query</strong> - These are answers which can be derived from data collected in monthly statistics reports.  The answers are obtained from a query which is stored here (with some modifications).  See below for details in creating or editing these queries.</li>
                <li><strong>Calculation</strong> - These are answers which are obtained by adding up other answers.  You can define a calculation by putting a comma separated list of the questions to be added by their number (e.g. "8.02,8.03,8.04,8.05" would add up questions 2, 3, 4, and 5 of section 8)</li>
                <li><strong>Multiple</strong> - At least for this version of this tool, these are placeholders.  This means that this is a likely repeated field listing (for example) board members or staff members.  The goal of this tool is to make the collection of standard statistics easier, so although accommodating this feature could be done in the future, this is a bit complicated and not a high priority</li>
            </ul>
            <p>The formats are:</p>
            <ul>
                <li><strong>Number</strong> - Internally referred to as <em>Integer</em>.  These are positive whole numbers representing reguar quantities of things or counts.</li>
                <li><strong>Currency</strong> - These are numbers stored with two decimal places and up to 8 digits before the decimal, so a maximum of $99,999,999.99.</li>
                <li><strong>Text</strong> - This is just plain text data, like an address or the library's name.</li>
            </ul>
            <h2>Queries</h2>
            <p>The queries are intended to be built against a core set of tables designated specifically for use with the state report.  It is possible to store and report on statistics unrelated to the state report in this tool, but a core feature is collection of data for the state report.  To make that data consistent over time, even when the means of collecting it or the sources from which it's collected are change over time, that data is stored in its own set of tables.</p>
            <p>A query is built using regular SQL (MySQL/MariaDB) syntax except that the references to the content of the year and month fields are replaced with placeholders been the pipe (|) character.  Here's an example query:</p>
            <div class="p-3 text-info-emphasis bg-info-subtle border border-info-subtle rounded-3">SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Audience` = 'Adult') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, '-0', '-'), `Month`+0, '-01')) BETWEEN DATE('|startyear|-|startmonth|-01') AND DATE('|endyear|-|startmonth|-01\')</div>
            <p>If you want to modify queries for the state report in this tool, you'll need to use this structure to make sure that the query continues to work as the fiscal year changes.</p>
            <h2>Table Reference</h2>
            <p>Below is a list of the state report data tables and their structures which can be used in constructing queries.</p>
            <?php
                $tablelist = array("SRBudgetAdjustments" => "Budget Income/Expenses","SRBudgetCategories" => "Budgeting Categories","SRComputers" => "Computer Inventory","SRILL" => "Interlibrary Loan Transactions","SRPatronAssistance" => "Reference and Tutorial Assistance","SRCollection" => "Physical/Digital Circulation","SRPrograms" => "Program Statistics","SRSpaceUse" => "Study/Meeting Room Use","SRSpaces" => "List of Study/Meeting Rooms","SRTechnologyCounts" => "Internet/Hotspot/Website Usage","SRVisits" => "Library Visits");
                foreach ($tablelist as $table => $description) { ?>
                    <h3><?php echo $description; ?></h3>
                    <h4><?php echo $table; ?></h4>
                    <table class="table">
                        <thead>
                            <th>Data Column</th>
                            <th>Data Type/Description</th>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $query = "DESCRIBE `" . $table . "`";
                                $result = $db->query($query);
                                while ($tableinfo = $result->fetch_assoc()) { ?>
                                    <td><?php echo $tableinfo['Field']; ?></td>
                                    <td><?php echo $tableinfo['Type']; ?></td>
                                <?php }
                            } catch (mysqli_sql_exception $e) {
                                echo "<html><head><title>Error</title></head><body>";
                                echo "<p>Error getting description of $table table: ". $e->getMessage();
                                echo "</p></body></html>";
                                $db->close();
                                exit();                                
                            }
                            ?>
                        </tbody>
                    </table>
                <?php
                    if ($table == "SRBudgetCategories") { ?>
                        <p>The budget categories table is pre-loaded with the following categories:</p>
                        <ul>
                            <?php
                            try {
                                $result = $db->query("SELECT CategoryDescription, CategoryType FROM SRBudgetCategories");
                                while ($bcat = $result->fetch_assoc()) { ?>
                                    <li><?php echo $bcat['CategoryDescription'] . " (" . $bcat['CategoryType'] . ")"; ?></li>
                                <?php }
                            } catch (mysqli_sql_exception $e) {
                                echo "<html><head><title>Error</title></head><body>";
                                echo "<p>Error getting results from SRBudgetCategories table: ". $e->getMessage();
                                echo "</p></body></html>";
                                $db->close();
                                exit();   
                            }
                            ?>
                        </ul>
                    <? }
                }
            ?>

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