<?php
/*
 Database information

 This site requires a MySQL or MariaDB database */

$mysqlhost = "localhost"; //If your database server is somewhere else, put that here
$dbname = ""; //The name of your database
$dbadmin = ""; //A user with all rights on the database
$dbadminpw = "";  //The password for a user with all rights
$dbuser = "";  //A user with insert, select, delete, update access only
$dbuserpw = "";  //Password for the restricted user

/* Use this variable for collecting data from other databases (example in comments)
$dblist = array( 
  "Suma" => array(
    "host" => "localhost",
    "dbname" => "suma_db",
    "user" => "suma_user",
    "password" => "092j3jkalsfd",));
*/ 

/*
 Site information
*/

$sitename = "Babbletron"; //This should be a short descriptor for your site
                        //which shows up in the upper-left corner of each page.

$protocol = "http"; //If you've set up SSL, change this to https
              //If this site is internet available setting it up
              //with SSL is strongly recommended

$server = ""; //Put your server address here
              //(e.g. "server.mylibrary.local")

$webdir = ""; //The directory this site is installed into
               //This should be started with a slash if not the root
               //(e.g. "/statistics") 
               //If the directory is the root directory, leave blank

$bootstrapdir = ""; //The directory bootstrap is installed into
$entryrestriction = 0; //Set to 1 to require a login to enter data (no login for viewing)
                       //Set to 2 to require a login to enter data or view reports

$cgiwebdir = ""; //The path from the server root to the CGI directory
                 //(e.g. "/cgi-bin" if it's found at http://mysite.local/cgi-bin)
$cgidir = ""; //The full system path to the cgi directory
              //(e.g. "/usr/lib/cgi-bin/")

$datepickerdir = ""; //The directory the bootstrap-datepicker javascript library
                  //is installed in.  Docs and information can be found at
                  //https://bootstrap-datepicker.readthedocs.io/

?>