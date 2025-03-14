<?php
//These are shared functions, primarily for managing report question data for 
//questions that are repeatable for multiple instances of a counted element

function outletInfo($db, $fiscalyear) {
    $data = array();
    #Get fiscal year month
    $monthinfo = $db->query("SELECT `FYMonth`+0 FROM `LibraryInfo` WHERE `Branch` = 0");
    $fymonth = $monthinfo->fetch_column();

    #Get basic Outlet info
    $current_info = $db->query("SELECT * FROM `LibraryInfo`");
    while ($library = $current_info->fetch_assoc()) {
        $currentid = $library['LibraryID'];
        $old_info = $db->query("SELECT * FROM `LibraryChanges` WHERE `LibraryID` = $currentid AND `FYChanges` = $fiscalyear");

        #Set values as if there were no changes (which likely there aren't)
        foreach ($library as $key => $value) {
            if ($key != 'LibraryID') {
                $data[$currentid][$key] = $value;
            }
        }

        #If there are any changes, note those and move the current value to the new value
        if ($old_info->num_rows == 1) {
            $oldlibrary = $old_info->fetch_assoc();
            if (isset($oldlibrary['LegalName'])) {
                $data[$currentid]['NewLegalName'] = $data[$currentid]['LegalName'];
                $data[$currentid]['LegalName'] = $oldlibrary['LegalName'];
                $data[$currentid]['LegalNameChange'] = $oldlibrary['LegalNameChange'];
            }
            if (isset($oldlibrary['LibraryAddress'])) {
                $data[$currentid]['NewLibraryAddress'] = $data[$currentid]['LibraryAddress'];
                $data[$currentid]['LibraryAddress'] = $oldlibrary['LibraryAddress'];
                $data[$currentid]['PhysicalAddressChange'] = $oldlibrary['PhysicalAddressChange'];
            }
            if (isset($oldlibrary['LibraryCity'])) {
                $data[$currentid]['NewLibraryCity'] = $data[$currentid]['LibraryCity'];
                $data[$currentid]['LibraryCity'] = $oldlibrary['LibraryCity'];
            }
            if (isset($oldlibrary['LibraryZIP'])) {
                $data[$currentid]['NewLibraryZIP'] = $data[$currentid]['LibraryZIP'];
                $data[$currentid]['LibraryZIP'] = $oldlibrary['LibraryZIP'];
            }
            if (isset($oldlibrary['LibraryCounty'])) {
                $data[$currentid]['NewLibraryCounty'] = $data[$currentid]['LibraryCounty'];
                $data[$currentid]['LibraryCounty'] = $oldlibrary['LibraryCounty'];
            }
            if (isset($oldlibrary['LibraryTelephone'])) {
                $data[$currentid]['NewLibraryTelephone'] = $data[$currentid]['LibraryTelephone'];
                $data[$currentid]['LibraryTelephone'] = $oldlibrary['LibraryTelephone'];
            }
            if (isset($oldlibrary['SquareFootage'])) {
                $data[$currentid]['NewSquareFootage'] = $data[$currentid]['SquareFootage'];
                $data[$currentid]['SquareFootage'] = $oldlibrary['SquareFootage'];
                $data[$currentid]['SquareFootageReason'] = $oldlibrary['SquareFootageReason'];
            }
        }

        #Calculate total hours and weeks open
        $hours = 0;
        $weeks = 52;
        $nextdate = date_create("2000-01-01");
        $daysinarow = 0;
        $hour_info = $db->query("SELECT `DateImplemented`, `DayOfWeek`, `HoursOpen` FROM `LibraryHours` WHERE `LibraryID` = $currentid ORDER BY `DayOfWeek`, `DateImplemented` DESC");
        $open_hours = array();
        while ($info = $hour_info->fetch_assoc()) {
            #Need to keep these rules in the order they are returned in
            array_push($open_hours, array("DateImplemented" => $info['DateImplemented'], "DayOfWeek" => $info['DayOfWeek'], "HoursOpen" => $info['HoursOpen']));
        }

        #To catch schedule changes for different days of the week we need to loop through each schedule
        #backwards.  To start we need to find the day after the end of the current fiscal year.
        #In most cases the day after the end of the fiscal year is in the same numerical calendar year
        #as the fiscal year, but that's not the case in a Jan-Dec fiscal year.
        if ($fymonth == 1) {
            $startstring = $fiscalyear . "-01-01";
            $endstring = $fiscalyear . "-01-01";
            $decemberstart = $fiscalyear . "-12-31";
            $startdate = date_create($decemberstart);
            $enddate = date_create($endstring);
        } else {
            if ($fymonth < 10) {
                $startstring = $fiscalyear . "-0" . $fymonth . "-01";
                $endstring = $fiscalyear-1 . "-0" . $fymonth . "-01";
            } else {
                $startstring = $fiscalyear . "-" . $fymonth . "-01";
                $endstring = $fiscalyear-1 . "-" . $fymonth . "-01";
            }
            $startdate = date_create($startstring);
            date_sub($startdate,date_interval_create_from_date_string("1 day"));
            $enddate = date_create($endstring);
        }

        $closed_info = $db->query("SELECT `DateClosed`, `HoursClosed` FROM `LibraryClosings` WHERE ((`LibraryID` = $currentid) OR (`LibraryID` IS NULL)) AND (`DateClosed` BETWEEN DATE('$endstring' AND '$startstring') ORDER BY `DateClosed` DESC");
        $closed_hours = array();
        while ($info = $closed_info->fetch_assoc()) {
            $closed_hours[$info['DateClosed']] = $info['HoursClosed'];
        }

        #Loop backwards through a year
        while ($startdate >= $enddate) {
            $weekday = date_format($startdate, 'l');
            #Loop through rules, looking for a rule that matches the day that was
            #Implemented before or on the current date
            foreach ($open_hours as $hourrule) {
                if ($hourrule['DayOfWeek'] == $weekday) {
                    if (date_create($hourrule['DateImplemented']) <= $startdate) {
                        $hours += $hourrule['HoursOpen'];
                        #If a match is found, break out of this foreach
                        #so a duplicate match is not found
                        break;
                    }
                }
            }
            #Now check the same date for closings
            #This also will add extra hours open.  These are stored as negative closed hours
            foreach ($closed_hours as $date_closed => $hours_closed) {
                if (date_create($date_closed) == $startdate) {
                    if ($daysinarow == 0) {
                        #Check if we're building up to an entire week off, starting with Saturday
                        if (date_format($startdate, "l") == "Saturday") {
                            $daysinarow = 1;
                            $nextdate = $startdate;
                            date_sub($nextdate, date_interval_create_from_date_string("1 day"));
                        }
                    } else {
                        if ($startdate == $nextdate) {
                            $daysinarow++;
                            if ($daysinarow == 7) {
                                $weeks--;
                                $daysinarow = 0;
                            }
                            date_sub($nextdate, date_interval_create_from_date_string("1 day"));
                        } else if (date_format($startdate, "l") == "Saturday") {
                            #We've missed a whole bunch of days but we're on a Saturday again
                            $daysinarow = 1;
                            $nextdate = $startdate;
                            date_sub($nextdate, date_interval_create_from_date_string("1 day"));
                        } else {
                            $daysinarow = 0;
                            $nextdate = date_create("2000-01-01");
                        }
                    }
                    #Subtract the hours from the total number of hours open
                    $hours = $hours - $hours_closed;
                    #Remove this date since it won't occur again
                    unset($closed_hours[$date_closed]);
                    #Escape from this loop
                    break;
                }
            }
            #Reduce the start date by 1
            date_sub($startdate, date_interval_create_from_date_string("1 day"));
        }
        $data[$currentid]['TotalHours'] = $hours;
        $data[$currentid]['TotalWeeks'] = $weeks;

        #Get total visits for this location
        $visit_info = $db->query("SELECT SUM(`Total`) AS `Total` FROM `SRVisits` WHERE (DATE(CONCAT(`Year`, '-', IF(`Month`+0 < 10, '0', ''), `Month`+0, '-01')) BETWEEN DATE('$endstring') AND DATE('$startstring')) AND (`LibraryID` = $currentid)");
        $data[$currentid]['TotalVisits'] = $visit_info->fetch_column(0);
    }
    return $data;
}

function referendumInfo($db, $fiscalyear) {
    #Get fiscal year month
    $monthinfo = $db->query("SELECT `FYMonth`+0 FROM `LibraryInfo` WHERE `Branch` = 0");
    $fymonth = $monthinfo->fetch_column();

    if ($fymonth == 1) {
        $yearstart = $fiscalyear . "-01-01";
        $yearend = $fiscalyear+1 . "-01-01";
    } else if ($fymonth < 10) {
        $yearstart = $fiscalyear-1 . "-0" . $fymonth . "-01";
        $yearend = $fiscalyear . "-0" . $fymonth . "-01";
    } else {
        $yearstart = $fiscalyear-1 . "-" . $fymonth . "-01";
        $yearend = $fiscalyear . "-" . $fymonth . "-01";
    }
    
    $data = array();
    $referenda_info = $db->query("SELECT `ReferendumType`, `ReferendumDate`, `ReferendumPassed`, `EffectiveDate`, `BallotLanguage` FROM `Referenda` WHERE `ReferendumDate` BETWEEN DATE('$yearstart') AND DATE('$yearend`) ORDER BY `EffectiveDate` ASC");
    if ($referenda_info->num_rows > 0) {
        while ($info = $referenda_info->fetch_assoc()) {
            array_push($data, array("ReferendumType" => $info['ReferendumType'], "ReferendumDate" => $info['ReferendumDate'], "ReferendumPassed" => $info['ReferendumPassed'], "EffectiveDate" => $info['EffectiveDate'], "BallotLanguage" => $info['BallotLanguage']));
        }
    }
    return $data;
}

function trusteeInfo($db, $fiscalyear) {
    ($fiscalyear); //Unused in this function, but the parameter will be passed
    $data = array();
    $board_info = $db->query("SELECT TrusteeName, TrusteePosition, TrusteeTermEnds, TrusteeTelephone, TrusteeEmail, TrusteeHomeAddress, TrusteeCity, TrusteeState, TrusteeZIP FROM BoardTrustees ORDER BY TrusteeName ASC");
    if ($board_info->num_rows > 0) {
        while ($info = $board_info->fetch_assoc()) {
            array_push($data, array("TrusteeName" => $info['TrusteeName'], "TrusteePosition" => $info['TrusteePosition'], "TrusteeTermEnds" => $info['TrusteeTermEnds'], "TrusteeEmail" => $info['TrusteeEmail'], "TrusteeHomeAddress" => $info['TrusteeHomeAddress'], "TrusteeCity" => $info['TrusteeCity'], "TrusteeState" => $info['TrusteeState'], "TrusteeZIP" => $info['TrusteeZIP']));
        }
    }
    return $data;
}

function mlsPositionInfo($db, $fiscalyear) {
    ($fiscalyear); //Unused in this function, but the parameter will be passed
    $data = array();
    $position_info = $db->query("SELECT `lp.PositionTitle`, `wa.WorkAreaDescription` AS `PositionWorkArea`, `lp.HourlyRate` AS `PositionHourlyRate`, `lp.HoursPerWeek` AS `PositionWeeklyHours` FROM `LibrarianPositions` lp INNER JOIN `WorkAreas` wa ON `lp.WorkAreaID` = `wa.WorkAreaID` WHERE `lp.EducationLevelID` = 'MLS' AND `lp.PositionStatus` = 'Active' ORDER BY `lp.HoursPerWeek` DESC, `wa.WorkAreaDescription` ASC, `lp.PositionTitle` ASC");
    if ($position_info->num_rows > 0) {
        while ($info = $position_info->fetch_assoc()) {
            array_push($data, array("PositionTitle" => $info['PositionTitle'], "PositionWorkArea" => $info['PositionWorkArea'], "PositionHourlyRate" => $info['PositionHourlyRate'], "PositionWeeklyHours" => $info['PositionWeeklyHours']));
        }
    }
    return $data;
}

function otherPositionInfo($db, $fiscalyear) {
    ($fiscalyear); //Unused in this function, but the parameter will be passed
    $data = array();
    $position_info = $db->query("SELECT `lp.PositionTitle`, `wa.WorkAreaDescription` AS `PositionWorkArea`, `el.EducationLevel` AS `PositionEducation`, `lp.HourlyRate` AS `PositionHourlyRate`, `lp.HoursPerWeek` AS `PositionWeeklyHours` FROM `LibrarianPositions` lp INNER JOIN `WorkAreas` wa ON `lp.WorkAreaID` = `wa.WorkAreaID` INNER JOIN `EducationLevels` el ON `lp.EducationLevelID` = `el.EducationLevelID` WHERE `lp.EducationLevelID` != 'MLS' AND `lp.PositionStatus` = 'Active' ORDER BY `lp.HoursPerWeek` DESC, `wa.WorkAreaDescription` ASC, `lp.PositionTitle` ASC");
    if ($position_info->num_rows > 0) {
        while ($info = $position_info->fetch_assoc()) {
            array_push($data, array("PositionTitle" => $info['PositionTitle'], "PositionWorkArea" => $info['PositionWorkArea'], "PositionHourlyRate" => $info['PositionHourlyRate'], "PositionWeeklyHours" => $info['PositionWeeklyHours']));
        }
    }
    return $data;
}

function vacancyPositionInfo($db, $fiscalyear) {
    $data = array();
    
    #Get fiscal year month
    $monthinfo = $db->query("SELECT `FYMonth`+0 FROM `LibraryInfo` WHERE `Branch` = 0");
    $fymonth = $monthinfo->fetch_column();

    if ($fymonth == 1) {
        $yearstart = $fiscalyear . "-01-01";
        $yearend = $fiscalyear+1 . "-01-01";
    } else if ($fymonth < 10) {
        $yearstart = $fiscalyear-1 . "-0" . $fymonth . "-01";
        $yearend = $fiscalyear . "-0" . $fymonth . "-01";
    } else {
        $yearstart = $fiscalyear-1 . "-" . $fymonth . "-01";
        $yearend = $fiscalyear . "-" . $fymonth . "-01";
    }

    $position_info = $db->query("SELECT `lp.PositionTitle`, `wa.WorkAreaDescription` AS `PositionWorkArea`, `el.EducationLevel` AS `PositionEducation`, `lp.HoursPerWeek` AS `PositionWeeklyHours`, `lp.DateVacated`, `lp.AnnualSalaryMinimum` AS `PositionSalaryMinimum`, `lp.AnnualSalaryMaximum` AS `PositionSalaryMaximum` FROM `LibrarianPositions` lp INNER JOIN `WorkAreas` wa ON `lp.WorkAreaID` = `wa.WorkAreaID` INNER JOIN `EducationLevels` el ON `lp.EducationLevelID` = `el.EducationLevelID` WHERE (`lp.DateVacated` BETWEEN DATE('$yearstart') AND DATE('$yearend')) AND ((`lp.PositionStatus` = 'Vacant') OR (DATE('IFNULL(`lp.DateFilled`, '2000-01-01')) > DATE('$yearend'))) ORDER BY `lp.HoursPerWeek` DESC, `wa.WorkAreaDescription` ASC, `lp.PositionTitle` ASC");

    if ($position_info->num_rows > 0) {
        while ($info = $position_info->fetch_assoc()) {
            //Need to calculate weeks vacant
            $date_vacant = date_create($info['DateVacated']);
            $end_date = date_create($yearend);
            $timespan = date_diff($date_vacant, $end_date, true);
            $days = $timespan->format('%a');
            $weeks = floor($days/7);
            
            array_push($data, array("PositionTitle" => $info['PositionTitle'], "PositionWorkArea" => $info['PositionWorkArea'], "PositionEducation" => $info['PositionEducation'],"PositionWeeklyHours" => $info['PositionWeeklyHours'], "WeeksVacant" => $weeks, "PositionSalaryMinimum" => $info['PositionSalaryMinimum'], "PositionSalaryMaximum" => $info['PositionSalaryMaximum']));
        }
    }
    return $data;
}

function newPositionInfo($db, $fiscalyear) {
    $data = array();

    #Get fiscal year month
    $monthinfo = $db->query("SELECT `FYMonth`+0 FROM `LibraryInfo` WHERE `Branch` = 0");
    $fymonth = $monthinfo->fetch_column();

    if ($fymonth == 1) {
        $yearstart = $fiscalyear . "-01-01";
        $yearend = $fiscalyear+1 . "-01-01";
    } else if ($fymonth < 10) {
        $yearstart = $fiscalyear-1 . "-0" . $fymonth . "-01";
        $yearend = $fiscalyear . "-0" . $fymonth . "-01";
    } else {
        $yearstart = $fiscalyear-1 . "-" . $fymonth . "-01";
        $yearend = $fiscalyear . "-" . $fymonth . "-01";
    }

    $position_info = $db->query("SELECT `lp.PositionTitle`, `wa.WorkAreaDescription` AS `PositionWorkArea`, `el.EducationLevel` AS `PositionEducation`, `lp.HoursPerWeek` AS `PositionWeeklyHours`, `lp.DateCreated`, `lp.DateFilled` AS `DatePositionFilled` FROM `LibrarianPositions` lp INNER JOIN `WorkAreas` wa ON `lp.WorkAreaID` = `wa.WorkAreaID` INNER JOIN `EducationLevels` el ON `lp.EducationLevelID` = `el.EducationLevelID` WHERE `lp.DateCreated` BETWEEN DATE('$yearstart') AND DATE('$yearend') ORDER BY `lp.HoursPerWeek` DESC, `wa.WorkAreaDescription` ASC, `lp.PositionTitle` ASC");

    if ($position_info->num_rows > 0) {
        while ($info = $position_info->fetch_assoc()) {
            //Determine if the position was filled at the end of the fiscal year
            if (isset($info['DatePositionFilled'])) {
                $filled_date = date_create($info['DatePositionFilled']);
                $enddate = date_create($yearend);
                if ($filled_date > $yearend) {
                    $filled = "no";
                } else {
                    $filled = "yes";
                }
            } else {
                $filled = "no";
            }
            if ($filled = "no") {
                $date_filled = null;
            } else {
                $date_filled = $info['DatePositionFilled'];
            }

            array_push($data, array("PositionTitle" => $info['PositionTitle'], "PositionWorkArea" => $info['PositionWorkArea'], "PositionEducation" => $info['PositionEducation'],"PositionWeeklyHours" => $info['PositionWeeklyHours'], "PositionFilled" => $filled, "DatePositionFilled" => $date_filled));
        }
    }
    return $data;

}

function eliminatedPositionInfo($db, $fiscalyear) {
    $data = array();

    #Get fiscal year month
    $monthinfo = $db->query("SELECT `FYMonth`+0 FROM `LibraryInfo` WHERE `Branch` = 0");
    $fymonth = $monthinfo->fetch_column();

    if ($fymonth == 1) {
        $yearstart = $fiscalyear . "-01-01";
        $yearend = $fiscalyear+1 . "-01-01";
    } else if ($fymonth < 10) {
        $yearstart = $fiscalyear-1 . "-0" . $fymonth . "-01";
        $yearend = $fiscalyear . "-0" . $fymonth . "-01";
    } else {
        $yearstart = $fiscalyear-1 . "-" . $fymonth . "-01";
        $yearend = $fiscalyear . "-" . $fymonth . "-01";
    }
    
    $position_info = $db->query("SELECT `lp.PositionTitle`, `wa.WorkAreaDescription` AS `PositionWorkArea`, `el.EducationLevel` AS `PositionEducation`, `lp.HoursPerWeek` AS `PositionWeeklyHours`, `lp.DateEliminated` AS `DatePositionEliminated`, `lp.ReasonEliminated` AS `ReasonPositionEliminated`, `lp.LastSalary` AS `PositionLastSalary` FROM `LibrarianPositions` lp INNER JOIN `WorkAreas` wa ON `lp.WorkAreaID` = `wa.WorkAreaID` INNER JOIN `EducationLevels` el ON `lp.EducationLevelID` = `el.EducationLevelID` WHERE `lp.DateEliminated` BETWEEN DATE('$yearstart') AND DATE('$yearend') ORDER BY `lp.HoursPerWeek` DESC, `wa.WorkAreaDescription` ASC, `lp.PositionTitle` ASC");

    if ($position_info->num_rows > 0) {
        while ($info = $position_info->fetch_assoc()) {
            array_push($data, array("PositionTitle" => $info['PositionTitle'], "PositionWorkArea" => $info['PositionWorkArea'], "PositionEducation" => $info['PositionEducation'],"PositionWeeklyHours" => $info['PositionWeeklyHours'], "DatePositionEliminated" =>$info['DatePositionEliminated'], "ReasonPositionEliminated" => $info['ReasonPositionEliminated'], "PositionLastSalary" => $info['PositionLastSalary']));
        }
    }
    return $data;    
}

?>