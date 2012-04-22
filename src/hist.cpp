#include <stdio.h>
#include <stdlib.h>
#include <strings.h>
#include <tiffio.h>

// http://www.ibm.com/developerworks/linux/library/l-libtiff/

#define minr 0
#define minc 1024
#define maxc 1024
#define maxr 5

int main(int argc, char *argv[]) {
  TIFF *image;
  // Open the TIFF file
  if((image = TIFFOpen(argv[1], "r")) == NULL){
    printf("Could not open file\n");
    exit(42);
  }
  uint16 bps, spp;
  TIFFGetField(image, TIFFTAG_BITSPERSAMPLE, &bps);
  TIFFGetField(image, TIFFTAG_SAMPLESPERPIXEL, &spp);
  printf("BPS=%u SPP=%u\n", bps, spp);
/*
  uint16 photo, fillorder;
  TIFFGetField(image, TIFFTAG_PHOTOMETRIC, &photo);
  if(!TIFFGetField(image, TIFFTAG_FILLORDER, &fillorder)) {
    printf("FillOrder undefined, assume MSB2LSB\n");
    fillorder = FILLORDER_MSB2LSB;
  }
  printf("photo=%u order=%u\n", photo, fillorder);
*/
  uint16 minsv, maxsv;
  if(!TIFFGetField(image, TIFFTAG_MINSAMPLEVALUE, &minsv)) {
    printf("MinSamplerateValue undefined, assume 0\n");
    minsv = 0;
  }
  if(!TIFFGetField(image, TIFFTAG_MAXSAMPLEVALUE, &maxsv)) {
    printf("MaxSamplerateValue undefined, assume 65535\n");
    maxsv = 65535;
  }
  printf("SampleRateValue Min=%u Max=%u\n", minsv, maxsv);
/*
  tsize_t stripSize = TIFFStripSize (image);
  int stripMax = TIFFNumberOfStrips (image);
  printf("strip size=%d max=%d\n", (int)stripSize, stripMax);
*/
  unsigned int histsize = maxsv-minsv+1;
  uint16 * hist = (uint16*)malloc(histsize * bps/8);
  bzero(hist, histsize * bps/8);
  tsize_t lsize = TIFFScanlineSize(image);
  //unsigned char * buf = (unsigned char*)malloc(bufsize);
  //unsigned int bufsize = stripSize; // * stripMax;
  tdata_t buf = _TIFFmalloc(lsize);
  uint32 imlen;
  TIFFGetField(image, TIFFTAG_IMAGELENGTH, &imlen);
  printf("ImageLength=%d ScanlineSize=%d\n", imlen, lsize);
  for(uint32 i = 0; i < imlen; i++) {
    int readsz = TIFFReadScanline(image, buf, i);
    for(uint32 j = 0; j < lsize*8/bps; j++) {
       uint16 raw = ((uint16*)buf)[j];
       if(raw<minsv || raw>maxsv) { printf(" minsv-maxsv error: %u\n", raw); exit(1); }
       hist[raw-minsv]++;
    }
  }
  for(uint16 i=minsv; i<=maxsv; i++) {
    printf("HIST %u: %u (%.2lf%%)\n", i, hist[i], hist[i]*100./(lsize*imlen/2));
  }
  uint16 minh=0, maxh=65535;
  for(uint16 i=minsv+minr; i<=maxsv; i++) {
    if(hist[i]>minc) { minh=i; break; }
  }
  for(uint16 i=maxsv-maxr; i>=minsv; i--) {
    if(hist[i]>maxc) { maxh=i; break; }
  }
  printf("RANGE %u %u\n", minh, maxh);
}
