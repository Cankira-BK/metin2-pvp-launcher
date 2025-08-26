import os
from PIL import Image

root = os.getcwd()

def convert_tga_to_png():
	tga_files = [f for f in os.listdir(root) if f.lower().endswith('.tga')]
	for tga_file in tga_files:
		full_path = os.path.join(root, tga_file)
		png_path = os.path.splitext(full_path)[0] + '.png'

		print(f"Converting: {tga_file} -> {os.path.basename(png_path)}")
		with Image.open(full_path) as img:
			img.save(png_path)

		os.remove(full_path)
		print(f"Deleted original: {tga_file}")

if __name__ == "__main__":
	convert_tga_to_png()
	print("Conversion complete.")
