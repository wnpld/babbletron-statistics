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

    my $sth = $dbh->prepare(q{SELECT `d.ReportYear`, CONCAT(`q.SectionID`, '.', `q.Number`) AS QNumber, `q.Question`, `q.Format`, `q.Source`, `d.IntegerData`, `d.CurrencyData`, `d.TextData` FROM `SRData` d INNER JOIN `SRQuestions` q ON `d.QuestionID` = q.`QuestionID` ORDER BY `d.ReportYear` ASC, `q.SectionID` ASC, `q.Number` ASC}) or return_error("SQL Error", "Could not prepare report query: " . $DBI::errstr);
    $sth->execute() or return_error("SQL Error", "Could not execute report query: " . $DBI::errstr);

    my %questions;
    while (my @row = $sth->fetchrow_array()) {
        $questions{$row[0]}{$row[1]}{'Question'} = $row[2];
        $questions{$row[0]}{$row[1]}{'AnswerType'} = $row[4];
        $questions{$row[0]}{$row[1]}{'DataType'} = $row[3];
        if (($row[4] ne "Multiple") && ($row[3] eq "Integer")) {
            if (defined($row[5])) {
                $questions{$row[0]}{$row[1]}{'Answer'} = $row[5];
            }
        } elsif (($row[4] ne "Multiple") && ($row[3] eq "Currency")) {
            if (defined($row[6])) {
                $questions{$row[0]}{$row[1]}{'Answer'} = $row[6];
            }
        } elsif (($row[4] ne "Multiple") && ($row[3] eq "Text")) {
            if (defined($row[7])) {
                $questions{$row[0]}{$row[1]}{'Answer'} = $row[7];
            }
        }
    }

    $sth->close();
    $dbh->disconnect;

    open my $excelfh, ">", \my $output or die "Failed to open Excel filehandle: $!";

    my $workbook = Excel::Writer::XLSX->new( $excelfh );
    my @sheets;

    my $force_text_format = $workbook->add_format();
    $force_text_format->set_num_format( 0x31 ); #Text
    my $number_format = $workbook->add_format();
    $number_format->set_num_format( 0x25 ); #Whole numbers with commas
    my $currency_format = $workbook->add_format(); 
    $currency_format = set_num_format( 0x2c ); #Accounting format
    my $text_format = $workbook->add_format();
    $text_format->set_text_wrap();

    foreach my $year (keys %questions) {
        push(@sheets, $workbook->add_worksheet($year));
        my $row = 0;
        my $rows = keys %{questions{$year}};
        my $current_sheet = (scalar @sheets) - 1;
        my $y = 1; #Skip a row for the table header

        foreach my $question (keys %{$questions{$year}}) {
            $sheets[$current_sheet]->write(0, $y, $question, $force_text_format);
            $sheets[$current_sheet]->write(1, $y, $questions{$year}{$question}{'Question'}, $text_format);
            $sheets[$current_sheet]->write(2, $y, $questions{$year}{$question}{'AnswerType'}, $force_text_format);
            my $datatype = $questions{$year}{$question}{'DataType'};
            my $answer = $questions{$year}{$question}{'Answer'};
            if ($datatype eq "Integer") {
                $sheets[$current_sheet]->write(3, $y, $answer, $number_format);
            } elsif ($datatype eq "Currency") {
                $sheets[$current_sheet]->write(3, $y, $answer, $currency_format);
            } else {
                $sheets[$current_sheet]->write(3, $y, $answer, $text_format);
            }
            $y++;
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

sub return_error {
    my ($error, $message) = @_;
    print "Content-type: text/html\n\n";
    print "<html><head><title>$error</title></head><body>";
    print "<h1>$error</h1>";
    print "<p>$message</p>";
    echo "</p></body></html>";
    exit;
}