dist: assets
	rm -f euhwc.zip
	zip -r euhwc.zip . -x euhwc.zip Makefile .gitignore

assets:
	mkdir -p images
	inkscape -e images/euhwc-logo.png --export-height 70 ../../assets/euhwc-logo.svg
	inkscape -e favicon.ico --export-width 16 --export-height 16 ../../assets/euhwc-favicon.svg
	inkscape -e images/eu-logo.png --export-height 70 --export-width 70 ../../assets/eu-logo.svg

clean:
	rm -rf euhwc.zip images favicon.ico

.PHONY: dist assets clean
