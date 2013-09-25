dist: assets
	rm -f euhwc.zip
	zip -r euhwc.zip . -x euhwc.zip Makefile .gitignore

assets:
	mkdir -p images
	inkscape -e images/euhwc-logo.png --export-height 70 ../../assets/euhwc-logo.svg

	inkscape -e favicon-16x16.ico --export-width 16 --export-height 16 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-24x24.ico --export-width 24 --export-height 24 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-32x32.ico --export-width 32 --export-height 32 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-64x64.ico --export-width 64 --export-height 64 ../../assets/euhwc-favicon.svg
	convert favicon-*x*.ico favicon.ico
	rm favicon-*x*.ico

	inkscape -e favicon.png --export-width 300 --export-height 300 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-iphone.png --export-width 57 --export-height 57 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-iphone-retina.png --export-width 114 --export-height 114 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-ipad.png --export-width 72 --export-height 72 ../../assets/euhwc-favicon.svg
	inkscape -e favicon-ipad-retina.png --export-width 144 --export-height 144 ../../assets/euhwc-favicon.svg

	inkscape -e images/eu-logo.png --export-height 70 --export-width 70 ../../assets/eu-logo.svg

clean:
	rm -rf euhwc.zip images favicon*

.PHONY: dist assets clean
