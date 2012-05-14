#!/usr/bin/perl

use strict;
use warnings;
use File::Basename;

#param 1 needs to be defined as ${selected_resource_path}
#param 2 needs to be defined as ${resource_loc} 
my $ftphost = "devel2.compactmvc.net";
my $ftpuser = "bhohbaum";
my $ftppass = "Aq59ru45e6Ma4cC22Ya2";
my $basedir = "html/libcompactmvc";
my $project = "libcompactmvc"; 

my $targetdir = $ARGV[0];
my $tfile = basename($targetdir);
$targetdir =~ s/$tfile//;
$targetdir =~ s/\/$project\//$basedir\//;

print(`echo cd $targetdir > __tmpupload.ftp`);
print(`echo put $ARGV[1] >> __tmpupload.ftp`);
print(`echo quit >> __tmpupload.ftp`);
print(`cat __tmpupload.ftp | ncftp -u $ftpuser -p $ftppass $ftphost`);
print(`rm __tmpupload.ftp`);
