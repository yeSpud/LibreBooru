/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| #     ### ####  ####  ##### ####  ##### ##### ####  #   # |
| #      #  #   # #   # #     #   # #   # #   # #   # #   # |
| #      #  ####  ####  ###   ####  #   # #   # ####  #   # |
| #      #  #   # #   # #     #   # #   # #   # #   # #   # |
| ##### ### ####  #   # ##### ####  ##### ##### #   # ##### |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

LibreBooru is an open-source Booru. It aims to enable users to host their own
image-library with an intuitive and extensive search, tagging-system and more.

Come hang out on Discord: https://discord.5ynchro.net
I'll be there if you have any questions, requests, and I'll regularly post
updates on the current devel build!

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 0. TABLE OF CONTENTS                                      |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

1. Live Demo
2. Donations
3. Installation
4. Translating
5. Uodating
6. Credits
7. Fun Facts

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 1. LIVE EDEMO                                             |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

See LibreBooru in action at https://booru.5ynchro.net
It's running the latest version with close to no changes to the source.
Donate to keep it alive :D

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 2. DONATIONS                                              |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

I Have spent a considerable amount of time writing this. If you like this,
consider donating over at ko-fi: https://ko-fi.com/aetherwellen
It really helps me out and keeps the development going <3
(Regardless of your donation, I will keep working on this tho haha)

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 3. INSTALLATION                                           |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

First off, these are the requirements:
- PHP 8.x (built and tested on 8.3 but older versions should work as well)
- A MySQL Database

After downloading the latest LibreBooru release, drop it into your /var/www
folder (or whatever you use) and make sure your domain points to /public_html
so theoretically, it should only be accessable by domain.com/index.php
YOU DO NOT WANT TO ACCESS ANY FILES ABOVE FROM THE PUBLIC!
(Don't open it in the browser - you're not done yet)

LibreBooru depends on external libraries you need to install now.
Download the latest releases of Smarty, Parsedown and jQuery:
- https://github.com/smarty-php/smarty/releases/latest
- https://github.com/erusev/parsedown/releases/latest
- https://cdnjs.com/libraries/jquery

If you want to support videos, FFmpeg is also required:
- FFmpeg (https://ffmpeg.org/download.html)

Now dorp all of them in the /software directory (if it doesn't exist, create it).
The final structure should look something like this:
\ software
| \ parsedown
| | - Parsedown.php
| | - ...
| \ smarty
| | \ libs
| | | - Smarty.class.php
| | \ src
| | | - ...
\ public_html
| \ assets
| | \ classic
| | | \ js
| | | | - jquery.min.js

(For windows users)
\ software
| \ ffmpeg
| | - ffmpeg.exe
| | - ffplay.exe
| | - ffprobe.exe

After configuring your webserver (I hope you know how to do this, if not,
search the internet; I am no wiki nor do I want to teach you the basics of
your webserver), open the address and you should be redirected to install.php
(If not, you should see an error message)

| BLOCKING BOTS

By default, everything should work out of the box if you're using apache2/httpd.
If it doesn't, you've either messed up during the installation or you don't have
a specific module enabled/installed.

To block Bots on NGINX: (at least this is my guess... I use apache2)
location ~* \.(jpg|jpeg|png|gif|bmp|tiff)$ {
  add_header X-Robots-Tag "noindex, nofollow";
}

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 4. TRANSLATING                                            |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

Help out translating LibreBooru in your language at 5ynchro's Weblate: https://translate.5ynchro.net

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 5. UPDATING                                               |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

You can most always use the inbuild Update System in the Admin Area. However,
ALWAYS make a backup of your data and database before doing so! You never know what may go wrong.

Also, please make sure to check out LibreBooru-Extras README.md:
https://github.com/5ynchrogazer/LibreBooru-Extras/blob/master/README.md
for further instructions and how-to's when upgrading.

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 6. CREDITS                                                |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

As of now, everything (exceptions listed below) has been written by @5ynchrogazer.

LibreBooru makes use of following libraries:
- Smarty template engine (https://github.com/smarty-php/smarty)
- Parsedown (https://github.com/erusev/parsedown)
- Userbar Generator (ttps://github.com/v1rx/userbar-generator)
- jQuery (https://jquery.com)
- FFmpeg (https://ffmpeg.org)

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 7. FUN FACTS                                              |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––––––/

... Every part of LibreBooru was created entirely on my ThinkPad T430 while
    listening to the iCon Radio (https://iconradio.stream.laut.fm/icon_radio)
... I struggled a lot with the search-functions, they still haunt me
    to this day
... Allowing guests to to upload/edit posts or make changes to the wiki will
    result in the system breaking as it's not intended for users with no ID to do
    anything at all, but I may fix this in the future... maybe
... Sometimes when tagging a post, it throws an error ranting about duplicate
    entries... no idea what I'm supposed to do here now
... The Footer message has been inspired from Shish's Shimmie2 ;)
... All images were edited in GIMP
... OpenBooru was rebranded to LibreBooru on 22nd December 2024