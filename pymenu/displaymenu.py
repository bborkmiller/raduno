import string
from os import listdir
import filecmp
import datetime
from shutil import copyfile
import htmlstuff # HTML header and footer text

# Open the file and read it in
menu_file = "text_menu.txt"
# ** Add some error handling here **
mf = open(menu_file, "r")
menu_text = mf.readlines()
mf.close()

############################################################################
## Parse the text menu file
# strip whitespace and linefeeds
menu_text = [line.lstrip().rstrip() for line in menu_text]

# remove empty lines
menu_text = [line for line in menu_text if len(line) > 0]

menu_code = []
for line in menu_text:
    new_line = {}

    # Check for an image
    if ".jpg" in line or ".png" in line:
        new_line["tag"] = "img"
        new_line["text"] = line
        menu_code.append(new_line)
        continue

    # split out the type symbol
    type, text = line.split(' ', 1)
    if type == "#":
        new_line["tag"] = "h1"
        new_line["text"] = string.capwords(text)
    elif type == "##":
        new_line["tag"] = "h2"
        new_line["text"] = string.capwords(text)
    elif type == "---":
        new_line["tag"] = "div"
        new_line["text"] = '</div>\n\n<div class="section">'
    elif ".jpg" in type or ".png" in type:
        new_line["tag"] = "img"
        new_line["text"] = line
    else:
        new_line["tag"] = "p"
        # Make sure the first letter is capitalized, leave the rest alone
        new_line["text"] = line[:1].upper() + line[1:]

    menu_code.append(new_line)

############################################################################
## Write out the web menu file to web_menu.html
## This is what people see at radunotc.com/menu/
web_menu = ""
for item in menu_code:
    # Omit the div and img
    if item["tag"] not in {"div", "img"}:
        web_menu += f"\t<{item['tag']}>{item['text']}</{item['tag']}>"
    web_menu += "\n"

wb = open('web_menu.html', 'w')
wb.write(web_menu)
wb.close()

############################################################################
## Generate the display menu
## This is what goes on the big TV in the restaurant
display_menu = htmlstuff.display_header
for item in menu_code:
    if item["tag"] == "div":
        display_menu += item["text"]
    elif item["tag"] == "img":
        display_menu += f"\t<img src={item['text']}>"
    else: # h1, h2, and p
        display_menu += f"\t<{item['tag']}>{item['text']}</{item['tag']}>"

    display_menu += "\n"
display_menu += htmlstuff.display_footer

# Write out the formatted menu to the browser
print(display_menu)

############################################################################
## Archive the menu file, if it's different than what's already in there
archive_files = listdir("menu_archive/")
# Subset to just the "menu_" files
archive_files = [f for f in archive_files if f.split("_", 1)[0] == "menu"]
# Sort and keep the most recent
latest = sorted(archive_files)[-1]
# Compare new file to most recent. If they don't match, then archive
if filecmp.cmp("menu_archive/" + latest, menu_file) == False:
     curdt = datetime.datetime.now()
     datetime = curdt.strftime("%Y%m%d-%H%M")
     newfile = f"menu_archive/menu_{datetime}.txt"
     copyfile(menu_file, newfile)
