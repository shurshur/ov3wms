#!/usr/bin/perl
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

my $gh = `echo $cy $cx|GeoidEval|awk '{ print \$1 }'`;
print "$gh\n";
