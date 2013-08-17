dist: assets
	rm -f euhwc-social-login.zip
	zip -r euhwc-social-login.zip . -x euhwc-social-login.zip Makefile .gitignore

assets:
	mkdir -p images
	inkscape -e images/euhwc-button.png --export-height 35 --export-width 35 ../../assets/euhwc-button.svg

clean:
	rm -rf euhwc-social-login.zip images

.PHONY: dist assets clean
