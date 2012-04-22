#!/usr/bin/perl
use POSIX qw(floor);
use strict;

my $fn = $ARGV[0];
unless($fn=~/\.pvl$/) {
  die "specify pvl file";
}

#print "PVL: $fn\n";
open F,"<$fn";

my ($tif, $h, $w);

while(<F>) {
  chomp;
  if(/imageFileName\s*=\s*\"(.+)\"/) {
    $tif = $1;
  }
  if(/pixelsPerScanLine\s*=\s*(\d+)/) {
    $w = $1;
  }
  if(/numScanLines\s*=\s*(\d+)/) {
    $h = $1;
  }
  last if /geodeticCorners/;
}

die if !($w && $h && $tif);

#print "TIF: $tif\n";

my ($lat,$lon);
my @x=();
my @y=();

my $i=-1;

while(<F>) {
  chomp;
  last if /geodeticCorners/;
  $i++ if /BEGIN_GROUP/;
  if(/END_GROUP/) {
  }

  if(/latitude\s*=\s*(.+);/) {
    $lat = $1;
    push @y,$lat;
  }
  if(/longitude\s*=\s*(.+);/) {
    $lon = $1;
    push @x,$lon;
  }
}

my $cx = ($x[0]+$x[1]+$x[2]+$x[3])/4;
my $cy = ($y[0]+$y[1]+$y[2]+$y[3])/4;

my $mx0 = floor($x[0]/5)+37;
my $mx1 = floor($x[1]/5)+37;
my $mx2 = floor($x[2]/5)+37;
my $mx3 = floor($x[3]/5)+37;

my $my0 = 12-floor($y[0]/5);
my $my1 = 12-floor($y[1]/5);
my $my2 = 12-floor($y[2]/5);
my $my3 = 12-floor($y[3]/5);

my $minx = $mx0;
$minx = $mx2 if $mx2 < $mx0;
my $maxx = $mx1;
$maxx = $mx3 if $mx3 > $mx1;
my $miny = $my0;
$miny = $my2 if $my2 < $my0;
my $maxy = $my1;
$maxy = $my3 if $my3 > $my1;

for(my $i=$minx;$i<=$maxx;$i++) {
  for(my $j=$miny;$j<=$maxy;$j++) {
    printf "srtm_%02d_%02d\n",$i,$j;
  }
}
