/–––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| ##### ##### ##### #   # ####  ##### ##### ####  #   # |
| #   # #   # #     ##  # #   # #   # #   # #   # #   # |
| #   # ##### ###   # # # ####  #   # #   # ####  #   # |
| #   # #     #     #  ## #   # #   # #   # #   # #   # |
| ##### #     ##### #   # ####  ##### ##### #   #  ###  |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––/

OpenBooru is an open-source Booru. It aims to enable users to host their own
image-library with an intuitive and extensive search, tagging-system and more.

Table of contents:
1. Installation
X. Credits

| LIVE DEMO

See OpenBooru in action at https://openbooru.net
It's running the latest version with close to no changes to the source.
Donate to keep it alive :D

| DONATIONS

I Have spent a considerable amount of time writing this. If you like this,
consider donating over at ko-fi: https://ko-fi.com/aetherwellen
It really helps me out and keeps the development going <3
(Regardless of your donation, I will keep working on this tho haha)

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| 1. INSTALLATION                                       |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––/

First off, these are the requirements:
- PHP 8.x (built and tested on 8.3 but older versions should work as well)
- A MySQL Database

After downloading the latest OpenBooru release, drop it into your /var/www
folder (or whatever you use) and make sure your domain points to /public_html
so theoretically, it should only be accessable by domain.com/index.php
YOU DO NOT WANT TO ACCESS ANY FILES ABOVE FROM THE PUBLIC!
(Don't open it in the browser - you're not done yet)

OpenBooru depends on external libraries you need to install now.
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

/–––––––––––––––––––––––––––––––––––––––––––––––––––––––\
| X. CREDITS                                            |
\–––––––––––––––––––––––––––––––––––––––––––––––––––––––/

As of now, everything (exceptions listed below) has been written by @5ynchrogazer.

OpenBooru makes use of following libraries:
- Smarty template engine (https://github.com/smarty-php/smarty)
- Parsedown (https://github.com/erusev/parsedown)
- jQuery
- FFmpeg

| FUN FACTS

... Every part of OpenBooru was created entirely on my ThinkPad T430 while
    listening to the iCon Radio (https://iconradio.stream.laut.fm/icon_radio)
... I struggled a lot with the search-functions, they still haunt me
    to this day
... Allowing guests to to upload/edit posts or make changes to the wiki will
    result in the system breaking as it's not intended for users with no ID to do
    anything at all, but I may fix this in the future... maybe
... Sometimes when tagging a post, it throws an error ranting about duplicate
    entries... no idea what I'm supposed to do here now
... The Footer message has been inspired from Shish's Shimmie2 ;)