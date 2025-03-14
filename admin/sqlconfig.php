<?php
#######################
# SQL Structures & Data
#######################

#Users Table
$users_table = "CREATE TABLE `Users` (`UserID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `UserName` varchar(25) UNIQUE NOT NULL, `LastName` varchar(50), `FirstName` varchar(50), `Password` binary(32) NOT NULL, `Salt` char(100) NOT NULL, `UserRole` enum('Admin','Edit','View') NOT NULL)";

#Library Information Table
$libraries_table = "CREATE TABLE `LibraryInfo` (`LibraryID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `LibraryName` varchar(100) NOT NULL, `LegalName` varchar(100) NOT NULL, `LibraryAddress` varchar(150), `LibraryCity` varchar(75), `LibraryZIP` varchar(10) NOT NULL, `LibraryCounty` varchar(75) NOT NULL, `LibraryTelephone` char(10) NOT NULL, `SquareFootage` int NOT NULL, `Branch` tinyint(1) DEFAULT 1, `FYMonth` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DEFAULT 'January', `ISLControlNo` char(5) DEFAULT NULL, `ISLBranchNo` char(2) DEFAULT NULL)";

#Library Hours Table
$library_hours_table = "CREATE TABLE `LibraryHours` (`LibraryID` tinyint UNSIGNED, `DateImplemented` date NOT NULL, `DayOfWeek` enum('Sunday','Monday','Tuesday',Wednesday','Thursday','Friday','Saturday') NOT NULL, `HoursOpen` decimal(3,1) UNSIGNED NOT NULL, CONSTRAINT pk_libraryhours PRIMARY KEY (`LibraryID`, `DateImplemented`, `DayOfWeek`), INDEX fk_libraryhours (`LibraryID`), FOREIGN KEY (`LibraryID`) REFERENCES `LibraryInfo`(`LibraryID`))";

#Library Closings Table
#This is mainly to track closings, but it can also be used to track days where there
#were extended hours by storing a negative value that is equal to the extra hours
#(e.g. if a day is normally 4 hours but the library was open 8, a -4 could be stored here)
$library_closings_table = "CREATE TABLE `LibraryClosings` (`ClosingID` smallint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `LibraryID` tinyint NULL, `DateClosed` date NOT NULL, `HoursClosed` smallint UNSIGNED NOT NULL, `ClosingType` enum('Holiday','Staff Meeting','Weather','Other') NOT NULL, INDEX fk_libraryclosings (`LibraryID`), FOREIGN KEY (`LibraryID`) REFERENCES `LibraryInfo`(`LibraryID`))";

#Date Lookup Table
#This table is automatically populated and is used for complicated date calculations
#like figuring out how many Thursdays occurred in a fiscal year
$date_lookup_table = "CREATE TABLE `DateLookup` (`Date` date PRIMARY KEY, `Weekday` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL, `Month` enum('January','February','March','April','May','June','July','August','September','October','November','December') NOT NULL, `Year` year NOT NULL)";

#Special Tables for State Report
#Library Changes Table - For tracking changes to Library information
$library_changes_table = "CREATE TABLE `LibraryChanges` (`LibraryID` tinyint UNSIGNED NOT NULL, `FYChanged` year NOT NULL, `LegalName` varchar(100) NULL, `LegalNameChange` enum('Yes','No') NULL, `LibraryAddress` varchar(150) NULL, `PhysicalAddressChange` enum('Yes','No') NULL, `LibraryCity` varchar(75) NULL, `LibraryZIP` varchar(10) NULL, `LibraryCounty` varchar(75) NULL, `LibraryTelephone` char(10) NOT NULL, `SquareFootage` int NULL, `SquareFootageReason` varchar(200) NULL, CONSTRAINT pk_librarychanges PRIMARY KEY (`LibraryID`, `FYChanged`) INDEX fk_librarychanges (`LibraryID`), FOREIGN KEY (`LibraryID`) REFERENCES `LibraryInfo`(`LibraryID`))";

#Referenda
$referenda_table = "CREATE TABLE `Referenda` (`ReferendumID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `ReferendumType` varchar(75) NOT NULL, `ReferendumDate` date NOT NULL, `ReferendumPassed` enum('Yes','No') NOT NULL, `EffectiveDate` date NULL, `BallotLanguage` text NOT NULL)";

#Board Trustees
$board_table = "CREATE TABLE `BoardTrustees` (`TrusteeID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `TrusteeName` varchar(100) NOT NULL, `TrusteePosition` varchar(75) NOT NULL, `TrusteeTermEnds` date NOT NULL, `TrusteeTelephone` varchar(10) NOT NULL, `TrusteeEmail` varchar(100) NOT NULL, `TrusteeHomeAddress` varchar(150) NOT NULL, `TrusteeCity` varchar(75) NOT NULL, `TrusteeState` varchar(50) NOT NULL, `TrusteeZIP` varchar(10) NOT NULL)";

#Librarians
$librarians_table = "CREATE TABLE `LibrarianPositions` (`PositionID` smallint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `PositionStatus` enum('Active','Vacant','New','Eliminated') NOT NULL, `PositionTitle` varchar(100) NOT NULL, `WorkAreaID` varchar(4) NOT NULL, `EducationLevelID` varchar(3) NOT NULL, `HourlyRate` DECIMAL(5,2) NULL, `HoursPerWeek` tinyint UNSIGNED NOT NULL, `AnnualSalaryMinimum` DECIMAL(8,2) NULL, `AnnualSalaryMaximum` DECIMAL(8,2) NULL, `DateFilled` date NULL, `DateVacated` date NULL, `DateCreated` date NULL, `DateEliminated` date NULL, `LastSalaryPaid` DECIMAL(8,2) NULL, `ReasonEliminated` varchar(200) NULL, INDEX fk_librarianworkarea, FOREIGN KEY (`WorkAreaID`) REFERENCES `WorkAreas`(`WorkAreaID`), INDEX fk_librarianeducation, FOREIGN KEY (`EducationLevelID`) REFERENCES `EducationLevels`(`EducationLevelID`)";

#Work Areas
$work_areas_table = "CREATE TABLE `WorkAreas` (`WorkAreaID` varchar(4) PRIMARY KEY, `WorkAreaDescription` varchar(50) NOT NULL)";

#Add Work Areas to the Work Areas table
$work_areas_stmt = "INSERT INTO `WorkAreas` (`WorkAreaID`, `WorkAreaDescription`) VALUES (?, ?)";
$work_areas = array();
array_push($work_areas, array('LD', 'Library Director'));
array_push($work_areas, array('ADIR', 'Assistant Director'));
array_push($work_areas, array('ALIB', 'Assistant Librarian'));
array_push($work_areas, array('AUTO', 'Automation/Technology/Systems'));
array_push($work_areas, array('BKMB', 'Bookmobile'));
array_push($work_areas, array('CAT', 'Cataloging'));
array_push($work_areas, array('COL', 'Collection Development/Acquisitions'));
array_push($work_areas, array('CIR', 'Circulation'));
array_push($work_areas, array('ILL', 'Interlibrary Loan/Document Delivery'));
array_push($work_areas, array('REF', 'Reference'));
array_push($work_areas, array('CHD', 'Children\'s Services'));
array_push($work_areas, array('YAS', 'Young Adult Services'));
array_push($work_areas, array('ADT', 'Adult Services'));
array_push($work_areas, array('OTH', 'Other Type of Librarian'));
array_push($work_areas, array('NA', 'N/A'));

#Education Levels
$education_levels_table = "CREATE TABLE `EducationLevels` (`EducationLevelID` varchar(3) PRIMARY KEY, `EducationLevel` varchar(100) NOT NULL)";

#Add Education Levels
$education_levels_stmt = "INSERT INTO `EducationLevels` (`EducationLevelID`, `EducationLevel`) VALUES (?, ?)";
$education_levels = array();
array_push($education_levels, array('MLS', 'Master\'s Degree in library science'));
array_push($education_levels, array('MAO', 'Master\'s Degree: Not in library science'));
array_push($education_levels, array('BAL', 'Bachelor\'s Degree: Includes a major or minor in library science'));
array_push($education_levels, array('BAC', 'Bachelor\'s Degree: No library science'));
array_push($education_levels, array('LTA', 'Less than a Bachelor\'s degree but with an LTA (Library Technical Assistant) or equivalent'));
array_push($education_levels, array('HS', 'Less than a Bachelor\'s degree'));
array_push($education_levels, array('NA', 'N/A'));

##State Report Support Tables
#Library Spaces (Meeting rooms & study Rooms)
$spaces_table = "CREATE TABLE `SRSpaces` (`SpaceID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `SpaceDescription` varchar(75), `LibraryID` tinyint UNSIGNED NOT NULL, `SpaceType` enum('Meeting Room', 'Study Room'), INDEX libraryspace_fk (`LibraryID`), FOREIGN KEY (`LibraryID`) REFERENCES `LibraryInfo`(`LibraryID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Space Use
$spaceuse_table = "CREATE TABLE `SRSpaceUse` (`SpaceUseID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `SpaceID` tinyint UNSIGNED NOT NULL, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `Total` smallint UNSIGNED NOT NULL, INDEX spaceid_fk (`SpaceID`), FOREIGN KEY (`SpaceID`) REFERENCES `SRSpaces`(`SpaceID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Budget Categories
$budgetcategories_table = "CREATE TABLE `SRBudgetCategories` (`CategoryID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `CategoryDescription` varchar(100) NOT NULL, `CategoryType` enum('Income', 'Expense') NOT NULL)";

#Budget Categories - Data
$budgetcategories_stmt = "INSERT INTO `SRBudgetCategories` (`CategoryDescription`, `CategoryType`) VALUES (?, ?)";
$budgetcategories_data = array();
array_push($budgetcategories_data, array('Local Government Income', 'Income'));
array_push($budgetcategories_data, array('Per Capita Grant', 'Income'));
array_push($budgetcategories_data, array('Monetary Gifts', 'Income'));
array_push($budgetcategories_data, array('Non-Resident Card Fees', 'Income'));
array_push($budgetcategories_data, array('Other Income', 'Income'));
array_push($budgetcategories_data, array('Salaries and Wages', 'Expense'));
array_push($budgetcategories_data, array('Fringe Benefits', 'Expense'));
array_push($budgetcategories_data, array('Staff Development', 'Expense'));
array_push($budgetcategories_data, array('Print Materials', 'Expense'));
array_push($budgetcategories_data, array('Electronic Materials', 'Expense'));
array_push($budgetcategories_data, array('Other Materials', 'Expense'));
array_push($budgetcategories_data, array('Other Operating Expenditures', 'Expense'));
array_push($budgetcategories_data, array('Capital Expenditures', 'Expense'));

#Budget Expenses/Income
$budgetadjustments_table = "CREATE TABLE `SRBudgetAdjustments` (`AdjustmentID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `CategoryID` tinyint UNSIGNED NOT NULL, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `Total` decimal(8,2) NOT NULL, INDEX budgetcategory_fk (`CategoryID`), FOREIGN KEY (`CategoryID`) REFERENCES `SRBudgetCategories`(`CategoryID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Library Visits
$visits_table = "CREATE TABLE `SRVisits` (`VisitID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `LibraryID` tinyint UNSIGNED NOT NULL, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `Total` int UNSIGNED NOT NULL, INDEX libraryvisits_fk (`LibraryID`), FOREIGN KEY (`LibraryID`) REFERENCES `LibraryInfo`(`LibraryID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Registered Library Cards
$cards_table = "CREATE TABLE `SRCards` (`CardCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, CountType = enum('Resident', 'Non-Resident') NOT NULL, `TotalCards` int UNSIGNED NOT NULL)";

#Registered Library Cards
$cards_table = "CREATE TABLE `SRCards` (`CardCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `TotalCards` int UNSIGNED NOT NULL)";

#Library Programs
$programs_table = "CREATE TABLE `SRPrograms` (`ProgramCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `CountType` enum('Event','Attendance') NOT NULL, `Age` enum('0-5', '6-11', '12-18', '19+', 'All Ages') NOT NULL, `Synchronous` enum('Yes', 'No') NOT NULL, `ProgramLocation` enum('Onsite', 'Offsite', 'Virtual') NOT NULL, `Total` smallint UNSIGNED NOT NULL)";

#Library Collection Size
$collection_table = "CREATE TABLE`SRCollection` (`CollectionCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `Material` enum('Book', 'Serial Subscription', 'Audio', 'Video', 'Other') NOT NULL, `MaterialType` enum('Physical', 'Digital') NOT NULL, `Audience` enum('Adult', 'Young Adult', 'Child') NOT NULL, `Total` int UNSIGNED NOT NULL)";

#Library Circulation
$circulation_table = "CREATE TABLE SRCirculation (`CirculationCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL,  `Material` enum('Book', 'Audio', 'Magazine', 'Video', 'Other') NOT NULL, `MaterialType` enum('Physical', 'Digital') NOT NULL, `Audience` enum('Adult', 'Young Adult', 'Children') NOT NULL, `Total` int UNSIGNED NOT NULL)";

#Interlibrary Loan
$ill_table = "CREATE TABLE `SRILL` (`ILLID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `ILLRole` enum('Lender', 'Borrower') NOT NULL, `Total` int UNSIGNED NOT NULL)";

#Computer Inventory
$computers_table = "CREATE TABLE `SRComputers` (`ComputerID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `FormFactor` enum('Desktop', 'Laptop', 'Server') NOT NULL, `User` enum('Staff', 'Public') NOT NULL, `InternetAccess` enum('Yes', 'No') NOT NULL)";

#Technology Use
$technologies_table = "CREATE TABLE `SRTechnologyCounts` (`TechnologyCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `TechnologyType` enum('Public Internet', 'Wireless Access', 'Website Sessions') NOT NULL, `Total` int UNSIGNED NOT NULL)";

#Reference Questions & Assistance
$patronassistance_table = "CREATE TABLE `SRPatronAssistance` (`AssistanceCountID` int UNSIGNED AUTO_INCREMENT PRIMARY KEY, `Month` enum('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') NOT NULL, `Year` YEAR NOT NULL, `AssistanceType` enum('Reference', 'Tutorial') NOT NULL, `Total` smallint UNSIGNED NOT NULL)";

##State Report Tables
#Sections Table
$report_sections = "CREATE TABLE `SRSections` (`SectionID` tinyint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `SectionDescription` varchar(75) NOT NULL)";

#Questions Table
$report_questions = "CREATE TABLE `SRQuestions` (`QuestionID` smallint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `SectionID` tinyint UNSIGNED NOT NULL, `Number` varchar(10) NOT NULL, `Question` varchar(550) NOT NULL, `Source` enum('Direct', 'Query', 'Multiple', 'Calculation') NOT NULL, `Query` text DEFAULT NULL, `Format` enum('Integer', 'Currency', 'Text') NOT NULL, UNIQUE (`SectionID`, `Number`), INDEX section_fk (`SectionID`), FOREIGN KEY (`SectionID`) REFERENCES `SRSections`(`SectionID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Sections Data
$report_sections_prepared_statement = "INSERT INTO SRSections (`SectionID`, `SectionDescription`) VALUES (?, ?)";
$report_sections_data = array();
array_push($report_sections_data, array(1, 'Identification'));
array_push($report_sections_data, array(2, 'Service Outlets'));
array_push($report_sections_data, array(3, 'Annual Report Data'));
array_push($report_sections_data, array(4, 'Referenda'));
array_push($report_sections_data, array(5, 'Current Library Board'));
array_push($report_sections_data, array(6, 'Facility/Facilities'));
array_push($report_sections_data, array(7, 'Assets and Liabilities'));
array_push($report_sections_data, array(8, 'Operating Receipts by Source'));
array_push($report_sections_data, array(9, 'Staff Expenditures'));
array_push($report_sections_data, array(10, 'Collection Expenditures'));
array_push($report_sections_data, array(11, 'Other Expenditures'));
array_push($report_sections_data, array(12, 'Capital Revenue'));
array_push($report_sections_data, array(13, 'Personnel'));
array_push($report_sections_data, array(14, 'Library Visits'));
array_push($report_sections_data, array(15, 'Programs, Self-Directed Activities and Attendance and Views'));
array_push($report_sections_data, array(16, 'Registered Users'));
array_push($report_sections_data, array(17, 'Resources Owned'));
array_push($report_sections_data, array(18, 'Use of Resources'));
array_push($report_sections_data, array(19, 'Patron Services'));
array_push($report_sections_data, array(20, 'Automation'));
array_push($report_sections_data, array(21, 'Internet'));
array_push($report_sections_data, array(22, 'E-Rate'));
array_push($report_sections_data, array(23, 'Staff Development and Training'));
array_push($report_sections_data, array(24, 'Comments and Suggestions'));
array_push($report_sections_data, array(25, 'Public Library District Secretary\'s Audit'));
array_push($report_sections_data, array(99, 'COVID-19 Questions'));

#Questions Data
$report_questions_prepared_statement = "INSERT INTO `SRQuestions` (`SectionID`, `Number`, `Question`, `Source`, `Query`, `Format`) VALUES (?, ?, ?, ?, ?, ?, ?)";
$report_questions_data = array();
array_push($report_questions_data, array(1, '01', 'ISL Control #', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '02', 'ISL Branch #', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '03a', 'FSCS ID', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '03b', 'FSCS_SEQ', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '04a', 'Legal Name of Library', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '04b', 'If the library\'s name has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '05a', 'Facility Street Address', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '05b', 'If the facility\'s street address has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '05c', 'Was this a physical change?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '06a', 'Facility City', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '06b', 'If the facility\'s city has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '07a', 'Facility ZIP', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '07b', 'If the facility\'s ZIP code has changed, enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '08a', 'If the facility\'s mailing address has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '08b', 'If the facility\'s mailing address has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '09a', 'Mailing City', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '09b', 'If the facility\'s mailing city has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '10a', 'Mailing ZIP', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '11a', 'Library Telephone Number', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '11b', 'If the telephone number has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '12a', 'Library FAX Number', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '12b', 'If the fax number has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '13', 'Website', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '14', 'Name', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '15', 'Title', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '16', 'Library Director\'s E-mail', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '17a', 'Type of library', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '17b', 'If the library type has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '18', 'Is the main library a combined public and school library?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '19', 'Does your library contract with another library to RECEIVE ALL your library services?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '20', 'IF YES, list the name(s) of the library(ies) with whom you contract (Enter each in a separate repeating field)', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '21a', 'County in which the administrative entity is located', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '21b', 'If the administrative entity\'s county has changed, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '22a', 'Did the administrative entity\'s legal service area boundaries change during the past year?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '22b', 'IF YES, indicate the reason for the boundary change', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '23a', 'Population residing in tax base (Use the latest official federal census figure)', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(1, '23b', 'If the population residing in the tax base has had a LEGAL change, then enter the updated answer here', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '23c', 'Documentation of legal population change', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '24', 'If the population has changed from the prior year\'s answer, then indicate the reason', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '25a', 'This library is currently a member of what Illinois library system?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '25b', 'If the library\'s system has changed, then enter the updated answer here.', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '26', 'Does this library have an organized collection of printed or other library materials, or a combination thereof?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '27', 'Does this library have paid staff?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '28', 'Does this library have an established schedule in which services of the staff are available to the public?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '29', 'Does the library have the facilities necessary to support such a collection, staff, and schedule?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '30', 'Is this library supported in whole or in part with public funds?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(1, '31', 'Does this public library meet ALL the criteria of the PLSC public library definition?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(2, '01a', 'Total number of bookmobiles', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(2, '01b', 'Total number of branch libraries', 'Query', 'SELECT COUNT(`LibraryID`) AS `Total` FROM `LibraryInfo`', 'Integer'));
array_push($report_questions_data, array(2, '02a', 'Are any of the branch libraries a combined public and school library?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(2, '02b', 'If YES, provide the name of the branch or branches in the box provided', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(2, '03a', 'Service Outlet Legal Name', 'Multiple', '1|outletInfo|LegalName', 'Text'));
array_push($report_questions_data, array(2, '03b', 'If the outlet\'s legal name has changed, then enter the updated answer here.', 'Multiple', '1|outletInfo|NewLegalName', 'Text'));
array_push($report_questions_data, array(2, '03c', 'Was this an official name change?', 'Multiple', '1|outletInfo|LegalNameChange', 'Text'));
array_push($report_questions_data, array(2, '04', 'ISL Control #', 'Multiple', '1|outletInfo|ISLControl', 'Text'));
array_push($report_questions_data, array(2, '05', 'ISL Branch #', 'Multiple', '1|outletInfo|ISLBranch', 'Text'));
array_push($report_questions_data, array(2, '06a', 'Street Address', 'Multiple', '1|outletInfo|LibraryAddress', 'Text'));
array_push($report_questions_data, array(2, '06b', 'If the outlet\'s street address has changed, then enter the updated answer here.', 'Multiple', '1|outletInfo|NewLibraryAddress', 'Text'));
array_push($report_questions_data, array(2, '06c', 'Was this a physical location change', 'Multiple', '1|outletInfo|PhysicalAddressChange', 'Text'));
array_push($report_questions_data, array(2, '07a', 'City', 'Multiple', '1|outletInfo|LibraryCity', 'Text'));
array_push($report_questions_data, array(2, '07b', 'If the outlet\'s city has changed, then enter the updated answer here', 'Multiple', '1|outletInfo|NewLibraryCity', 'Text'));
array_push($report_questions_data, array(2, '08a', 'ZIP code', 'Multiple', '1|outletInfo|LibraryZIP', 'Text'));
array_push($report_questions_data, array(2, '08b', 'If the outlet\'s ZIP code has changed, then enter the updated answer here', 'Multiple', '1|outletInfo|NewLibraryZIP', 'Text'));
array_push($report_questions_data, array(2, '09a', 'County', 'Multiple', '1|outletInfo|LibraryCounty', 'Text'));
array_push($report_questions_data, array(2, '09b', 'If the outlet\'s county has changed, then enter the updated answer here', 'Multiple', '1|outletInfo|NewLibraryCounty', 'Text'));
array_push($report_questions_data, array(2, '10a', 'Telephone', 'Multiple', '1|outletInfo|LibraryTelephone', 'Text'));
array_push($report_questions_data, array(2, '10b', 'If the outlet\'s phone number has changed, then enter the updated answer here.', 'Multiple', '1|outletInfo|NewLibraryTelephone', 'Text'));
array_push($report_questions_data, array(2, '11a', 'Square Footage of Outlet', 'Multiple', '1|outletInfo|SquareFootage', 'Integer'));
array_push($report_questions_data, array(2, '11b', 'If the facility\'s square footage has changed, then enter the updated answer here', 'Multiple', '1|outletInfo|NewSquareFootage', 'Text'));
array_push($report_questions_data, array(2, '11c', 'Indicate the reason for the change/variance in square footage of this annual report as compared to the previous annual report', 'Multiple', '1|outletInfo|SquareFootageReason', 'Text'));
array_push($report_questions_data, array(2, '12', 'Total public service hours PER YEAR for this service outlet', 'Multiple', '1|outletInfo|TotalHours', 'Integer'));
array_push($report_questions_data, array(2, '13', 'Total number of weeks, during the fiscal year, this service outlet was open for service to the public', 'Multiple', '1|outletInfo|TotalWeeks', 'Integer'));
array_push($report_questions_data, array(2, '14', 'Total annual attendance/visits in the outlet', 'Multiple', '1|outletInfo|TotalVisits', 'Integer'));
array_push($report_questions_data, array(3, '01', 'Fiscal Year Start Date (mm/dd/year)', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(3, '02', 'Fiscal Year End Date (mm/dd/year)', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(3, '03', 'Number of months in this fiscal year', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(3, '04', 'Name of person preparing this annual report', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(3, '05', 'Telephone Number of Person Preparing Report', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(3, '06', 'FAX Number', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(3, '07', 'E-Mail Address', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(4, '01a', 'Was your library involved in a referendum during the fiscal year reporting period?', 'Query', 'SELECT IF(COUNT(ReferendumID) > 0, "Yes", "No") AS `Total` FROM `Referenda` WHERE `ReferendumDate` BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Text'));
array_push($report_questions_data, array(4, '01b', 'How many referenda was your library involved in?', 'Query', 'SELECT COUNT(ReferendumID) AS `Total` FROM `Referenda` WHERE `ReferendumDate` BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(4, '02', 'Referendum Type', 'Multiple', '2|referendumInfo|ReferendumType', 'Text'));
#array_push($report_questions_data, array(4, '03', 'Examples are: Annexation, Bond Issue, District Establishment, Tax Increase', 'Multiple', '2|referendumInfo|ReferendumType', 'Text'));
array_push($report_questions_data, array(4, '04', 'Referendum Date (mm/dd/year)', 'Multiple', '2|referendumInfo|ReferendumDate', 'Text'));
array_push($report_questions_data, array(4, '05', 'Passed or Failed?', 'Multiple', '2|referendumInfo|ReferendumPassed', 'Text'));
array_push($report_questions_data, array(4, '06', 'If PASSED, enter the effective date (mm/dd/year)', 'Multiple', '2|referendumInfo|ReferendumDatePassed', 'Text'));
array_push($report_questions_data, array(4, '07', 'Referendum ballot language documentation', 'Multiple', '2|referendumInfo|ReferendumLanguage', 'Text'));
array_push($report_questions_data, array(5, '01', 'Total number of board seats', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(5, '02a', 'Total number of vacant board seats', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(5, '02b', 'If there are vacancies, please explain', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(5, '03', 'This public library board of trustees attests that the current board is legally established, organized, and the terms of office for library trustees are all unexpired.', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(5, '04', 'IF NO, please explain', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(5, '05', 'Name', 'Multiple', '3|trusteeInfo|TrusteeName', 'Text'));
array_push($report_questions_data, array(5, '06', 'Trustee Position', 'Multiple', '3|trusteeInfo|TrusteePosition', 'Text'));
array_push($report_questions_data, array(5, '07', 'Present Term Ends (mm/year)', 'Multiple', '3|trusteeInfo|TrusteeTermEnds', 'Text'));
array_push($report_questions_data, array(5, '08', 'Telephone Number', 'Multiple', '3|trusteeInfo|TrusteeTelephone', 'Text'));
array_push($report_questions_data, array(5, '09', 'E-Mail Address', 'Multiple', '3|trusteeInfo|TrusteeEmail', 'Text'));
array_push($report_questions_data, array(5, '10', 'Home Address', 'Multiple', '3|trusteeInfo|TrusteeAddress', 'Text'));
array_push($report_questions_data, array(5, '11', 'City', 'Multiple', '3|trusteeInfo|TrusteeCity', 'Text'));
array_push($report_questions_data, array(5, '12', 'State', 'Multiple', '3|trusteeInfo|TrusteeState', 'Text'));
array_push($report_questions_data, array(5, '13', 'ZIP code', 'Multiple', '3|trusteeInfo|TrusteeZIP', 'Text'));
array_push($report_questions_data, array(6, '01', 'Does the library address the environmental needs of patrons on the autism spectrum?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(6, '01b', 'If so, please describe', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(6, '02', 'Total Number of Meeting Rooms', 'Query', 'SELECT COUNT(`SpaceID`) AS `Total` FROM `SRSpaces` WHERE `SpaceType` = \'Meeting Room\'', 'Integer'));
array_push($report_questions_data, array(6, '02b', 'Total number of times meeting room(s) used by the public during the fiscal year', 'Query', 'SELECT SUM(su.`Total`) AS `Total` FROM `SRSpaceUse` su INNER JOIN `SRSpaces` s ON su.`SpaceID` = s.`SpaceID` WHERE (s.`SpaceType` = \'Meeting Room\') AND DATE(CONCAT(su.`Year`, IF(su.`Month`+0 < 10, \'-0\', \'-\'), su.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\'', 'Integer'));
array_push($report_questions_data, array(6, '03', 'Total Number of Study Rooms', 'Query', 'SELECT COUNT(`SpaceID`) AS `Total` FROM `SRSpaces` WHERE `SpaceType` = \'Study Room\'', 'Integer'));
array_push($report_questions_data, array(6, '03b', 'Total number of times study room(s) used by the public during the fiscal year', 'Query', 'SELECT SUM(su.`Total`) AS `Total` FROM `SRSpaceUse` su INNER JOIN `SRSpaces` s ON su.`SpaceID` = s.`SpaceID` WHERE (s.`SpaceType` = \'Study Room\') AND DATE(CONCAT(su.`Year`, IF(su.`Month`+0 < 10, \'-0\', \'-\'), su.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\'', 'Integer'));
array_push($report_questions_data, array(7, '01', 'What is the estimated current fair market value fo the library\'s real estate (land and buildings including garages, sheds, etc.)?', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(7, '02', 'During the last fiscal year, did the library acquire any real and/or personal property?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '03', 'Purchase', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '04', 'Legacy', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '05', 'Gift', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '06', 'Other', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '07', 'Provide a general description of the property acquired', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '08', 'Does your library have fiscal accumulations (reserve funds, outstanding fund balances, etc.)?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '09', 'IF YES, then provide a statement that details the dollar amount(s) and the reason(s) for the fiscal accumulations', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '10', 'Does your library have any outstanding liabilities including bonds, judgements, settlements, etc.?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '11', 'IF YES, what is the total amount of the outstanding liabilities?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(7, '12', 'IF YES, then prepare a statement that identifies each outstanding liability and its specific dollar amount', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '01', 'Local government (includes all local government funds designated by the community, district, or region and available for expenditure by the public library, except capital income from bond sales which must be reported in 12.1a only)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Local Government Income\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(8, '01a', 'Is this library\'s annual tax levy/fiscal appropriation subject to tax caps [the Property Tax Extension Limitation Law, 36 ILCS 200/18-185, et seq.]?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '01b', 'Local government funds for the ensuing or upcoming/current fiscal year (includes all local government funds designated by the community, district, or region and avialable for expenditure by the public library, except capital income from bond sales which must be reported in 12.1a only)', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '02', 'Per capita grant', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Per Capita Grant\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(8, '03', 'Equalization aid grant', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '04', 'Personal property replacement tax', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '05', 'Other State Government funds received', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '06', 'If Other, please specify', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '07', 'Total State Government Funds (8.2 + 8.3 + 8.4 + 8.5)', 'Calculation', '8.02,8.03,8.04,8.05', 'Currency'));
array_push($report_questions_data, array(8, '08', 'LSTA funds received', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '09', 'E-Rate funds received', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '10', 'Other federal funds received', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '11', 'If Other, please specify', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '12', 'Total Federal Government Funds (8.8 + 8.9 + 8.10)', 'Calculation', '8.08,8.09,8.10', 'Currency'));
array_push($report_questions_data, array(8, '13', 'Monetary Gifts and Donations', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Monetary Gifts\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(8, '14', 'Other receipts intended to be used for operating expenditures', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE ((bc.`CategoryDescription` = \'Other Income\') OR (bc.`CategoryDescription` = \'Non-Resident Card Fees\')) AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(8, '15', 'TOTAL all other receipts (8.13 + 8.14)', 'Calculation', '8.13,8.14', 'Currency'));
array_push($report_questions_data, array(8, '16', 'Other non-capital receipts placed in reserve funds', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '17', 'TOTAL receipts (8.1 + 8.7 + 8.12 + 8.15)', 'Calculation', '8.01,8.07,8.12,8.15', 'Currency'));
array_push($report_questions_data, array(8, '18a', 'The library safeguards its funds using which option?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '18b', 'Proof of Certificate of Insurance for Library Funds', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '19', 'What is the coverage amount of either the surety bond OR the insurance policy/insurance instrument?', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(8, '20', 'Is the amount of the surety bond, insurance policy or other insurance instrument in compliance with library law?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(8, '21', 'The designated custodian of the library\'s funds is:', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(9, '01', 'Salaries and wages for all library staff', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE bc.(`CategoryDescription` = \'Salaries and Wages\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(9, '02a', 'Fringe benefits, for all library staff, paid for from either the library\'s or the municipal corporate authority\'s appropriation', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Fringe Benefits\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(9, '02b', 'If this library answered question 9.2a as zero, please select and explanation from the drop-down box', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(9, '03', 'Total Staff Expenditures (9.1 + 9.2)', 'Calculation', '9.01,9.02', 'Currency'));
array_push($report_questions_data, array(10, '01', 'Printed Materials (books, newspapers, etc.)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Print Materials\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(10, '02', 'Electronic Materials (e-books, databases, etc.)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Electronic Materials\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(10, '03a', 'Other Materials (CDs, DVDs, video games, etc.)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Other Materials\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(10, '03b', 'Please provide an explanation of the other types of material expenditures', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(10, '04', 'TOTAL Collection Expenditures (10.1 + 10.2 + 10.3)', 'Calculation', '10.01,10.02,10.03', 'Currency'));
array_push($report_questions_data, array(11, '01', 'All other operating expenditures not included above (supplies, utilities, legal fees, etc.)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE ((bc.`CategoryDescription` = \'Other Operating Expenditures\') OR (bc.`CategoryDescription` = \'Staff Development\')) AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(11, '02', 'TOTAL operating expenditures (9.3 + 10.4 + 11.1)', 'Calculation', '9.03,10.04,11.01', 'Currency'));
array_push($report_questions_data, array(12, '01a', 'Local Government: Capital income from Bond Sales', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(12, '01b', 'Local Government: Other', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(12, '01c', 'Total Local Government (12.1a + 12.1b)', 'Calculation', '12.01a,12.01b', 'Currency'));
array_push($report_questions_data, array(12, '02', 'State Government', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(12, '03', 'Federal Government', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(12, '04', 'Other Capital Revenue', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(12, '05', 'If Other, please specify', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(12, '06', 'Total Capital Revenue (12.1c + 12.2 + 12.3 + 12.4)', 'Calculation', '12.01c,12.02,12.03,12.04', 'Currency'));
array_push($report_questions_data, array(12, '07', 'Total Capital Expenditures', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Capital Expenditures\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(13, '01', 'Position Title', 'Multiple', '4|mlsPositionInfo|PositionTitle', 'Text'));
array_push($report_questions_data, array(13, '02', 'Primary Work Area', 'Multiple', '4|mlsPositionInfo|PositionWorkArea', 'Text'));
array_push($report_questions_data, array(13, '03', 'Hourly Rate', 'Multiple', '4|mlsPositionInfo|PositionHourlyRate', 'Currency'));
array_push($report_questions_data, array(13, '04', 'Total Hours/Week', 'Multiple', '4|mlsPositionInfo|PositionWeeklyHours', 'Integer'));
array_push($report_questions_data, array(13, '05', 'Total Group A: FTE ALA-MLS (13.4/40)', 'Query', NULL, 'Integer'));
array_push($report_questions_data, array(13, '06', 'Position Title', 'Multiple', '5|otherPositionInfo|PositionTitle', 'Text'));
array_push($report_questions_data, array(13, '07', 'Primary Work Area', 'Multiple', '5|otherPositionInfo|PositionWorkArea', 'Text'));
array_push($report_questions_data, array(13, '08', 'Education Level', 'Multiple', '5|otherPositionInfo|PositionEducation', 'Text'));
array_push($report_questions_data, array(13, '09', 'Hourly Rate', 'Multiple', '5|otherPositionInfo|PositionHourlyRate', 'Currency'));
array_push($report_questions_data, array(13, '10', 'Total Hours/Week', 'Multiple', '5|otherPositionInfo|PositionWeeklyHours', 'Integer'));
array_push($report_questions_data, array(13, '11', 'Total Group B: FTE Other Librarians (13.10/40)', 'Query', NULL, 'Integer'));
array_push($report_questions_data, array(13, '12', 'Total FTE Librarians (13.5 + 13.11)', 'Calculation', '13.05,13.11', 'Integer'));
array_push($report_questions_data, array(13, '13', 'Total hours worked in a typical week by all Group C employees', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '14', 'Minimum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '15', 'Maximum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '16', 'Total FTE Group C employees (13.13/40)', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '17', 'Total Hours worked in a typical week by all Group D employees', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '18', 'Minimum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '19', 'Maximum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '20', 'Total FTE Group D employees (13.17/40)', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '21', 'Total hours worked in a typical week by all GROUP E employees', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '22', 'Minimum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '23', 'Maximum hourly rate actually paid', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(13, '24', 'Total FTE Group E employees (13.21/40)', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(13, '25', 'Total FTE Other Paid Employees from Groups C, D, and E (13.16 + 13.20 + 13.24)', 'Calculation', '13.16,13.20,13.24', 'Integer'));
array_push($report_questions_data, array(13, '26', 'Total FTE Paid Employees (13.12 + 13.25)', 'Calculation', '13.12,13.25', 'Integer'));
array_push($report_questions_data, array(13, '27', 'Position Title', 'Multiple', '6|vacancyPositionInfo|PositionTitle', 'Text'));
array_push($report_questions_data, array(13, '28', 'Primary Work Area', 'Multiple', '6|vacancyPositionInfo|PositionWorkArea', 'Text'));
array_push($report_questions_data, array(13, '29', 'Education Level', 'Multiple', '6|vacancyPositionInfo|PositionEducation', 'Text'));
array_push($report_questions_data, array(13, '30', 'Total Hours/Week', 'Multiple', '6|vacancyPositionInfo|PositionWeeklyHours', 'Integer'));
array_push($report_questions_data, array(13, '31', 'Number of Weeks Vacant during report period.', 'Multiple', '6|vacancyPositionInfo|WeeksVacant', 'Integer'));
array_push($report_questions_data, array(13, '32', 'Annual Salary Range Minimum', 'Multiple', '6|vacancyPositionInfo|PositionSalaryMinimum', 'Currency'));
array_push($report_questions_data, array(13, '33', 'Annual Salary Range Maximum', 'Multiple', '6|vacancyPositionInfo|PositionSalaryMaximum', 'Currency'));
array_push($report_questions_data, array(13, '34', 'Position Title', 'Multiple', '7|newPositionInfo|PositionTitle', 'Text'));
array_push($report_questions_data, array(13, '35', 'Primary Work Area', 'Multiple', '7|newPositionInfo|PositionWorkArea', 'Text'));
array_push($report_questions_data, array(13, '36', 'Education Level', 'Multiple', '7|newPositionInfo|PositionEducation', 'Text'));
array_push($report_questions_data, array(13, '37', 'Total Hours/Week', 'Multiple', '7|newPositionInfo|PositionWeeklyHours', 'Integer'));
array_push($report_questions_data, array(13, '38', 'Current Status: Filled or Unfilled', 'Multiple', '7|newPositionInfo|PositionFilled', 'Text'));
array_push($report_questions_data, array(13, '39', 'Date Filled (mm/year, if applicable)', 'Multiple', '7|newPositionInfo|DatePositionFilled', 'Text'));
array_push($report_questions_data, array(13, '40', 'Position Title', 'Multiple', '8|eliminatedPositionInfo|PositionTitle', 'Text'));
array_push($report_questions_data, array(13, '41', 'Primary Work Area', 'Multiple', '8|eliminatedPositionInfo|PositionWorkArea', 'Text'));
array_push($report_questions_data, array(13, '42', 'Education Level', 'Multiple', '8|eliminatedPositionInfo|PositionEducation', 'Text'));
array_push($report_questions_data, array(13, '43', 'Total Hours/Week', 'Multiple', '8|eliminatedPositionInfo|PositionWeeklyHours', 'Integer'));
array_push($report_questions_data, array(13, '44', 'Date Eliminated (mm/year)', 'Multiple', '8|eliminatedPositionInfo|DatePositionEliminated', 'Text'));
array_push($report_questions_data, array(13, '45', 'Last Annual Salary Paid', 'Multiple', '8|eliminatedPositionInfo|PositionLastSalary', 'Currency'));
array_push($report_questions_data, array(13, '46', 'Reason Eliminated', 'Multiple', '8|eliminatedPositionInfo|ReasonPositionEliminated', 'Text'));
array_push($report_questions_data, array(14, '01', 'Total annual visits/attendance in the library [auto filled]', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRVisits` WHERE DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(14, '01a', 'Library Visits Reporting Method', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(15, '01', 'Number of Synchronous Programs for Children Age 0-5', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'0-5\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '02', 'Attendance at Synchronous Programs for Children Age 0-5', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'0-5\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '03', 'Number of Children\'s Self-Directed (Asynchronous) Activities Ages 0-5', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'0-5\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '04', 'Participants at Children\'s Self-Directed (Asynchronous) Activites Ages 0-5', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'0-5\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '05', 'Number of Synchronous Programs for Children Age 6-11', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'6-11\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '06', 'Attendance at Synchronous Programs for Children Age 6-11', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'6-11\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '07', 'Number of Children\'s Self-Directed (Asynchronous) Activities Ages 6-11', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'6-11\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '08', 'Participants at Children\'s Self-Directed (Asynchronous) Activites Ages 6-11', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'6-11\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '09', 'Total Number of Children\'s Synchronous Programs', 'Calculation', '15.01,15.05', 'Integer'));
array_push($report_questions_data, array(15, '10', 'Total Attendance at Children\'s Synchronous Programs', 'Calculation', '15.02,15.06', 'Integer'));
array_push($report_questions_data, array(15, '11', 'Total Number of Children\'s Self-Directed (Asynchronous) Activites', 'Calculation', '15.03,15.07', 'Integer'));
array_push($report_questions_data, array(15, '12', 'Participants at Children\'s Self-Directed (Asynchronous) Activities', 'Calculation', '15.04,15.08', 'Integer'));
array_push($report_questions_data, array(15, '13', 'Number of Synchronous Program Sessions for Young Adults Ages 12-18', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'12-18\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '14', 'Attendance at Synchronous Programs for Young Adults Ages 12-18', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'12-18\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '15', 'Number of Young Adult Self-Directed (Asynchronous) Activities Ages 12-18', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'12-18\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '16', 'Participants at Young Adult Self-Directed (Asynchronous) Activities Ages 12-18', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'12-18\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '17', 'Number of Synchronous Programs for Adults Ages 19 and over', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'19+\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '18', 'Attendance at Synchronous Programs for Adults Ages 19 and over', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'19+\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '19', 'Number of Self-Directed (Asynchronous) Activities for Adults Ages 19 and over', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'19+\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '20', 'Participants at Self-Directed (Asynchronous) Activities for Adults Ages 19 and over', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'19+\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '21', 'Number of Synchronous Programs for General Interest (All Ages)', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'All Ages\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '22', 'Attendance at Synchronous Programs for General Interest (All Ages)', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'All Ages\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '23', 'Number of Self-Directed (Asynchronous) Activities for General Interest (All Ages)', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`Age` = \'All Ages\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '24', 'Participants at Self-Directed (Asynchronous) Activities for General Interest (All Ages)', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`Age` = \'All Ages\') AND (`Synchronous` = \'No\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '25', 'Total Number of Synchronous Programs', 'Calculation', '15.09,15.13,15.17,15.21', 'Integer'));
array_push($report_questions_data, array(15, '26', 'Total Attendance at Synchronous Programs', 'Calculation', '15.10,15.14,15.18,15.22', 'Integer'));
array_push($report_questions_data, array(15, '27', 'Total Number of Self-Directed (Asynchronous) Activities', 'Calculation', '15.09,15.15,15.19,15.23', 'Integer'));
array_push($report_questions_data, array(15, '28', 'Total Participants at Self-Directed (Asynchronous) Activities', 'Calculation', '15.10,15.16,15.20,15.24', 'Integer'));
array_push($report_questions_data, array(15, '29', 'Synchronous In-Person OnSite Program Sessions', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`ProgramLocation` = \'Onsite\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '30', 'Synchronous In-Person Onsite Progam Attendance', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`ProgramLocation` = \'Onsite\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '31', 'Synchronous In-Person Offsite Program Sessions', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`ProgramType` = \'Offsite\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '32', 'Synchronous In-Person Offsite Program Attendance', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`ProgramType` = \'Offsite\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '33', 'Synchronous Virtual Program Sessions', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Event\') AND (`ProgramType` = \'Virtual\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '34', 'Synchronous Virtual Program Session Attendance', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPrograms` WHERE (`CountType` = \'Attendance\') AND (`ProgramType` = \'Virtual\') AND (`Synchronous` = \'Yes\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(15, '35', 'Total Synchronous Program Sessions', 'Calculation', '15.29,15.31,15.33', 'Integer'));
array_push($report_questions_data, array(15, '36', 'Total Synchronous Program Session Attendance', 'Calculation', '15.30,15.32,15.34', 'Integer'));
array_push($report_questions_data, array(15, '37', 'Total Number of Asynchronous (Virtual) Program Presentations', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(15, '38', 'Total Views of Asynchronous (Virtual) Program Presentations', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(15, '39a', 'Did the library provide any special programming for patrons on the autism spectrum?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(15, '39b', 'Please describe the programming provided', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(16, '01', 'Total Number of Unexpired Resident Cards', 'Query', 'SELECT `Total` FROM `SRCards` WHERE DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\') AND `CountType` = \'Resident\' ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(16, '02a', 'Total Number of Unexpired Non-Resident Cards', 'Query', 'SELECT `Total` FROM `SRCards` WHERE DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\') AND `CountType` = \'Non-Resident\' ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(16, '02a(1)', 'Of the total in 16.2a, how many Cards for Kids Act cards were issued?', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(16, '02a(2)', 'Of the total in 16.2a, how many Disabled Veterans cards were issued?', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(16, '02b', 'What was the total amount of the fees collected from the sale of non-resident cards during the past fiscal year?', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE bc.`CategoryDescription` = \'Non-Resident Card Fees\' AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(16, '03', 'Total Number of Registered Cards (16.1 + 16.2)', 'Calculation', '16.01,16.02', 'Integer'));
array_push($report_questions_data, array(16, '04', 'Is your library\'s registered user/patron file purged a minimum of one time every three years?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(16, '05', 'Does the library charge overdue fines to any users when they fail to return physical print materials by the date due?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(17, '01', 'Books Held at the end of the fiscal year (volume count)', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Book\') AND (`MaterialType` = \'Physical\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '02', 'Current Print Serial Subscriptions', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Serial Subscription\') AND (`MaterialType` = \'Physical\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '03', 'Total Print Materials (17.1 + 17.2)', 'Calculation', '17.01,17.02', 'Integer'));
array_push($report_questions_data, array(17, '04', 'E-books Held at end of the fiscal year', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Book\') AND (`MaterialType` = \'Digital\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '05a', 'Audio Recordings: Physical Units Held at end of the fiscal year', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Audio\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '05b', 'Audio Recordings: Downloadable Units Held at end of the fiscal year', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Audio\') AND (`MaterialType` = \'Digital\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '06a', 'DVDs/Videos: Physical Units Held at end of the fiscal year', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Video\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '06b', 'DVDs/Videos: Downloadable Units Held at the end of the fiscal yea', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Video\') AND (`MaterialType` = \'Digital\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '06c', 'Other Circulating Physical Items', 'Query', 'SELECT `Total` FROM `SRCollection` WHERE (DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) < DATE(\'|endyear|-|startmonth|-01\')) AND (`Material` = \'Other\') ORDER BY DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) DESC, LIMIT 1', 'Integer'));
array_push($report_questions_data, array(17, '06d', 'Total Physical Items', 'Calculation', '17.03,17.05a,17.06a,17.06c', 'Integer'));
array_push($report_questions_data, array(17, '07', 'Electronic Collections - Local/Other Cooperative Agreements', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(17, '08', 'Electronic Collections - State (State Library)', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(17, '09', 'Total Electronic Collections (17.7 + 17.8)', 'Calculation', '17.07,17.08', 'Integer'));
array_push($report_questions_data, array(18, '01', 'Number of adult materials loaned', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Audience` = \'Adult\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '02', 'Number of young adult materials loaned', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Audience` = \'Young Adult\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '03', 'Number of children\'s materials loaned', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Audience` = \'Children\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '04', 'Total number of materials loaned (18.1 + 18.2 + 18.3)', 'Calculation', '18.01,18.02,18.03', 'Integer'));
array_push($report_questions_data, array(18, '05', 'Books - Physical', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Book\') AND (`MaterialType` = \'Physical\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '06', 'Videos/DVDs - Physical', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Video\') AND (`MaterialType` = \'Physical\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '07', 'Audios (include music) - Physical', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Audio\') AND (`MaterialType` = \'Physical\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '08', 'Magazines/Periodicals - Physical', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Magazine\') AND (`MaterialType` = \'Physical\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '09', 'Other Items - Physical', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Other\') AND (`MaterialType` = \'Physical\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '10', 'Physical Item Circulation (18.5-18.9)', 'Calculation', '18.05,18.06,18.07,18.08,18.09', 'Integer'));
array_push($report_questions_data, array(18, '11', 'Use of Electronic Materials', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` IN (\'Book\',\'Audio\',\'Video\')) AND (`MaterialType` = \'Digital\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '12', 'Total Circulation of Materials (18.10 + 18.11)', 'Calculation', '18.10,18.11', 'Integer'));
array_push($report_questions_data, array(18, '13', 'Successful Retrieval of Electronic Information', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRCirculation WHERE (`Material` = \'Magazine\') AND (`MaterialType` = \'Digital\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '14', 'Electronic Content Use (18.11 + 18.13)', 'Calculation', '18.11,18.13', 'Integer'));
array_push($report_questions_data, array(18, '15', 'Total Collection Use (18.10 + 18.11 + 18.13)', 'Calculation', '18.10,18.11,18.13', 'Integer'));
array_push($report_questions_data, array(18, '16', 'Interlibrary Loans Provided TO other libraries', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRILL WHERE (`ILLRole` = \'Lender\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(18, '17', 'Interlibrary Loans Received FROM other libraries', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRILL WHERE (`ILLRole` = \'Borrower\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(19, '01', 'Total Annual Reference Transactions', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPatronAssistance` WHERE (`AssistanceType` = \'Reference\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(19, '01a', 'Reference Transactions Reporting Method', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(19, '02', 'Total Annual One-on-One Tutorials', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM `SRPatronAssistance` WHERE (`AssistanceType` = \'Tutorial\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(20, '01', 'Total number of ALL computers in the library', 'Query', 'SELECT COUNT(`ComputerID`) AS `Total` FROM `SRComputers`', 'Integer'));
array_push($report_questions_data, array(20, '02', 'Total number of PUBLIC USE (Internet and non-Internet accessible) computers in the library', 'Query', 'SELECT COUNT(`ComputerID`) AS `Total` FROM `SRComputers` WHERE `User` = \'Public\'', 'Integer'));
array_push($report_questions_data, array(20, '03', 'Is your library\'s catalog automated?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(20, '04', 'Is your library\'s catalog accessible via the web?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(20, '05', 'Does your library have a telecommunications messaging device for the hearing impaired?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '01', 'Does your library have Internet access?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '02a', 'What is the maximum speed of your library\'s Internet connection (Select one)', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '02b', 'If Other, please specify', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '03', 'What Is the monthly cost of the library\'s internet access?', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(21, '04', 'Number of Internet Computers Available for Public Use', 'Query', 'SELECT COUNT(`ComputerID`) AS `Total` FROM `SRComputers` WHERE (`User` = \'Public\') AND (`InternetAccess` = \'Yes\')', 'Integer'));
array_push($report_questions_data, array(21, '05', 'Number of Uses (Sessions) of Public Internet Computers Per Year', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRTechnologyCounts WHERE (`TechnologyType` = \'Public Internet\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(21, '05a', 'Reporting Method for Number of Uses of Public Internet Computers Per Year', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '06', 'Wireless Sessions Per Year', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRTechnologyCounts WHERE (`TechnologyType` = \'Wireless Access\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(21, '06a', 'Reporting Method for Wireless Sessions', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '07', 'Does your library utilize Internet filters on some or all of the public access computers?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '08', 'Does your library provide instruction (workshops, classes) to patrons on the use of the internet?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(21, '09', 'Number of website visits or sessions to your library website', 'Query', 'SELECT SUM(`Total`) AS `Total` FROM SRTechnologyCounts WHERE (`TechnologyType` = \'Website Sessions\') AND DATE(CONCAT(`Year`, IF(`Month`+0 < 10, \'-0\', \'-\'), `Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Integer'));
array_push($report_questions_data, array(22, '01', 'Did your library apply directly for E-rate discounts for the fiscal year?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(22, '02a', 'If YES, did your library apply for Category 1, Category 2 or both?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(22, '02b', 'If YES, what is the dollar amount that your library was awarded for the fiscal year report period?', 'Direct', NULL, 'Currency'));
array_push($report_questions_data, array(22, '03', 'If NO, why did your library NOT participate in the E-rate program?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(23, '01', 'How much money did your library spend on staff development and training this fiscal year (Round answer to the nearest whole dollar.)', 'Query', 'SELECT SUM(ba.`Total` AS Total FROM `SRBudgetAdjustments` ba INNER JOIN `SRBudgetCategories` bc ON ba.`CategoryID` = bc.`CategoryID` WHERE (bc.`CategoryDescription` = \'Staff Development\') AND DATE(CONCAT(bc.`Year`, IF(bc.`Month`+0 < 10, \'-0\', \'-\'), bc.`Month`+0, \'-01\')) BETWEEN DATE(\'|startyear|-|startmonth|-01\') AND DATE(\'|endyear|-|startmonth|-01\')', 'Currency'));
array_push($report_questions_data, array(23, '02', 'Does the above amount include travel expenses?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(23, '03', 'How many hours of training did employees receive this year?', 'Direct', NULL, 'Integer'));
array_push($report_questions_data, array(23, '04', 'Does your library provide training to enable staff to better serve their patrons on the autism spectrum?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(23, '05', 'Would you like to receive autism training at your library?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(24, '01', 'Are there any other factors that may have affected your library\'s annual report data of which you would like to make us aware?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(24, '02', 'Are there any unique programs or services your library provided during the report period of which you would like to make us aware?', 'Direct', NULL, 'Text'));
array_push($report_questions_data, array(24, '03', 'Please provide any comments, suggestions or concers about the Illinois Public Library Annual Report (IPLAR)', 'Direct', NULL, 'Text'));

#State Reporting Collected Data
#The Iteration field is for handling questions with multiple sets of answers, e.g. list of board members
$report_data = "CREATE TABLE `SRData` (`ReportYear` YEAR NOT NULL, `QuestionID` smallint UNSIGNED NOT NULL, 'Iteration' tinyint UNSIGNED DEFAULT 0, `IntegerData` int UNSIGNED NULL, `CurrencyData` decimal(10,2) NULL, `TextData` text NULL, PRIMARY KEY(`ReportYear`, `QuestionID`, `Iteration`), INDEX srquestion_fk (`QuestionID`), FOREIGN KEY (`QuestionID`) REFERENCES `SRQuestions`(`QuestionID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Table for keeping track of user-created tables
$custom_table_list = "CREATE TABLE `CustomTables` (`CustomTableID` smallint UNSIGNED AUTO_INCREMENT PRIMARY KEY, 'TableName' VARCHAR(50) NOT NULL, `SectionID` = tinyint UNSIGNED NOT NULL, `EntryMethod` enum('Manual', 'Database', 'File') NOT NULL, `CollectionFrequency` enum('PerInstance','Daily','Monthly','Static') NOT NULL, `InActiveUse` enum('Yes','No') NOT NULL, INDEX customsection_fk (`SectionID`), FOREIGN KEY (`SectionID`) REFERENCES `SRSections`(`SectionID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Table for managing external database relationships
$custom_table_dbs = "CREATE TABLE `CustomTableDBs` (`CustomTableDBID` smallint UNSIGNED AUTO_INCREMENT PRIMARY KEY, `CustomTableID` smallint UNSIGNED NOT NULL, `Database` varchar(50) NOT NULL, `ImportQuery` text NOT NULL, INDEX customtable_dbs_fk (`CustomTableID`), FOREIGN KEY (`CustomTableID`) REFERENCES `CustomTables` (`CustomTableID`) ON UPDATE CASCADE ON DELETE CASCADE)";

#Table for managing file relationships
$custom_table_files = "CREATE TABLE `CustomTableFiles` (`TableFileMatchID` UNSIGNED AUTO_INCREMENT PRIMARY KEY, `CustomTableID` smallint UNSIGNED NOT NULL, `FileType` enum('CSV','Tab','XLS','XLSX') NOT NULL, `FileFieldMappings` text NOT NULL, INDEX customtable_files_fk (`CustomTableID`), FOREIGN KEY (`CustomTableID`) REFERENCES `CustomTables` (`CustomTableID`) ON UPDATE CASCADE ON DELETE CASCADE)";

//// Relationship Lookup Query (reference)
// SELECT `TABLE_NAME`, `COLUMN_NAME`, `REFERENCED_TABLE_NAME`, `REFERENCED_COLUMN_NAME` FROM 
// `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` WHERE `TABLE_SCHEMA` = SCHEMA() AND 
// `REFERENCED_TABLE_NAME` IS NOT NULL

?>