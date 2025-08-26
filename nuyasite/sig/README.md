# SIG Creator (Special Item Group Creator)
**SIG Creator** is a web tool designed to simplify and speed up the editing of item groups.

## Why Use It
Manually editing item groups can be tedious, looking up vnums, names, indexes... It's time-consuming and frustrating. SIG Creator automates and streamlines that process, saving you time and hassle.

## Highlights
- Supports 11 languages (and counting).
- Focused solely on item groups.
- Supports multiple drop types. More info available in the FAQ under the group type selector.

## Setup
1. **Convert item icons.**
	- Copy all icons to `share/icon/item`
	- Run the `conv.py` script to convert `.tga` files to `.png` (*`pillow` library required*)

	> Make sure you have [Python](https://www.python.org/) installed, then install [Pillow](https://pypi.org/project/pillow/):
	```bash
	pip install pillow
	```

2. **Add item data.**
	- Add item names and descriptions in the `share/locale/*` folders.
	- Add item list file in `share/locale/` folder.

3. **Configure the app.**
	- Open `include/config.php` and replace `APP_URL` with the URL where your tool will be hosted.

## License
[MIT License](https://github.com/Owsap/m2-sig-creator/blob/main/LICENSE)
