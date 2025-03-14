#!/usr/bin/perl

use DBI;
use CGI;
use Excel::Writer::XLSX;
use strict;
use lib 'shared';
use module Common;

my $query = CGI->new;
my $action = $query->param('action');

if ($action eq "dump") {
    #Dump the entire database table into an Excel document
    my $dsn = "DBI:mysql:database=$Common::$mysqldb";
    my $dbh = DBI->connect($dsn, $Common::$dbuser, $Common::$dbuserpw, { RaiseError => 1});

    #These sort results aren't completely in order.  They need to be subgrouped by question sequence under group and then by iteration if there's a group,
    #but that's nearly impossible to stuff into a SQL query.
    my $sth = $dbh->prepare(q{SELECT `d.ReportYear`, CONCAT(`q.SectionID`, '.', `q.Number`) AS QNumber, IF(`q.Source` == 'Multiple', SUBSTRING_INDEX(`q.Query`, '|', 1), 0) AS `Group`, `d.Iteration`, `q.Question`, `q.Format`, `q.Source`, `d.IntegerData`, `d.CurrencyData`, `d.TextData` FROM `SRData` d INNER JOIN `SRQuestions` q ON `d.QuestionID` = q.`QuestionID` ORDER BY `d.ReportYear` ASC, `q.SectionID` ASC, `q.Number` ASC, IF(`q.Source` == 'Multiple', SUBSTRING_INDEX(`q.Query`, '|', 1), 0) ASC, `d.Iteration` ASC}) or return_error("SQL Error", "Could not prepare report query: " . $DBI::errstr);
    $sth->execute() or return_error("SQL Error", "Could not execute report query: " . $DBI::errstr);

    my %questions;
    while (my @row = $sth->fetchrow_array()) {
        $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'Question'} = $row[4];
        $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'AnswerType'} = $row[6];
        $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'DataType'} = $row[5];
        if ($row[3] eq "Integer") {
            if (defined($row[5])) {
                $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'Answer'} = $row[7];
            }
        } elsif ($row[3] eq "Currency") {
            if (defined($row[6])) {
                $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'Answer'} = $row[8];
            }
        } elsif ($row[3] eq "Text") {
            if (defined($row[7])) {
                $questions{$row[0]}{$row[1]}{$row[2]}{$row[3]}{'Answer'} = $row[9];
            }
        }
    }

    $sth->close();
    $dbh->disconnect;

    open my $excelfh, ">", \my $output or die "Failed to open Excel filehandle: $!";

    my $workbook = Excel::Writer::XLSX->new( $excelfh );
    my @sheets;
    my %formats;

    $formats{'force_text'} = $workbook->add_format();
    $formats{'force_text'}->set_num_format( 0x31 ); #Text
    $formats{'number'} = $workbook->add_format();
    $formats{'number'}->set_num_format( 0x25 ); #Whole numbers with commas
    $formats{'currency'} = $workbook->add_format(); 
    $formats{'currency'}->set_num_format( 0x2c ); #Accounting format
    $formats{'text'} = $workbook->add_format();
    $formats{'text'}->set_text_wrap();

    foreach my $year (keys %questions) {
        push(@sheets, $workbook->add_worksheet($year));
        my $row = 0;
        my $rows = keys %{questions{$year}};
        my $current_sheet = (scalar @sheets) - 1;
        my $y = 1; #Skip a row for the table header
        my $lastgroup = 0;
        my %sortgroup;
        my @questionlist; #For keeping track of questions when iterating

        foreach my $question (keys %{$questions{$year}}) {
            foreach my $group (keys %{$questions{$year}{$question}}) {
                foreach my $iteration (keys %{$questions{$year}{$question}{$group}}) {
                    if ($group != $lastgroup) {
                        if ($lastgroup != 0) {
                            #Changed from a non-zero group number.  Print sorted.
                            foreach my $qiteration (keys %sortgroup) {
                                for (my $x = 0; $x < scalar @{$sortgroup{$qiteration}}; $x++) {
                                    my %qdata = %{${$sortgroup{$qiteration}}[$x]};
                                    $y = &printrow(\%qdata, $sheets[$current_sheet], $y, $questionlist[$x], \%formats);
                                }
                            }
                            %sortgroup = undef;
                            @questionlist = undef;
                        }
                        if ($group != 0) {
                            #This is a new group which needs to be sorted
                            $lastgroup = $group;
                        } else {
                            #Restore lastgroup to 0
                            $lastgroup = 0;
                        }
                        if ($group == 0) {
                            #Add a row to the spreadsheet
                            $y = &printrow(\%{$questions{$year}{$question}{$group}{$iteration}}, $sheets[$current_sheet], $y, $question, \%formats);
                        } else {
                            #Add the question info to the sort group
                            push(@{$sortgroup{$iteration}}, \%{$questions{$year}{$question}{$group}{$iteration}});
                            if ($iteration == 1) {
                                #In the first iteration add each question to the question list
                                push(@questionlist, $question);
                            }
                        }

                    }
                }
            }
        }
        my $tablename = $year . "Questions";
        $sheets[$current_sheet]->add_table(
            0, 0, $rows, 4,
            {
                style => 'Table Style Medium 2',
                name => $tablename;,
                columns => [
                    {header => 'Number'},
                    {header => 'Question'},
                    {header => 'Source'},
                    {header => 'Response'},
                ]
            }
        );
    }
    $workbook->close();
    use bytes;
    my $byte_size = length($output);
    print "Content-length: $byte_size\n";
    print "Contenty-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\n";
    print "Content-Disposition:attachment;filename=" . $filename . ".xlsx\n\n";
    binmode STDOUT;
    print $output;
}
exit;

sub printrow {
    my %response = %{$_[0]};
    my $sheet = $_[1];
    my $row = $_[2];
    my $qno = $_[3];
    my %formatlist = %{$_[4]};
    $sheet->write(0, $row, $qno, $formatlist{'force_text'});
    $sheet->write(1, $row, $response{'Question'}, $formatlist{'text'});
    $sheet->write(2, $row, $response{'AnswerType'}, $formatlist{'force_text'});
    my $datatype = $response{'DataType'};
    my $answer = $response{'Answer'};
    if ($datatype eq "Integer") {
        $sheet->write(3, $row, $answer, $formatlist{'number'});
    } elsif ($datatype eq "Currency") {
        $sheet->write(3, $row, $answer, $formatlist{'currency'});
    } else {
        $sheet->write(3, $row, $answer, $formatlist{'text'});
    }
    $row++;
    return $row;
}

sub return_error {
    my ($error, $message) = @_;
    print "Content-type: text/html\n\n";
    print "<html><head><title>$error</title></head><body>";
    print "<h1>$error</h1>";
    print "<p>$message</p>";
    echo "</p></body></html>";
    exit;
}